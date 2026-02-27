<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kardex', function (Blueprint $table) {
            $table->increments('id_movimiento');
            $table->unsignedInteger('id_producto');
            $table->unsignedInteger('id_talla');
            $table->enum('tipo_movimiento', ['compra', 'venta']);
            $table->unsignedInteger('cantidad');
            $table->timestamp('fecha')->useCurrent();
            $table->string('referencia', 150)->nullable();

            $table->foreign('id_producto')->references('id_producto')->on('productos');
            $table->foreign('id_talla')->references('id_talla')->on('tallas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kardex');
    }
};
