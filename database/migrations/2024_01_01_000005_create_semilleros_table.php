<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semilleros', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 200);
            $table->foreignId('coordinador_id')->constrained('users')->onDelete('restrict');
            $table->string('enfoque', 100);
            $table->text('descripcion')->nullable();
            $table->text('plan_formativo')->nullable();
            $table->string('email_contacto', 100)->nullable();
            $table->string('categoria', 50)->nullable();
            $table->date('fecha_creacion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('codigo');
            $table->index('coordinador_id');
            $table->index('activo');
            $table->index('categoria');
        });

        Schema::create('semillero_grupo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semillero_id')->constrained()->onDelete('cascade');
            $table->foreignId('grupo_id')->constrained()->onDelete('cascade');
            $table->date('fecha_articulacion');
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->unique(['semillero_id', 'grupo_id', 'fecha_articulacion']);
            $table->index(['semillero_id', 'activo']);
            $table->index(['grupo_id', 'activo']);
        });

        Schema::create('semillero_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semillero_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('rol', 50)->default('Integrante');
            $table->date('fecha_ingreso');
            $table->date('fecha_retiro')->nullable();
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->unique(['semillero_id', 'user_id', 'fecha_ingreso']);
            $table->index(['semillero_id', 'activo']);
            $table->index(['user_id', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semillero_user');
        Schema::dropIfExists('semillero_grupo');
        Schema::dropIfExists('semilleros');
    }
};
