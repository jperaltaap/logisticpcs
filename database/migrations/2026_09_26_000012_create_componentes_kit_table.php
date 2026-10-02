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
        Schema::create('componentes_kit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kit_id')->constrained('kits')->cascadeOnDelete();
            $table->foreignId('articulo_id')->constrained('articulos')->cascadeOnUpdate();
            $table->decimal('cantidad', 10, 2)->default(1.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('componentes_kit');
    }
};
