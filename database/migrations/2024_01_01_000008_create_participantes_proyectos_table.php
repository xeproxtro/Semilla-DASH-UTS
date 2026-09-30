<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participantes_proyectos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('rol', [
                'Director',
                'Coinvestigador',
                'Investigador',
                'Asistente',
                'Estudiante',
                'Tecnico',
                'Administrativo'
            ])->default('Investigador');
            $table->integer('horas_dedicacion')->default(0);
            $table->decimal('presupuesto_asignado', 15, 2)->default(0);
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->unique(['proyecto_id', 'user_id', 'fecha_inicio']);
            $table->index(['proyecto_id', 'activo']);
            $table->index(['user_id', 'activo']);
            $table->index('rol');
        });

        Schema::create('fuentes_financiacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained()->onDelete('cascade');
            $table->string('fuente', 200);
            $table->string('tipo', 50);
            $table->decimal('monto', 15, 2)->default(0);
            $table->string('moneda', 10)->default('COP');
            $table->date('fecha_asignacion')->nullable();
            $table->date('fecha_finalizacion')->nullable();
            $table->string('contrato_convenio', 100)->nullable();
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->index(['proyecto_id', 'activo']);
            $table->index('tipo');
            $table->index('fuente');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuentes_financiacion');
        Schema::dropIfExists('participantes_proyectos');
    }
};
