<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos_ctei')->onDelete('cascade');
            $table->foreignId('subido_por')->constrained('users')->onDelete('cascade');
            $table->enum('tipo', ['Archivo', 'Enlace', 'Documento', 'Video', 'Audio', 'Imagen', 'Otro'])->default('Archivo');
            $table->string('nombre', 300);
            $table->string('ruta_archivo', 500)->nullable();
            $table->string('url_enlace', 500)->nullable();
            $table->string('version', 20)->default('1.0');
            $table->text('descripcion')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->integer('tamano_bytes')->default(0);
            $table->string('hash_integridad', 64)->nullable();
            $table->string('algoritmo_hash', 20)->default('sha256');
            $table->enum('nivel_acceso', ['Privado', 'Interno', 'Publico'])->default('Privado');
            $table->date('fecha_documento')->nullable();
            $table->string('registro_soportado', 100)->nullable();
            $table->boolean('validado')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['producto_id', 'activo']);
            $table->index('tipo');
            $table->index('nivel_acceso');
            $table->index('fecha_documento');
            $table->index('subido_por');
        });

        Schema::create('listas_chequeo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias_producto')->onDelete('set null');
            $table->foreignId('subtipo_id')->nullable()->constrained('subtipos_producto')->onDelete('set null');
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->enum('tipo_lista', ['Completitud', 'Calidad', 'Legal', 'Tecnico'])->default('Completitud');
            $table->boolean('obligatoria')->default(true);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['categoria_id', 'activo']);
            $table->index(['subtipo_id', 'activo']);
            $table->index('tipo_lista');
        });

        Schema::create('items_lista_chequeo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lista_chequeo_id')->constrained('listas_chequeo')->onDelete('cascade');
            $table->string('nombre', 300);
            $table->text('descripcion')->nullable();
            $table->enum('tipo_item', ['Check', 'Texto', 'Numero', 'Fecha', 'Archivo'])->default('Check');
            $table->boolean('obligatorio')->default(true);
            $table->integer('orden')->default(0);
            $table->text('opciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index(['lista_chequeo_id', 'activo']);
            $table->index('orden');
        });

        Schema::create('revisiones_expedientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos_ctei')->onDelete('cascade');
            $table->foreignId('lista_chequeo_id')->nullable()->constrained('listas_chequeo')->onDelete('set null');
            $table->enum('estado', [
                'Borrador',
                'Enviado',
                'Devuelto',
                'Revisado',
                'Avalado',
                'Reportado',
                'Validado_Externamente',
                'Rechazado',
                'Anulado'
            ])->default('Borrador');
            $table->date('fecha_estado')->nullable();
            $table->text('observaciones')->nullable();
            $table->integer('porcentaje_completitud')->default(0);
            $table->boolean('cumple_requisitos')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['producto_id', 'activo']);
            $table->index('estado');
            $table->index('fecha_estado');
        });

        Schema::create('respuestas_revision', function (Blueprint $table) {
            $table->id();
            $table->foreignId('revision_id')->constrained('revisiones_expedientes')->onDelete('cascade');
            $table->foreignId('item_lista_id')->constrained('items_lista_chequeo')->onDelete('cascade');
            $table->text('respuesta')->nullable();
            $table->boolean('cumple')->default(false);
            $table->text('observaciones')->nullable();
            $table->foreignId('respondido_por')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('fecha_respuesta')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index(['revision_id', 'activo']);
            $table->index('item_lista_id');
            $table->index('respondido_por');
        });

        Schema::create('historial_estados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos_ctei')->onDelete('cascade');
            $table->foreignId('realizado_por')->constrained('users')->onDelete('cascade');
            $table->enum('accion', [
                'Crear',
                'Enviar',
                'Devolver',
                'Revisar',
                'Aprobar',
                'Rechazar',
                'Reportar',
                'Validar',
                'Anular'
            ]);
            $table->string('estado_anterior', 50)->nullable();
            $table->string('estado_nuevo', 50);
            $table->text('observaciones')->nullable();
            $table->string('rol_usuario', 50)->nullable();
            $table->timestamp('fecha_accion')->useCurrent();
            $table->string('direccion_ip', 45)->nullable();
            $table->text('metadata')->nullable();
            
            $table->index(['producto_id', 'fecha_accion']);
            $table->index('realizado_por');
            $table->index('accion');
            $table->index('estado_nuevo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_estados');
        Schema::dropIfExists('respuestas_revision');
        Schema::dropIfExists('revisiones_expedientes');
        Schema::dropIfExists('items_lista_chequeo');
        Schema::dropIfExists('listas_chequeo');
        Schema::dropIfExists('evidencias');
    }
};
