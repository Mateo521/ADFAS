<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ajustes', function (Blueprint $table) {
            $table->id();
            $table->decimal('cuota_monto', 10, 2)->default(15000);
            $table->integer('cuota_dia_vencimiento')->default(10);
            $table->timestamps();
        });


        DB::table('ajustes')->insert(['cuota_monto' => 15000, 'cuota_dia_vencimiento' => 10]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ajustes');
    }
};
