<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarifas', function (Blueprint $table) {
            $table->id();
            $table->string('categoria'); //  PRIMERA A, SUB 15
            $table->string('disciplina')->default('FUTBOL 11'); // FUTBOL 11 o FUTSAL
            $table->decimal('monto_principal', 10, 2)->default(0);  
            $table->decimal('monto_asistente', 10, 2)->default(0);  
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarifas');
    }
};