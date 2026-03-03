<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleCompra extends Model
{
    protected $table = 'detalle_compras';
    protected $primaryKey = 'id_detalle_compra';
    public $timestamps = false;

    protected $casts = [
        'id_compra'          => 'int',
        'id_producto'        => 'int',
        'id_talla'           => 'int',
        'cantidad_comprada'  => 'int',
        'cantidad_restante'  => 'int',
        'costo_unitario'     => 'float'
    ];

    protected $fillable = [
        'id_compra',
        'id_producto',
        'id_talla',
        'sku_lote',
        'cantidad_comprada',
        'cantidad_restante',
        'costo_unitario'
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'id_compra');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function talla()
    {
        return $this->belongsTo(Talla::class, 'id_talla');
    }
}
