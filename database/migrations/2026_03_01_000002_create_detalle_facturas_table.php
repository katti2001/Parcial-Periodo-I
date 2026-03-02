<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('detalle_facturas')) {
            return;
        }

        Schema::create('detalle_facturas', function (Blueprint $table) {
            $table->increments('id_detalle_factura');
            $table->unsignedInteger('id_factura');
            $table->unsignedInteger('id_producto');
            $table->unsignedInteger('id_talla');
            $table->unsignedInteger('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('total_linea', 10, 2)->default(0);

            $table->foreign('id_factura')->references('id_factura')->on('facturas')->cascadeOnDelete();
            $table->foreign('id_producto')->references('id_producto')->on('productos');
            $table->foreign('id_talla')->references('id_talla')->on('tallas');

            $table->index('id_factura', 'idx_detalle_facturas_factura');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_facturas');
    }
};
