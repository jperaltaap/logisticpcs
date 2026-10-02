<?php

use Illuminate\Database\Migrations\Migration;
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

        if ($isMysql) {
            DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('ADMINISTRADOR', 'LOGISTICO', 'SUPERVISOR', 'AUDITOR', 'TECNICO') NOT NULL DEFAULT 'LOGISTICO'");
        }

        if (Schema::hasTable('roles')) {
            DB::table('roles')->updateOrInsert(
                ['name' => 'TECNICO', 'guard_name' => 'web'],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('roles')) {
            DB::table('roles')->where('name', 'TECNICO')->delete();
        }

        $isMysql = DB::getDriverName() === 'mysql';
        if ($isMysql) {
            DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('ADMINISTRADOR', 'LOGISTICO', 'SUPERVISOR', 'AUDITOR') NOT NULL DEFAULT 'LOGISTICO'");
        }
    }
};
