<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('facturas')) {
            return;
        }

        Schema::create('facturas', function (Blueprint $table) {
            $table->increments('id_factura');
            $table->unsignedInteger('id_pedido')->nullable();
            $table->unsignedInteger('id_usuario')->nullable();
            $table->string('numero', 50)->unique();
            $table->enum('estado', ['borrador', 'emitida', 'pagada', 'vencida', 'cancelada'])->default('emitida');
            $table->timestamp('fecha_emision')->useCurrent();
            $table->date('fecha_vencimiento')->nullable();
            $table->string('moneda', 10)->default('USD');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('impuesto', 10, 2)->default(0);
            $table->decimal('costo_envio', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->text('notas')->nullable();

            $table->foreign('id_pedido')->references('id_pedido')->on('pedidos')->nullOnDelete();
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->nullOnDelete();

            $table->index('id_usuario', 'idx_facturas_usuario');
            $table->index('estado', 'idx_facturas_estado');
            $table->index('fecha_vencimiento', 'idx_facturas_vencimiento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
