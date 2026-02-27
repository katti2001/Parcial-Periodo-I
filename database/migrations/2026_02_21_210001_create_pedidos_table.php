<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->increments('id_pedido');
            $table->unsignedInteger('id_usuario')->nullable();
            $table->unsignedInteger('id_cupon')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('monto_descuento', 10, 2)->default(0);
            $table->decimal('costo_envio', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('moneda', 10)->default('USD');
            $table->string('estado_pago', 30)->nullable();
            $table->string('paypal_order_id', 100)->nullable();
            $table->string('paypal_payer_id', 100)->nullable();
            $table->enum('estado_pedido', ['pendiente', 'procesando', 'enviado', 'entregado', 'cancelado'])->default('procesando');
            $table->timestamp('fecha_pedido')->useCurrent();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->nullOnDelete();
            $table->foreign('id_cupon')->references('id_cupon')->on('cupones')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
