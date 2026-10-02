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
        Schema::create('notificaciones_alertas', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', [
                'STOCK_MINIMO',
                'MATERIAL_SIN_MOVIMIENTO',
                'PRESTAMO_VENCIDO',
                'CALIBRACION_POR_VENCER',
            ]);
            $table->string('titulo', 150);
            $table->text('mensaje');
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->boolean('leida')->default(false);
            $table->dateTime('fecha_alerta')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones_alertas');
    }
};
