<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->increments('id_producto');
            $table->string('sku_base', 20)->unique();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->decimal('precio_venta_base', 10, 2);
            $table->unsignedInteger('id_categoria')->nullable();
            $table->unsignedInteger('id_equipo')->nullable();
            $table->boolean('activo')->default(true);

            $table->foreign('id_categoria')->references('id_categoria')->on('categorias')->nullOnDelete();
            $table->foreign('id_equipo')->references('id_equipo')->on('equipos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
