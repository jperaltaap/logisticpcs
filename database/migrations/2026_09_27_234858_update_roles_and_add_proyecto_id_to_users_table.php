<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $isMysql = DB::getDriverName() === 'mysql';

        // 1. Ampliar temporalmente el ENUM para permitir LOGISTICO
        if ($isMysql) {
            DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('ADMINISTRADOR', 'ALMACENERO', 'LOGISTICO', 'SUPERVISOR', 'AUDITOR') NOT NULL DEFAULT 'LOGISTICO'");
        }

        // 2. Actualizar usuarios existentes con rol ALMACENERO a LOGISTICO
        DB::table('users')->where('rol', 'ALMACENERO')->update(['rol' => 'LOGISTICO']);

        // 3. Fijar el ENUM definitivo sin ALMACENERO
        if ($isMysql) {
            DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('ADMINISTRADOR', 'LOGISTICO', 'SUPERVISOR', 'AUDITOR') NOT NULL DEFAULT 'LOGISTICO'");
        }

        // 4. Actualizar rol en Spatie roles si existe
        if (Schema::hasTable('roles')) {
            DB::table('roles')->where('name', 'ALMACENERO')->update(['name' => 'LOGISTICO']);
        }

        // 5. Agregar columna proyecto_id a users
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'proyecto_id')) {
                $table->foreignId('proyecto_id')->nullable()->after('estado')->constrained('proyectos')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'proyecto_id')) {
                $table->dropForeign(['proyecto_id']);
                $table->dropColumn('proyecto_id');
            }
        });

        if (Schema::hasTable('roles')) {
            DB::table('roles')->where('name', 'LOGISTICO')->update(['name' => 'ALMACENERO']);
        }

        $isMysql = DB::getDriverName() === 'mysql';
        if ($isMysql) {
            DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('ADMINISTRADOR', 'ALMACENERO', 'SUPERVISOR', 'AUDITOR') NOT NULL DEFAULT 'ALMACENERO'");
        }
    }
};
