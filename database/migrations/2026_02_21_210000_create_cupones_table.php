<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cupones', function (Blueprint $table) {
            $table->increments('id_cupon');
            $table->string('codigo', 30)->unique();
            $table->enum('tipo', ['porcentaje', 'fixed']);
            $table->decimal('valor', 10, 2);
            $table->date('fecha_vencimiento')->nullable();
            $table->boolean('activo')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cupones');
    }
};
