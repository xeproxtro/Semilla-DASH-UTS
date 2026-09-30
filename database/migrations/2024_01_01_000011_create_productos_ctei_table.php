<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos_ctei', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_interno', 50)->unique();
            $table->foreignId('subtipo_id')->constrained('subtipos_producto')->onDelete('restrict');
            $table->string('titulo', 500);
            $table->text('descripcion')->nullable();
            $table->date('fecha_publicacion');
            $table->date('fecha_registro')->nullable();
            $table->enum('estado', [
                'Borrador',
                'Enviado',
                'Devuelto',
                'Revisado',
                'Avalado',
                'Reportado'
            ])->default('Borrador');
            $table->string('doi', 100)->nullable()->unique();
            $table->string('isbn', 20)->nullable()->unique();
            $table->string('issn', 20)->nullable()->unique();
            $table->string('patente', 50)->nullable()->unique();
            $table->string('registro', 50)->nullable()->unique();
            $table->string('handle', 100)->nullable()->unique();
            $table->string('url', 500)->nullable();
            $table->text('palabras_clave')->nullable();
            $table->string('idioma', 50)->default('Español');
            $table->string('ciudad', 100)->nullable();
            $table->string('pais', 50)->default('Colombia');
            $table->integer('total_autores')->default(0);
            $table->decimal('presupuesto_inversion', 15, 2)->default(0);
            $table->string('moneda', 10)->default('COP');
            $table->boolean('visible_publicamente')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('codigo_interno');
            $table->index('subtipo_id');
            $table->index('estado');
            $table->index('fecha_publicacion');
            $table->index('doi');
            $table->index('isbn');
            $table->index('issn');
            $table->index('patente');
            $table->index('registro');
            $table->index('handle');
        });

        Schema::create('producto_autor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos_ctei')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('orden_autoria')->default(0);
            $table->string('rol_autoria', 50)->default('Autor');
            $table->boolean('autor_correspondencia')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->unique(['producto_id', 'user_id']);
            $table->index(['producto_id', 'activo']);
            $table->index(['user_id', 'activo']);
            $table->index('orden_autoria');
        });

        Schema::create('producto_grupo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos_ctei')->onDelete('cascade');
            $table->foreignId('grupo_id')->constrained()->onDelete('cascade');
            $table->boolean('grupo_principal')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->unique(['producto_id', 'grupo_id']);
            $table->index(['producto_id', 'activo']);
            $table->index(['grupo_id', 'activo']);
            $table->index('grupo_principal');
        });

        Schema::create('producto_semillero', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos_ctei')->onDelete('cascade');
            $table->foreignId('semillero_id')->constrained()->onDelete('cascade');
            $table->boolean('semillero_principal')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->unique(['producto_id', 'semillero_id']);
            $table->index(['producto_id', 'activo']);
            $table->index(['semillero_id', 'activo']);
            $table->index('semillero_principal');
        });

        Schema::create('producto_proyecto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos_ctei')->onDelete('cascade');
            $table->foreignId('proyecto_id')->constrained()->onDelete('cascade');
            $table->boolean('resultado_principal')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->unique(['producto_id', 'proyecto_id']);
            $table->index(['producto_id', 'activo']);
            $table->index(['proyecto_id', 'activo']);
            $table->index('resultado_principal');
        });

        Schema::create('producto_institucion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos_ctei')->onDelete('cascade');
            $table->foreignId('institucion_id')->constrained()->onDelete('cascade');
            $table->enum('tipo_participacion', ['Productora', 'Coeditora', 'Financiadora'])->default('Productora');
            $table->boolean('institucion_principal')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->unique(['producto_id', 'institucion_id']);
            $table->index(['producto_id', 'activo']);
            $table->index(['institucion_id', 'activo']);
            $table->index('institucion_principal');
        });

        Schema::create('producto_financiacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos_ctei')->onDelete('cascade');
            $table->string('fuente', 200);
            $table->string('tipo', 50);
            $table->decimal('monto', 15, 2)->default(0);
            $table->string('moneda', 10)->default('COP');
            $table->string('contrato_convenio', 100)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index(['producto_id', 'activo']);
            $table->index('fuente');
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_financiacion');
        Schema::dropIfExists('producto_institucion');
        Schema::dropIfExists('producto_proyecto');
        Schema::dropIfExists('producto_semillero');
        Schema::dropIfExists('producto_grupo');
        Schema::dropIfExists('producto_autor');
        Schema::dropIfExists('productos_ctei');
    }
};
