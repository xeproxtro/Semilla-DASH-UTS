<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cortes_historicos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->string('version', 20)->unique();
            $table->date('fecha_corte');
            $table->date('fecha_inicio_periodo');
            $table->date('fecha_fin_periodo');
            $table->foreignId('version_catalogo_id')->constrained('versiones_catalogo')->onDelete('restrict');
            $table->foreignId('creado_por')->constrained('users')->onDelete('cascade');
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['Abierto', 'Cerrado', 'Archivado'])->default('Abierto');
            $table->json('instituciones_incluidas')->nullable();
            $table->json('grupos_incluidos')->nullable();
            $table->json('semilleros_incluidos')->nullable();
            $table->json('reglas_ventanas')->nullable();
            $table->integer('total_productos')->default(0);
            $table->integer('productos_dentro_ventana')->default(0);
            $table->integer('productos_proximo_vencer')->default(0);
            $table->integer('productos_fuera_ventana')->default(0);
            $table->integer('expedientes_incompletos')->default(0);
            $table->timestamp('fecha_congelamiento')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('version');
            $table->index('fecha_corte');
            $table->index('estado');
            $table->index('version_catalogo_id');
            $table->index('creado_por');
        });

        Schema::create('detalle_corte_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corte_id')->constrained('cortes_historicos')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos_ctei')->onDelete('cascade');
            $table->foreignId('subtipo_id')->constrained('subtipos_producto')->onDelete('restrict');
            $table->enum('estado_ventana', ['dentro_ventana', 'proximo_vencer', 'fuera_ventana', 'no_determinable'])->default('no_determinable');
            $table->enum('estado_expediente', ['Borrador', 'Enviado', 'Devuelto', 'Revisado', 'Avalado', 'Reportado', 'Validado_Externamente', 'Rechazado', 'Anulado'])->nullable();
            $table->integer('dias_ventana')->nullable();
            $table->boolean('es_elegible')->default(false);
            $table->boolean('expediente_completo')->default(false);
            $table->text('observaciones')->nullable();
            $table->json('metadatos_congelados')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->unique(['corte_id', 'producto_id']);
            $table->index(['corte_id', 'estado_ventana']);
            $table->index(['corte_id', 'estado_expediente']);
            $table->index('es_elegible');
        });

        Schema::create('metricas_corte', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corte_id')->constrained('cortes_historicos')->onDelete('cascade');
            $table->string('tipo_metrica', 100);
            $table->string('categoria', 50)->nullable();
            $table->string('subcategoria', 50)->nullable();
            $table->integer('valor')->default(0);
            $table->decimal('valor_decimal', 15, 2)->nullable();
            $table->text('descripcion')->nullable();
            $table->json('desglose')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index(['corte_id', 'tipo_metrica']);
            $table->index('categoria');
        });

        Schema::create('alertas_tablero', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corte_id')->nullable()->constrained('cortes_historicos')->onDelete('set null');
            $table->enum('tipo_alerta', ['Producto_Vencer', 'Expediente_Incompleto', 'Ventana_Caducada', 'Sin_Aval', 'Requerido']);
            $table->foreignId('producto_id')->nullable()->constrained('productos_ctei')->onDelete('cascade');
            $table->foreignId('grupo_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('semillero_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('titulo', 300);
            $table->text('descripcion')->nullable();
            $table->enum('prioridad', ['Baja', 'Media', 'Alta', 'Critica'])->default('Media');
            $table->date('fecha_alerta');
            $table->date('fecha_resolucion')->nullable();
            $table->boolean('resuelta')->default(false);
            $table->foreignId('asignado_a')->nullable()->constrained('users')->onDelete('set null');
            $table->text('acciones_requeridas')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['corte_id', 'tipo_alerta']);
            $table->index('producto_id');
            $table->index('grupo_id');
            $table->index('semillero_id');
            $table->index('prioridad');
            $table->index('fecha_alerta');
            $table->index('resuelta');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas_tablero');
        Schema::dropIfExists('metricas_corte');
        Schema::dropIfExists('detalle_corte_producto');
        Schema::dropIfExists('cortes_historicos');
    }
};
