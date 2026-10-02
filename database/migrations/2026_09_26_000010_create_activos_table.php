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
        Schema::create('activos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('articulo_id')->constrained('articulos')->cascadeOnUpdate();
            $table->string('codigo_interno', 50)->unique();
            $table->string('numero_serie', 100)->nullable()->index();
            $table->foreignId('ubicacion_actual_id')->constrained('ubicaciones');
            $table->foreignId('responsable_personal_id')->nullable()->constrained('personal')->nullOnDelete();
            $table->foreignId('cuadrilla_actual_id')->nullable()->constrained('cuadrillas')->nullOnDelete();
            $table->foreignId('proyecto_actual_id')->nullable()->constrained('proyectos')->nullOnDelete();
            $table->enum('estado_operativo', ['OPERATIVO', 'EN_MANTENIMIENTO', 'DANADO', 'DE_BAJA'])->default('OPERATIVO');
            $table->enum('condicion_prestamo', ['DISPONIBLE', 'PRESTADO_CAMPO', 'EN_TRANSFERENCIA', 'EXTRAVIADO'])->default('DISPONIBLE');
            $table->date('fecha_ingreso');
            $table->dateTime('fecha_ultima_asignacion')->nullable();
            $table->dateTime('fecha_ultimo_retorno')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activos');
    }
};
