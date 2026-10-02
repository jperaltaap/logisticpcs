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
        // 1. articulos
        if (Schema::hasTable('articulos') && ! Schema::hasColumn('articulos', 'proyecto_id')) {
            Schema::table('articulos', function (Blueprint $table) {
                $table->foreignId('proyecto_id')->nullable()->after('categoria_id')->constrained('proyectos')->nullOnDelete();
            });
        }

        // 2. ubicaciones
        if (Schema::hasTable('ubicaciones') && ! Schema::hasColumn('ubicaciones', 'proyecto_id')) {
            Schema::table('ubicaciones', function (Blueprint $table) {
                $table->foreignId('proyecto_id')->nullable()->after('id')->constrained('proyectos')->nullOnDelete();
            });
        }

        // 3. kits
        if (Schema::hasTable('kits') && ! Schema::hasColumn('kits', 'proyecto_id')) {
            Schema::table('kits', function (Blueprint $table) {
                $table->foreignId('proyecto_id')->nullable()->after('id')->constrained('proyectos')->nullOnDelete();
            });
        }

        // 4. inventario_stock
        if (Schema::hasTable('inventario_stock') && ! Schema::hasColumn('inventario_stock', 'proyecto_id')) {
            Schema::table('inventario_stock', function (Blueprint $table) {
                $table->foreignId('proyecto_id')->nullable()->after('ubicacion_id')->constrained('proyectos')->nullOnDelete();
            });
        }

        // 5. kardex_movimientos
        if (Schema::hasTable('kardex_movimientos') && ! Schema::hasColumn('kardex_movimientos', 'proyecto_id')) {
            Schema::table('kardex_movimientos', function (Blueprint $table) {
                $table->foreignId('proyecto_id')->nullable()->after('despacho_id')->constrained('proyectos')->nullOnDelete();
            });
        }

        // 6. mantenimientos_calibraciones
        if (Schema::hasTable('mantenimientos_calibraciones') && ! Schema::hasColumn('mantenimientos_calibraciones', 'proyecto_id')) {
            Schema::table('mantenimientos_calibraciones', function (Blueprint $table) {
                $table->foreignId('proyecto_id')->nullable()->after('activo_id')->constrained('proyectos')->nullOnDelete();
            });
        }

        // 7. notificaciones_alertas
        if (Schema::hasTable('notificaciones_alertas') && ! Schema::hasColumn('notificaciones_alertas', 'proyecto_id')) {
            Schema::table('notificaciones_alertas', function (Blueprint $table) {
                $table->foreignId('proyecto_id')->nullable()->after('referencia_id')->constrained('proyectos')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('notificaciones_alertas') && Schema::hasColumn('notificaciones_alertas', 'proyecto_id')) {
            Schema::table('notificaciones_alertas', function (Blueprint $table) {
                $table->dropConstrainedForeignId('proyecto_id');
            });
        }

        if (Schema::hasTable('mantenimientos_calibraciones') && Schema::hasColumn('mantenimientos_calibraciones', 'proyecto_id')) {
            Schema::table('mantenimientos_calibraciones', function (Blueprint $table) {
                $table->dropConstrainedForeignId('proyecto_id');
            });
        }

        if (Schema::hasTable('kardex_movimientos') && Schema::hasColumn('kardex_movimientos', 'proyecto_id')) {
            Schema::table('kardex_movimientos', function (Blueprint $table) {
                $table->dropConstrainedForeignId('proyecto_id');
            });
        }

        if (Schema::hasTable('inventario_stock') && Schema::hasColumn('inventario_stock', 'proyecto_id')) {
            Schema::table('inventario_stock', function (Blueprint $table) {
                $table->dropConstrainedForeignId('proyecto_id');
            });
        }

        if (Schema::hasTable('kits') && Schema::hasColumn('kits', 'proyecto_id')) {
            Schema::table('kits', function (Blueprint $table) {
                $table->dropConstrainedForeignId('proyecto_id');
            });
        }

        if (Schema::hasTable('ubicaciones') && Schema::hasColumn('ubicaciones', 'proyecto_id')) {
            Schema::table('ubicaciones', function (Blueprint $table) {
                $table->dropConstrainedForeignId('proyecto_id');
            });
        }

        if (Schema::hasTable('articulos') && Schema::hasColumn('articulos', 'proyecto_id')) {
            Schema::table('articulos', function (Blueprint $table) {
                $table->dropConstrainedForeignId('proyecto_id');
            });
        }
    }
};
