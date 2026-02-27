<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipos', function (Blueprint $table) {
            $table->increments('id_equipo');
            $table->string('nombre', 100)->unique();
            $table->string('pais', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};
