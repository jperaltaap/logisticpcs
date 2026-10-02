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
        Schema::create('kits', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_kit', 30)->unique();
            $table->string('nombre_kit', 150);
            $table->text('descripcion')->nullable();
            $table->enum('tipo_kit', ['KIT_HERRAMIENTAS', 'KIT_EPP', 'KIT_EMPALME', 'KIT_MATERIALES'])->default('KIT_HERRAMIENTAS');
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kits');
    }
};
