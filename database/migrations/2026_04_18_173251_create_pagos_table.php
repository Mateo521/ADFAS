<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();


            $table->integer('mes');
            $table->integer('anio');

            $table->decimal('monto', 10, 2);
            $table->enum('estado', ['pendiente', 'pagado'])->default('pendiente');


            $table->string('metodo_pago')->nullable();


            $table->string('mp_preference_id')->nullable();
            $table->string('mp_payment_id')->nullable();

            $table->timestamp('fecha_pago')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};