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
        Schema::create('articulos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias')->cascadeOnUpdate();
            $table->string('codigo_sku', 50)->unique();
            $table->string('descripcion', 200);
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->string('unidad_medida', 20)->default('UND');
            $table->enum('tipo_articulo', ['EPP', 'HERRAMIENTA', 'EQUIPO', 'MATERIAL', 'CONSUMIBLE', 'OFICINA']);
            $table->boolean('control_serie')->default(false);
            $table->decimal('stock_minimo', 10, 2)->default(0.00);
            $table->unsignedInteger('vida_util_meses')->nullable();
            $table->string('foto_referencia', 255)->nullable();
            $table->enum('estado', ['ACTIVO', 'MANTENIMIENTO', 'BAJA'])->default('ACTIVO');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articulos');
    }
};
