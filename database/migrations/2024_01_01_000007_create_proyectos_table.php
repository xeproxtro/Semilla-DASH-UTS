<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proyectos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('titulo', 500);
            $table->text('objetivo_general');
            $table->text('objetivos_especificos')->nullable();
            $table->enum('tipo', [
                'investigacion_desarrollo',
                'investigacion_creacion',
                'idi',
                'extension',
                'formativo'
            ]);
            $table->string('convocatoria', 100)->nullable();
            $table->foreignId('linea_investigacion_id')->nullable()->constrained('lineas_investigacion')->onDelete('set null');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->date('fecha_fin_real')->nullable();
            $table->enum('estado', [
                'Propuesto',
                'Aprobado',
                'En Ejecucion',
                'Suspendido',
                'Finalizado',
                'Cancelado'
            ])->default('Propuesto');
            $table->decimal('presupuesto_total', 15, 2)->default(0);
            $table->string('director', 200)->nullable();
            $table->foreignId('director_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('resumen')->nullable();
            $table->text('palabras_clave')->nullable();
            $table->string('url_externa', 500)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('codigo');
            $table->index('tipo');
            $table->index('estado');
            $table->index('fecha_inicio');
            $table->index('director_id');
            $table->index('linea_investigacion_id');
        });

        Schema::create('proyecto_grupo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained()->onDelete('cascade');
            $table->foreignId('grupo_id')->constrained()->onDelete('cascade');
            $table->enum('rol', ['Principal', 'Asociado', 'Colaborador'])->default('Principal');
            $table->date('fecha_asociacion');
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->unique(['proyecto_id', 'grupo_id', 'fecha_asociacion']);
            $table->index(['proyecto_id', 'activo']);
            $table->index(['grupo_id', 'activo']);
        });

        Schema::create('proyecto_semillero', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained()->onDelete('cascade');
            $table->foreignId('semillero_id')->constrained()->onDelete('cascade');
            $table->enum('rol', ['Principal', 'Asociado', 'Colaborador'])->default('Principal');
            $table->date('fecha_asociacion');
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->unique(['proyecto_id', 'semillero_id', 'fecha_asociacion']);
            $table->index(['proyecto_id', 'activo']);
            $table->index(['semillero_id', 'activo']);
        });

        Schema::create('proyecto_institucion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained()->onDelete('cascade');
            $table->foreignId('institucion_id')->constrained()->onDelete('cascade');
            $table->enum('tipo_participacion', ['Ejecutora', 'Coordinadora', 'Asociada', 'Financiadora'])->default('Ejecutora');
            $table->date('fecha_asociacion');
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->unique(['proyecto_id', 'institucion_id', 'fecha_asociacion']);
            $table->index(['proyecto_id', 'activo']);
            $table->index(['institucion_id', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyecto_institucion');
        Schema::dropIfExists('proyecto_semillero');
        Schema::dropIfExists('proyecto_grupo');
        Schema::dropIfExists('proyectos');
    }
};
