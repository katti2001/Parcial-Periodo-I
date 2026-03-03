<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devoluciones', function (Blueprint $table) {
            $table->increments('id_devolucion');

            $table->unsignedInteger('id_pedido');
            $table->unsignedInteger('id_usuario');

            $table->enum('estado', ['solicitado', 'aprobado', 'rechazado'])
                  ->default('solicitado');

            $table->enum('motivo', [
                'producto_defectuoso',
                'talla_incorrecta',
                'no_corresponde_descripcion',
                'no_llego',
                'cambio_opinion',
            ]);

            $table->text('descripcion')->nullable();

            $table->decimal('monto_reembolso', 10, 2)->nullable();

            $table->string('paypal_refund_id')->nullable();

            $table->text('notas_admin')->nullable();

            $table->timestamp('fecha_solicitud')->useCurrent();
            $table->timestamp('fecha_resolucion')->nullable();

            $table->timestamps();

            $table->foreign('id_pedido')->references('id_pedido')->on('pedidos');
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devoluciones');
    }
};
