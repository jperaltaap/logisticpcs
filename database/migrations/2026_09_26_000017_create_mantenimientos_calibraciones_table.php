<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mantenimientos_calibraciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activo_id')->constrained('activos');
            $table->enum('tipo', ['PREVENTIVO', 'CORRECTIVO', 'CALIBRACION_LAB', 'CERTIFICACION']);
            $table->string('proveedor_taller', 150)->nullable();
            $table->date('fecha_ingreso');
            $table->date('fecha_salida')->nullable();
            $table->date('proxima_calibracion_sugerida')->nullable();
            $table->decimal('costo', 10, 2)->default(0.00);
            $table->string('certificado_calibracion_pdf', 255)->nullable();
            $table->text('descripcion_falla_o_trabajo');
            $table->enum('resultado', ['CONFORME_OPERATIVO', 'NO_CONFORME_BAJA', 'EN_PROCESO'])->default('EN_PROCESO');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimientos_calibraciones');
    }
};
