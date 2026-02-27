<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imagenes_productos', function (Blueprint $table) {
            $table->increments('id_imagen');
            $table->unsignedInteger('id_producto');
            $table->string('url_imagen', 500);
            $table->boolean('es_principal')->default(false);

            $table->foreign('id_producto')->references('id_producto')->on('productos')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imagenes_productos');
    }
};
