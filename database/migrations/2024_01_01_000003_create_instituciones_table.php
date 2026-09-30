<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instituciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->string('nit', 20)->unique();
            $table->string('tipo', 50);
            $table->string('direccion', 200)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('pais', 50)->default('Colombia');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('nit');
            $table->index('activo');
        });

        Schema::create('vinculaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('institucion_id')->constrained()->onDelete('cascade');
            $table->string('cargo', 100)->nullable();
            $table->string('tipo_vinculacion', 50);
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->boolean('vinculacion_actual')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'vinculacion_actual']);
            $table->index(['institucion_id', 'vinculacion_actual']);
            $table->index('fecha_inicio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vinculaciones');
        Schema::dropIfExists('instituciones');
    }
};
