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
        Schema::create('personal', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_trabajador', 30)->nullable()->unique();
            $table->string('codigo_fotocheck', 30)->nullable()->unique();
            $table->string('dni', 15)->unique();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('cargo', 100);
            $table->string('area', 100);
            $table->string('telefono', 30)->nullable();
            $table->string('correo', 150)->nullable();
            $table->foreignId('proyecto_id')->nullable()->constrained('proyectos')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('estado', ['ACTIVO', 'VACACIONES', 'DESCANSO_MEDICO', 'CESADO'])->default('ACTIVO');
            $table->timestamps();
            $table->softDeletes();
        });

        // Asignación de clave foránea circular en proyectos
        Schema::table('proyectos', function (Blueprint $table) {
            $table->foreign('responsable_personal_id', 'fk_proyectos_responsable')
                ->references('id')
                ->on('personal')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropForeign('fk_proyectos_responsable');
        });

        Schema::dropIfExists('personal');
    }
};
