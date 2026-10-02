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
        Schema::create('kardex_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('articulo_id')->constrained('articulos');
            $table->foreignId('ubicacion_id')->constrained('ubicaciones');
            $table->foreignId('despacho_id')->nullable()->constrained('despachos_prestamos')->nullOnDelete();
            $table->enum('tipo_movimiento', [
                'INGRESO_COMPRA',
                'SALIDA_CONSUMO',
                'SALIDA_PRESTAMO',
                'RETORNO_PRESTAMO',
                'TRANSFERENCIA_INGRESO',
                'TRANSFERENCIA_SALIDA',
                'AJUSTE_SOBRANTE',
                'AJUSTE_FALTANTE',
            ]);
            $table->decimal('cantidad', 10, 2);
            $table->decimal('stock_anterior', 12, 2);
            $table->decimal('stock_posterior', 12, 2);
            $table->foreignId('usuario_id')->constrained('users');
            $table->dateTime('fecha_movimiento')->useCurrent();
            $table->string('motivo', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kardex_movimientos');
    }
};
