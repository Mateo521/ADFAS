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
    Schema::create('designaciones', function (Blueprint $table) {
        $table->id();
        // Claves foráneas 
        $table->foreignId('partido_id')->constrained()->onDelete('cascade');
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // El árbitro
        
        $table->string('funcion')->nullable(); // Ej: ARBITRO, ASISTENTE 1, 4to ARBITRO
        $table->enum('estado_confirmacion', ['pendiente', 'confirmado', 'rechazado'])->default('pendiente');
        
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('designacions');
    }
};
