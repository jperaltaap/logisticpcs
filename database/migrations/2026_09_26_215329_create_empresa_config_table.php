<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa_config', function (Blueprint $table) {
            $table->id();
            $table->string('razon_social', 200);
            $table->string('nombre_comercial', 200)->nullable();
            $table->string('ruc', 20)->nullable();
            $table->string('direccion', 300)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('pais', 100)->default('Perú');
            $table->string('telefono', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('sitio_web', 200)->nullable();
            $table->string('representante_legal', 150)->nullable();
            $table->string('cargo_representante', 100)->nullable();
            $table->string('logotipo_path', 500)->nullable();
            $table->string('moneda', 10)->default('PEN');
            $table->string('zona_horaria', 60)->default('America/Lima');
            $table->string('sistema_nombre', 100)->default('LogisticPCS');
            $table->string('sistema_subtitulo', 200)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa_config');
    }
};
