<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('partidos', function (Blueprint $table) {
        $table->id();
        $table->string('categoria'); // PRIMERA A, SUB 21 A
        $table->string('equipo_local');
        $table->string('equipo_visitante');
        $table->string('cancha');
        $table->date('fecha');
        $table->time('hora_inicio');
        $table->time('hora_fin')->nullable(); // Para calcular superposs
        $table->enum('estado', ['borrador', 'publicado', 'finalizado'])->default('borrador');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partidos');
    }
};
