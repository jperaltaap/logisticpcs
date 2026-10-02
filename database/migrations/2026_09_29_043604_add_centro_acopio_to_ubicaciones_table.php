<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE ubicaciones MODIFY COLUMN tipo ENUM('ALMACEN_CENTRAL','ALMACEN_OBRA','CENTRO_ACOPIO','EN_CAMPO','TALLER_REPARACION','BAJA') NOT NULL DEFAULT 'ALMACEN_CENTRAL'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE ubicaciones MODIFY COLUMN tipo ENUM('ALMACEN_CENTRAL','ALMACEN_OBRA','EN_CAMPO','TALLER_REPARACION','BAJA') NOT NULL DEFAULT 'ALMACEN_CENTRAL'");
        }
    }
};
