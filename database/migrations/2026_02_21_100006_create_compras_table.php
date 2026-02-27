<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->increments('id_compra');
            $table->unsignedInteger('id_proveedor')->nullable();
            $table->timestamp('fecha_compra')->useCurrent();
            $table->decimal('total_compra', 10, 2)->default(0);
            $table->string('numero_factura_proveedor', 50)->nullable();
            $table->enum('estado', ['solicitado', 'recibido', 'cancelado'])->default('solicitado');

            $table->foreign('id_proveedor')->references('id_proveedor')->on('proveedores')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
