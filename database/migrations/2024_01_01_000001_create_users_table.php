<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('email')->unique();
            $table->string('tipo_documento', 20);
            $table->string('numero_documento', 20)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('orcid_id', 50)->nullable()->unique();
            $table->string('cvlac_id', 50)->nullable()->unique();
            $table->boolean('activo')->default(true);
            $table->timestamp('ultimo_acceso')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['tipo_documento', 'numero_documento']);
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
