<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained()->onDelete('cascade');
            $table->string('codigo', 50)->nullable();
            $table->string('nombre', 300);
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['Actividad', 'Hito', 'Entregable', 'Tarea'])->default('Actividad');
            $table->foreignId('actividad_padre_id')->nullable()->constrained('actividades')->onDelete('set null');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->date('fecha_fin_real')->nullable();
            $table->enum('estado', [
                'Pendiente',
                'En Progreso',
                'Completado',
                'Atrasado',
                'Cancelado'
            ])->default('Pendiente');
            $table->integer('porcentaje_avance')->default(0);
            $table->foreignId('responsable_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('presupuesto_asignado', 15, 2)->default(0);
            $table->text('entregables_esperados')->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['proyecto_id', 'activo']);
            $table->index('tipo');
            $table->index('estado');
            $table->index('fecha_inicio');
            $table->index('responsable_id');
            $table->index('actividad_padre_id');
        });

        Schema::create('riesgos_proyectos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained()->onDelete('cascade');
            $table->string('nombre', 300);
            $table->text('descripcion')->nullable();
            $table->enum('categoria', ['Tecnico', 'Financiero', 'Operativo', 'Legal', 'Strategico'])->default('Tecnico');
            $table->enum('probabilidad', ['Baja', 'Media', 'Alta'])->default('Media');
            $table->enum('impacto', ['Bajo', 'Medio', 'Alto'])->default('Medio');
            $table->text('estrategia_mitigacion')->nullable();
            $table->text('plan_contingencia')->nullable();
            $table->foreignId('responsable_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('estado', ['Identificado', 'En Monitoreo', 'Mitigado', 'Materializado'])->default('Identificado');
            $table->date('fecha_identificacion');
            $table->date('fecha_resolucion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['proyecto_id', 'activo']);
            $table->index('categoria');
            $table->index('estado');
        });

        Schema::create('avances_proyectos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained()->onDelete('cascade');
            $table->foreignId('actividad_id')->nullable()->constrained('actividades')->onDelete('set null');
            $table->date('fecha_reporte');
            $table->text('descripcion_avance');
            $table->integer('porcentaje_cumplimiento')->default(0);
            $table->text('logros')->nullable();
            $table->text('dificultades')->nullable();
            $table->text('siguientes_pasos')->nullable();
            $table->foreignId('reportado_por')->constrained('users')->onDelete('cascade');
            $table->enum('tipo_reporte', ['Mensual', 'Trimestral', 'Semestral', 'Final'])->default('Mensual');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index(['proyecto_id', 'fecha_reporte']);
            $table->index('actividad_id');
            $table->index('reportado_por');
            $table->index('tipo_reporte');
        });

        Schema::create('decisiones_proyectos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained()->onDelete('cascade');
            $table->string('titulo', 300);
            $table->text('descripcion');
            $table->enum('tipo', ['Aprobacion', 'Cambio', 'Riesgo', 'Recurso', 'Cronograma'])->default('Aprobacion');
            $table->date('fecha_decision');
            $table->foreignId('tomada_por')->constrained('users')->onDelete('cascade');
            $table->text('justificacion')->nullable();
            $table->text('impacto')->nullable();
            $table->enum('estado', ['Pendiente', 'Aprobada', 'Rechazada', 'Implementada'])->default('Pendiente');
            $table->date('fecha_implementacion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index(['proyecto_id', 'fecha_decision']);
            $table->index('tipo');
            $table->index('estado');
            $table->index('tomada_por');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decisiones_proyectos');
        Schema::dropIfExists('avances_proyectos');
        Schema::dropIfExists('riesgos_proyectos');
        Schema::dropIfExists('actividades');
    }
};
