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
        Schema::create('ingresos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_ingreso', 50)->unique();
            $table->enum('tipo_ingreso', [
                'COMPRA_NUEVA',
                'AJUSTE_SOBRANTE',
                'TRANSFERENCIA_INGRESO',
                'DONACION_TRASPASO',
            ])->default('COMPRA_NUEVA');
            $table->foreignId('ubicacion_id')->constrained('ubicaciones');
            $table->foreignId('proyecto_id')->nullable()->constrained('proyectos')->nullOnDelete();
            $table->string('proveedor', 150)->nullable();
            $table->string('numero_comprobante', 100)->nullable();
            $table->date('fecha_ingreso');
            $table->foreignId('usuario_id')->constrained('users');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::create('ingreso_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingreso_id')->constrained('ingresos')->cascadeOnDelete();
            $table->foreignId('articulo_id')->constrained('articulos');
            $table->decimal('cantidad', 10, 2)->default(1.00);
            $table->decimal('costo_unitario', 12, 2)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        if (Schema::hasTable('activos') && ! Schema::hasColumn('activos', 'ingreso_id')) {
            Schema::table('activos', function (Blueprint $table) {
                $table->foreignId('ingreso_id')->nullable()->after('articulo_id')->constrained('ingresos')->nullOnDelete();
            });
        }

        if (Schema::hasTable('kardex_movimientos') && ! Schema::hasColumn('kardex_movimientos', 'ingreso_id')) {
            Schema::table('kardex_movimientos', function (Blueprint $table) {
                $table->foreignId('ingreso_id')->nullable()->after('despacho_id')->constrained('ingresos')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('kardex_movimientos') && Schema::hasColumn('kardex_movimientos', 'ingreso_id')) {
            Schema::table('kardex_movimientos', function (Blueprint $table) {
                $table->dropConstrainedForeignId('ingreso_id');
            });
        }

        if (Schema::hasTable('activos') && Schema::hasColumn('activos', 'ingreso_id')) {
            Schema::table('activos', function (Blueprint $table) {
                $table->dropConstrainedForeignId('ingreso_id');
            });
        }

        Schema::dropIfExists('ingreso_detalles');
        Schema::dropIfExists('ingresos');
    }
};
