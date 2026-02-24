<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_devoluciones', function (Blueprint $table) {
            $table->increments('id_detalle_devolucion');

            $table->unsignedInteger('id_devolucion');
            $table->unsignedInteger('id_detalle_pedido');

            // Debe ser <= cantidad original del detalle_pedido
            $table->unsignedInteger('cantidad_devuelta');

            $table->timestamps();

            $table->foreign('id_devolucion')
                  ->references('id_devolucion')
                  ->on('devoluciones')
                  ->onDelete('cascade');

            $table->foreign('id_detalle_pedido')
                  ->references('id_detalle_pedido')
                  ->on('detalle_pedidos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_devoluciones');
    }
};
