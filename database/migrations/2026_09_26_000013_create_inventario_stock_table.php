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
        Schema::create('inventario_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('articulo_id')->constrained('articulos')->cascadeOnUpdate();
            $table->foreignId('ubicacion_id')->constrained('ubicaciones');
            $table->decimal('cantidad_actual', 12, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['articulo_id', 'ubicacion_id'], 'uk_art_ubicacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario_stock');
    }
};
