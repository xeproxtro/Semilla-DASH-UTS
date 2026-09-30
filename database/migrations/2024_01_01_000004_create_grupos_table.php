<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 200);
            $table->foreignId('lider_id')->constrained('users')->onDelete('restrict');
            $table->string('gruplac_id', 50)->nullable()->unique();
            $table->text('mision')->nullable();
            $table->text('vision')->nullable();
            $table->text('plan_estrategico')->nullable();
            $table->string('categoria', 50)->nullable();
            $table->date('fecha_constitucion')->nullable();
            $table->string('email_contacto', 100)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('codigo');
            $table->index('lider_id');
            $table->index('activo');
            $table->index('categoria');
        });

        Schema::create('grupo_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('rol', 50)->default('Investigador');
            $table->date('fecha_ingreso');
            $table->date('fecha_retiro')->nullable();
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->unique(['grupo_id', 'user_id', 'fecha_ingreso']);
            $table->index(['grupo_id', 'activo']);
            $table->index(['user_id', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupo_user');
        Schema::dropIfExists('grupos');
    }
};
