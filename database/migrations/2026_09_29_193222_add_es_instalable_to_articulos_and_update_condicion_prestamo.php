<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('articulos') && ! Schema::hasColumn('articulos', 'es_instalable')) {
            Schema::table('articulos', function (Blueprint $table) {
                $table->boolean('es_instalable')->default(false)->after('control_serie');
            });
        }

        if (Schema::hasTable('activos')) {
            $driver = DB::getDriverName();
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE activos MODIFY COLUMN condicion_prestamo ENUM('DISPONIBLE', 'PRESTADO_CAMPO', 'EN_TRANSFERENCIA', 'EXTRAVIADO', 'INSTALADO_PROYECTO') NOT NULL DEFAULT 'DISPONIBLE'");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('activos')) {
            $driver = DB::getDriverName();
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE activos MODIFY COLUMN condicion_prestamo ENUM('DISPONIBLE', 'PRESTADO_CAMPO', 'EN_TRANSFERENCIA', 'EXTRAVIADO') NOT NULL DEFAULT 'DISPONIBLE'");
            }
        }

        if (Schema::hasTable('articulos') && Schema::hasColumn('articulos', 'es_instalable')) {
            Schema::table('articulos', function (Blueprint $table) {
                $table->dropColumn('es_instalable');
            });
        }
    }
};
