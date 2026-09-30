<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lineas_investigacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_id')->constrained()->onDelete('cascade');
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->string('codigo', 50)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['grupo_id', 'activo']);
            $table->index('codigo');
        });

        Schema::create('planes_trabajo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_id')->constrained()->onDelete('cascade');
            $table->foreignId('linea_investigacion_id')->nullable()->constrained('lineas_investigacion')->onDelete('set null');
            $table->string('nombre', 200);
            $table->string('version', 20)->default('1.0');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->text('objetivos')->nullable();
            $table->text('metas')->nullable();
            $table->enum('estado', ['Borrador', 'En Progreso', 'Completado', 'Archivado'])->default('Borrador');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['grupo_id', 'activo']);
            $table->index(['linea_investigacion_id', 'activo']);
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes_trabajo');
        Schema::dropIfExists('lineas_investigacion');
    }
};
