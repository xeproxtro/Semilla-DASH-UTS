<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('versiones_catalogo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('version', 20)->unique();
            $table->text('descripcion')->nullable();
            $table->date('fecha_vigencia_inicio');
            $table->date('fecha_vigencia_fin')->nullable();
            $table->boolean('vigente')->default(true);
            $table->string('fuente', 100)->default('Minciencias 2024');
            $table->timestamps();
            
            $table->index('version');
            $table->index('vigente');
            $table->index('fecha_vigencia_inicio');
        });

        Schema::create('categorias_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_catalogo_id')->constrained('versiones_catalogo')->onDelete('cascade');
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->enum('tipo_categoria', ['GNC', 'DTI', 'ASC-DPC', 'FRH'])->default('GNC');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['version_catalogo_id', 'activo']);
            $table->index('codigo');
            $table->index('tipo_categoria');
        });

        Schema::create('subtipos_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias_producto')->onDelete('cascade');
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 200);
            $table->text('definicion')->nullable();
            $table->integer('ventana_observacion_anios')->default(5);
            $table->integer('ventana_observacion_meses')->default(0);
            $table->boolean('requiere_validacion')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['categoria_id', 'activo']);
            $table->index('codigo');
            $table->index('ventana_observacion_anios');
        });

        Schema::create('campos_personalizados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subtipo_id')->constrained('subtipos_producto')->onDelete('cascade');
            $table->string('nombre_campo', 100);
            $table->string('tipo_dato', 50);
            $table->boolean('obligatorio')->default(false);
            $table->text('opciones')->nullable();
            $table->text('descripcion')->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index(['subtipo_id', 'activo']);
            $table->index('orden');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campos_personalizados');
        Schema::dropIfExists('subtipos_producto');
        Schema::dropIfExists('categorias_producto');
        Schema::dropIfExists('versiones_catalogo');
    }
};
