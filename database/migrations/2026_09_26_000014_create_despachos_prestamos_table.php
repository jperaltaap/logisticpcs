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
        Schema::create('despachos_prestamos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_guia', 30)->unique();
            $table->enum('tipo_movimiento', [
                'SALIDA_PRESTAMO_CAMPO',
                'DEVOLUCION_CAMPO',
                'CONSUMO_DIRECTO',
                'TRANSFERENCIA_UBICACION',
                'AJUSTE_INVENTARIO',
            ]);
            $table->foreignId('proyecto_id')->constrained('proyectos');
            $table->foreignId('personal_id')->nullable()->constrained('personal')->nullOnDelete();
            $table->foreignId('cuadrilla_id')->nullable()->constrained('cuadrillas')->nullOnDelete();
            $table->foreignId('ubicacion_origen_id')->constrained('ubicaciones');
            $table->foreignId('ubicacion_destino_id')->nullable()->constrained('ubicaciones');
            $table->foreignId('usuario_registro_id')->constrained('users');
            $table->dateTime('fecha_despacho')->useCurrent();
            $table->date('fecha_compromiso_retorno')->nullable();
            $table->enum('estado', [
                'PENDIENTE_ENTREGA',
                'ENTREGADO_EN_CAMPO',
                'PARCIALMENTE_DEVUELTO',
                'DEVUELTO_TOTAL',
                'ANULADO',
            ])->default('ENTREGADO_EN_CAMPO');
            $table->longText('firma_digital_base64')->nullable();
            $table->string('foto_acta_respaldo', 255)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('despachos_prestamos');
    }
};
