<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleFactura extends Model
{
    protected $table = 'detalle_facturas';
    protected $primaryKey = 'id_detalle_factura';
    public $timestamps = false;

    protected $casts = [
        'id_factura'      => 'int',
        'id_producto'     => 'int',
        'id_talla'        => 'int',
        'cantidad'        => 'int',
        'precio_unitario' => 'float',
        'total_linea'     => 'float',
    ];

    protected $fillable = [
        'id_factura',
        'id_producto',
        'id_talla',
        'cantidad',
        'precio_unitario',
    ];

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'id_factura');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function talla(): BelongsTo
    {
        return $this->belongsTo(Talla::class, 'id_talla');
    }
}
