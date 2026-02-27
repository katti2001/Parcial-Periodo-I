<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_pedidos', function (Blueprint $table) {
            $table->increments('id_detalle_pedido');
            $table->unsignedInteger('id_pedido');
            $table->unsignedInteger('id_producto');
            $table->unsignedInteger('id_talla');
            $table->unsignedInteger('cantidad');
            $table->decimal('precio_unitario', 10, 2);

            $table->foreign('id_pedido')->references('id_pedido')->on('pedidos')->cascadeOnDelete();
            $table->foreign('id_producto')->references('id_producto')->on('productos');
            $table->foreign('id_talla')->references('id_talla')->on('tallas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_pedidos');
    }
};
