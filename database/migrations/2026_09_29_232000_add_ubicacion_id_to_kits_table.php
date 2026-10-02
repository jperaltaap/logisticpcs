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
        if (Schema::hasTable('kits') && ! Schema::hasColumn('kits', 'ubicacion_id')) {
            Schema::table('kits', function (Blueprint $table) {
                $table->foreignId('ubicacion_id')
                    ->nullable()
                    ->after('proyecto_id')
                    ->constrained('ubicaciones')
                    ->nullOnDelete();
            });

            // Asignar almacén coherente con el proyecto a los kits existentes
            $kits = DB::table('kits')->whereNull('ubicacion_id')->get();
            foreach ($kits as $kit) {
                $ubicacionId = null;
                if ($kit->proyecto_id) {
                    $ubicacionId = DB::table('ubicaciones')
                        ->where('proyecto_id', $kit->proyecto_id)
                        ->orderByRaw('CASE WHEN tipo = "ALMACEN_CENTRAL" THEN 1 WHEN tipo = "CENTRO_ACOPIO" THEN 2 ELSE 3 END')
                        ->value('id');
                }
                if (! $ubicacionId) {
                    $ubicacionId = DB::table('ubicaciones')
                        ->orderByRaw('CASE WHEN tipo = "ALMACEN_CENTRAL" THEN 1 WHEN tipo = "CENTRO_ACOPIO" THEN 2 ELSE 3 END')
                        ->value('id');
                }
                if ($ubicacionId) {
                    DB::table('kits')->where('id', $kit->id)->update(['ubicacion_id' => $ubicacionId]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('kits') && Schema::hasColumn('kits', 'ubicacion_id')) {
            Schema::table('kits', function (Blueprint $table) {
                $table->dropConstrainedForeignId('ubicacion_id');
            });
        }
    }
};
