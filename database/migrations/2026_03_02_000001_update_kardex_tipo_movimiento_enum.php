<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE kardex MODIFY tipo_movimiento ENUM('compra', 'venta', 'devolucion', 'ajuste_inventario') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE kardex MODIFY tipo_movimiento ENUM('compra', 'venta') NOT NULL");
    }
};
