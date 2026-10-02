<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    /**
     * Muestra el listado de respaldos (.sql) generados en el servidor.
     */
    public function index(): View
    {
        abort_unless(
            auth()->user()?->rol === 'ADMINISTRADOR',
            403,
            'Acceso denegado: Solo los Administradores pueden gestionar los Backups de Base de Datos.'
        );

        $backupDir = storage_path('app/backups');
        if (! File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $archivos = collect(File::files($backupDir))
            ->filter(fn ($file) => str_ends_with(strtolower($file->getFilename()), '.sql'))
            ->map(function ($file) {
                $sizeBytes = $file->getSize();
                $sizeFormatted = $sizeBytes >= 1048576
                    ? number_format($sizeBytes / 1048576, 2).' MB'
                    : number_format(max($sizeBytes / 1024, 0.01), 2).' KB';

                $filename = $file->getFilename();
                $tz = config('app.timezone', 'America/Lima');

                if (preg_match('/backup_logisticpcs_(\d{4})_(\d{2})_(\d{2})_(\d{2})(\d{2})(\d{2})\.sql$/i', $filename, $m)) {
                    $fecha = Carbon::createFromFormat('Y-m-d H:i:s', "{$m[1]}-{$m[2]}-{$m[3]} {$m[4]}:{$m[5]}:{$m[6]}", $tz);
                } else {
                    $fecha = Carbon::createFromTimestamp($file->getMTime(), $tz);
                }

                return [
                    'nombre' => $filename,
                    'tamano_bytes' => $sizeBytes,
                    'tamano' => $sizeFormatted,
                    'fecha' => $fecha,
                ];
            })
            ->sortByDesc(fn ($item) => $item['fecha']->timestamp)
            ->values();

        $tablasCount = count($this->obtenerNombresTablas());
        $dbName = (string) config('database.connections.'.config('database.default').'.database');

        return view('backups.index', compact('archivos', 'tablasCount', 'dbName'));
    }

    /**
     * Genera un nuevo respaldo completo (.sql) de la base de datos activa.
     */
    public function generar(): RedirectResponse
    {
        $user = auth()->user();
        abort_unless(
            $user?->rol === 'ADMINISTRADOR',
            403,
            'Acceso denegado: Solo los Administradores pueden generar Backups de Base de Datos.'
        );

        $backupDir = storage_path('app/backups');
        if (! File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = now()->format('Y_m_d_His');
        $filename = "backup_logisticpcs_{$timestamp}.sql";
        $filepath = $backupDir.DIRECTORY_SEPARATOR.$filename;

        $sql = $this->construirDumpSql($user->name);
        File::put($filepath, $sql);

        SystemLog::registrar(
            accion: 'BACKUP',
            modulo: 'BACKUPS_BD',
            descripcion: "Respaldo de base de datos generado exitosamente: {$filename}",
            datosNuevos: [
                'archivo' => $filename,
                'tamano_kb' => round(strlen($sql) / 1024, 2),
                'generado_por' => $user->name,
            ]
        );

        return redirect()
            ->route('backups.index')
            ->with('status', "Respaldo de base de datos '{$filename}' generado exitosamente.");
    }

    /**
     * Descarga un archivo de respaldo (.sql).
     */
    public function descargar(string $archivo): BinaryFileResponse
    {
        abort_unless(
            auth()->user()?->rol === 'ADMINISTRADOR',
            403,
            'Acceso denegado: Solo los Administradores pueden descargar Backups de Base de Datos.'
        );

        $safeName = basename($archivo);
        $filepath = storage_path('app/backups'.DIRECTORY_SEPARATOR.$safeName);

        abort_unless(File::exists($filepath), 404, 'El archivo de respaldo solicitado no existe.');

        SystemLog::registrar(
            accion: 'BACKUP',
            modulo: 'BACKUPS_BD',
            descripcion: "Descarga de respaldo de base de datos: {$safeName}"
        );

        return response()->download($filepath, $safeName, [
            'Content-Type' => 'application/sql',
        ]);
    }

    /**
     * Elimina un archivo de respaldo (.sql) del almacenamiento local.
     */
    public function eliminar(string $archivo): RedirectResponse
    {
        abort_unless(
            auth()->user()?->rol === 'ADMINISTRADOR',
            403,
            'Acceso denegado: Solo los Administradores pueden eliminar Backups de Base de Datos.'
        );

        $safeName = basename($archivo);
        $filepath = storage_path('app/backups'.DIRECTORY_SEPARATOR.$safeName);

        if (File::exists($filepath)) {
            File::delete($filepath);

            SystemLog::registrar(
                accion: 'ELIMINACION',
                modulo: 'BACKUPS_BD',
                descripcion: "Eliminación de archivo de respaldo: {$safeName}",
                datosAnteriores: ['archivo' => $safeName]
            );

            return redirect()
                ->route('backups.index')
                ->with('status', "Archivo de respaldo '{$safeName}' eliminado correctamente.");
        }

        return redirect()
            ->route('backups.index')
            ->with('error', 'El archivo especificado no fue encontrado.');
    }

    /**
     * Obtiene la lista de tablas de la conexión actual.
     *
     * @return array<int, string>
     */
    protected function obtenerNombresTablas(): array
    {
        $tables = Schema::getTables();
        $names = [];
        foreach ($tables as $table) {
            if (is_array($table) && isset($table['name'])) {
                $names[] = (string) $table['name'];
            } elseif (is_object($table) && isset($table->name)) {
                $names[] = (string) $table->name;
            }
        }

        return $names;
    }

    /**
     * Construye el contenido SQL completo de estructura y datos.
     */
    protected function construirDumpSql(string $autor): string
    {
        $driver = DB::getDriverName();
        $pdo = DB::getPdo();
        $tablas = $this->obtenerNombresTablas();
        $fecha = now()->format('Y-m-d H:i:s');

        $lines = [
            '-- ============================================================================',
            '-- LOGISTICPRO - SISTEMA DE GESTIÓN LOGÍSTICA, ALMACÉN Y CAMPO',
            "-- Respaldo Completo de Base de Datos ({$driver})",
            "-- Fecha de Generación: {$fecha}",
            "-- Generado por: {$autor}",
            '-- Total de Tablas: '.count($tablas),
            '-- ============================================================================',
            '',
            'SET FOREIGN_KEY_CHECKS=0;',
            '',
        ];

        foreach ($tablas as $tabla) {
            $lines[] = '-- ----------------------------------------------------------------------------';
            $lines[] = "-- Tabla: `{$tabla}`";
            $lines[] = '-- ----------------------------------------------------------------------------';

            if ($driver === 'mysql' || $driver === 'mariadb') {
                $createResult = DB::select("SHOW CREATE TABLE `{$tabla}`");
                if (! empty($createResult)) {
                    $rowObj = (array) $createResult[0];
                    $createSql = $rowObj['Create Table'] ?? array_values($rowObj)[1] ?? null;
                    if ($createSql) {
                        $lines[] = "DROP TABLE IF EXISTS `{$tabla}`;";
                        $lines[] = $createSql.';';
                        $lines[] = '';
                    }
                }
            }

            $rows = DB::table($tabla)->get();
            if ($rows->isNotEmpty()) {
                foreach ($rows as $row) {
                    $rowData = (array) $row;
                    $columns = array_map(fn ($col) => "`{$col}`", array_keys($rowData));
                    $values = array_map(function ($val) use ($pdo) {
                        if ($val === null) {
                            return 'NULL';
                        }
                        if (is_bool($val)) {
                            return $val ? '1' : '0';
                        }

                        return $pdo->quote((string) $val);
                    }, array_values($rowData));

                    $lines[] = sprintf(
                        'INSERT INTO `%s` (%s) VALUES (%s);',
                        $tabla,
                        implode(', ', $columns),
                        implode(', ', $values)
                    );
                }
                $lines[] = '';
            }
        }

        $lines[] = 'SET FOREIGN_KEY_CHECKS=1;';
        $lines[] = '-- Fin del respaldo SQL';

        return implode("\n", $lines)."\n";
    }
}
