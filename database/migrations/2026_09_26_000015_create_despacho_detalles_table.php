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
        Schema::create('despacho_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('despacho_id')->constrained('despachos_prestamos')->cascadeOnDelete();
            $table->foreignId('articulo_id')->constrained('articulos');
            $table->foreignId('activo_id')->nullable()->constrained('activos');
            $table->foreignId('kit_id')->nullable()->constrained('kits')->nullOnDelete();
            $table->decimal('cantidad', 10, 2)->default(1.00);
            $table->enum('estado_item', [
                'ENTREGADO',
                'DEVUELTO_OPERATIVO',
                'DEVUELTO_DANADO',
                'EXTRAVIADO',
                'CONSUMIDO',
            ])->default('ENTREGADO');
            $table->dateTime('fecha_devolucion')->nullable();
            $table->foreignId('usuario_recepcion_retorno_id')->nullable()->constrained('users');
            $table->text('observacion_retorno')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('despacho_detalles');
    }
};
