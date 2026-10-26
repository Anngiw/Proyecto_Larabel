<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    //Ejecuta las migraciones. Crea la tabla usuarios en PostgreSQL.
     
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('correo', 150)->unique();
            $table->string('password', 255); // Almacena el hash seguro de la contraseña
            $table->integer('intentos_fallidos')->default(0); // Control de seguridad
            $table->timestamp('bloqueado_hasta')->nullable(); // Bloqueo temporal por intentos fallidos 
            $table->timestamps(); // Genera created_at y updated_at automáticamente
        });
    }

    //Revierte las migraciones.
     
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};