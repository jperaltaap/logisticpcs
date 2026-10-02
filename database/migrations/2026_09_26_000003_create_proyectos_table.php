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
        Schema::create('proyectos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 150);
            $table->string('cliente', 150);
            $table->string('ubicacion_direccion', 255);
            $table->date('fecha_inicio');
            $table->date('fecha_fin_estimada')->nullable();
            $table->unsignedBigInteger('responsable_personal_id')->nullable();
            $table->enum('estado', ['ACTIVO', 'SUSPENDIDO', 'FINALIZADO'])->default('ACTIVO');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proyectos');
    }
};
