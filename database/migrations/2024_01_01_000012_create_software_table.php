<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('software_registrado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos_ctei')->onDelete('cascade');
            $table->string('nombre', 200);
            $table->string('version', 50);
            $table->integer('anio_desarrollo');
            $table->enum('tipo', ['Aplicacion', 'Sistema', 'Libreria', 'Framework', 'Plugin', 'Otro'])->default('Aplicacion');
            $table->string('titular', 200);
            $table->string('licencia', 100)->nullable();
            $table->enum('disponibilidad', ['Privado', 'Open Source', 'Comercial', 'Freeware'])->default('Privado');
            $table->string('url_repositorio', 500)->nullable();
            $table->string('url_descarga', 500)->nullable();
            $table->text('descripcion_tecnica')->nullable();
            $table->string('plataforma', 100)->nullable();
            $table->string('lenguaje_programacion', 100)->nullable();
            $table->integer('lineas_codigo')->nullable();
            $table->boolean('tiene_certificacion_innovacion')->default(false);
            $table->string('entidad_certificadora', 200)->nullable();
            $table->date('fecha_certificacion')->nullable();
            $table->string('numero_registro_software', 50)->nullable();
            $table->date('fecha_registro_dnda')->nullable();
            $table->string('nombre_soporte_logico', 200)->nullable();
            $table->string('tipo_soporte_logico', 100)->nullable();
            $table->string('url_soporte_logico', 500)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('producto_id');
            $table->index('nombre');
            $table->index('version');
            $table->index('anio_desarrollo');
            $table->index('tipo');
            $table->index('tiene_certificacion_innovacion');
        });

        Schema::create('fases_software', function (Blueprint $table) {
            $table->id();
            $table->foreignId('software_id')->constrained('software_registrado')->onDelete('cascade');
            $table->enum('fase', ['Analisis', 'Diseño', 'Implementacion', 'Validacion']);
            $table->text('descripcion_fase');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->enum('estado', ['Pendiente', 'En Progreso', 'Completado'])->default('Pendiente');
            $table->text('documentacion')->nullable();
            $table->text('evidencias')->nullable();
            $table->foreignId('responsable_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->unique(['software_id', 'fase']);
            $table->index(['software_id', 'fase']);
            $table->index('estado');
            $table->index('responsable_id');
        });

        Schema::create('documentos_software', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fase_id')->constrained('fases_software')->onDelete('cascade');
            $table->string('nombre_archivo', 200);
            $table->string('ruta_archivo', 500);
            $table->string('tipo_documento', 50);
            $table->string('mime_type', 100)->nullable();
            $table->integer('tamano_bytes')->default(0);
            $table->text('descripcion')->nullable();
            $table->foreignId('subido_por')->constrained('users')->onDelete('cascade');
            $table->timestamp('fecha_subida')->useCurrent();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index(['fase_id', 'activo']);
            $table->index('tipo_documento');
            $table->index('subido_por');
        });

        Schema::create('certificaciones_innovacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('software_id')->constrained('software_registrado')->onDelete('cascade');
            $table->string('entidad_certificadora', 200);
            $table->string('numero_certificado', 100)->nullable();
            $table->date('fecha_emision');
            $table->date('fecha_vigencia')->nullable();
            $table->enum('nivel_innovacion', ['Bajo', 'Medio', 'Alto', 'Muy Alto'])->default('Medio');
            $table->text('descripcion_innovacion')->nullable();
            $table->string('url_certificado', 500)->nullable();
            $table->string('ruta_documento', 500)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index(['software_id', 'activo']);
            $table->index('entidad_certificadora');
            $table->index('fecha_emision');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificaciones_innovacion');
        Schema::dropIfExists('documentos_software');
        Schema::dropIfExists('fases_software');
        Schema::dropIfExists('software_registrado');
    }
};
