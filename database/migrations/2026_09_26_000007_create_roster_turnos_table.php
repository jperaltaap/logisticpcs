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
        Schema::create('roster_turnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personal_id')->constrained('personal')->cascadeOnDelete();
            $table->foreignId('proyecto_id')->constrained('proyectos');
            $table->string('grupo_guardia', 30)->default('GUARDIA A');
            $table->date('fecha');
            $table->enum('condicion_laboral', [
                'TRABAJO_CAMPO',
                'DESCANSO_CAMPAMENTO',
                'BAJADA_DESCANSO',
                'PERMISO',
                'LICENCIA_MEDICA',
            ])->default('TRABAJO_CAMPO');
            $table->string('observaciones', 255)->nullable();
            $table->timestamps();

            $table->unique(['personal_id', 'fecha'], 'uk_personal_fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roster_turnos');
    }
};
