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
        Schema::table('partidos', function (Blueprint $table) {
       
            $table->integer('goles_local')->nullable()->after('equipo_visitante');
            $table->integer('goles_visitante')->nullable()->after('goles_local');
        });
    }

    public function down(): void
    {
        Schema::table('partidos', function (Blueprint $table) {
          
            $table->dropColumn(['goles_local', 'goles_visitante']);
        });
    }
};
