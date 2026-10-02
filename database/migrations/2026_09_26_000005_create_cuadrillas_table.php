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
        Schema::create('cuadrillas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_cuadrilla', 30)->unique();
            $table->string('nombre', 120);
            $table->foreignId('proyecto_id')->constrained('proyectos');
            $table->foreignId('lider_personal_id')->constrained('personal');
            $table->string('regimen_laboral', 30)->default('14x7');
            $table->enum('estado', ['ACTIVA', 'DISUELTA', 'EN_DESCANSO'])->default('ACTIVA');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuadrillas');
    }
};
