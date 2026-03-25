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
    Schema::create('noticias', function (Blueprint $table) {
        $table->id();
        $table->string('tipo');  
        $table->string('titulo');
        $table->text('contenido');
        $table->string('imagen_ruta')->nullable(); // f opcional
        $table->string('archivo_ruta')->nullable(); // PDF/Excel opcional
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // El admin que la publicó
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('noticias');
    }
};
