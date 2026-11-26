<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tareas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $t->string('nombre', 150);
            $t->text('descripcion')->nullable();
            $t->date('fecha_inicio');
            $t->date('fecha_fin');
            $t->enum('estado', ['Pendiente', 'En progreso', 'Completada'])->default('Pendiente');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tareas');
    }
};