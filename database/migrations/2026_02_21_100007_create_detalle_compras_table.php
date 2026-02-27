<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_compras', function (Blueprint $table) {
            $table->increments('id_detalle_compra');
            $table->unsignedInteger('id_compra');
            $table->unsignedInteger('id_producto');
            $table->unsignedInteger('id_talla');
            $table->unsignedInteger('cantidad_comprada');
            $table->unsignedInteger('cantidad_restante');
            $table->decimal('costo_unitario', 10, 2);

            $table->foreign('id_compra')->references('id_compra')->on('compras')->cascadeOnDelete();
            $table->foreign('id_producto')->references('id_producto')->on('productos');
            $table->foreign('id_talla')->references('id_talla')->on('tallas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_compras');
    }
};
