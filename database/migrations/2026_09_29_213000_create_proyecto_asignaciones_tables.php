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
        if (! Schema::hasTable('proyecto_user')) {
            Schema::create('proyecto_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['proyecto_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('personal_proyecto')) {
            Schema::create('personal_proyecto', function (Blueprint $table) {
                $table->id();
                $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
                $table->foreignId('personal_id')->constrained('personal')->cascadeOnDelete();
                $table->boolean('es_responsable')->default(false);
                $table->string('rol_en_proyecto', 80)->nullable();
                $table->timestamps();

                $table->unique(['proyecto_id', 'personal_id']);
            });
        }

        // Sincronizar asignaciones existentes de usuarios a proyectos
        $usersWithProject = DB::table('users')->whereNotNull('proyecto_id')->get(['id', 'proyecto_id']);
        foreach ($usersWithProject as $u) {
            DB::table('proyecto_user')->insertOrIgnore([
                'proyecto_id' => $u->proyecto_id,
                'user_id' => $u->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Sincronizar asignaciones existentes de personal a proyectos
        $personalWithProject = DB::table('personal')->whereNotNull('proyecto_id')->get(['id', 'proyecto_id']);
        foreach ($personalWithProject as $p) {
            DB::table('personal_proyecto')->insertOrIgnore([
                'proyecto_id' => $p->proyecto_id,
                'personal_id' => $p->id,
                'es_responsable' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Sincronizar responsables principales de proyectos
        $proyectosWithResp = DB::table('proyectos')->whereNotNull('responsable_personal_id')->get(['id', 'responsable_personal_id']);
        foreach ($proyectosWithResp as $pr) {
            $exists = DB::table('personal_proyecto')
                ->where('proyecto_id', $pr->id)
                ->where('personal_id', $pr->responsable_personal_id)
                ->exists();

            if ($exists) {
                DB::table('personal_proyecto')
                    ->where('proyecto_id', $pr->id)
                    ->where('personal_id', $pr->responsable_personal_id)
                    ->update(['es_responsable' => true, 'updated_at' => now()]);
            } else {
                DB::table('personal_proyecto')->insert([
                    'proyecto_id' => $pr->id,
                    'personal_id' => $pr->responsable_personal_id,
                    'es_responsable' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_proyecto');
        Schema::dropIfExists('proyecto_user');
    }
};
