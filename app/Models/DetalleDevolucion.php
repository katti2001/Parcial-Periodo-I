<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DetalleDevolucion
 *
 * @property int $id_detalle_devolucion
 * @property int $id_devolucion
 * @property int $id_detalle_pedido
 * @property int $cantidad_devuelta
 *
 * @property Devolucion    $devolucion
 * @property DetallePedido $detallePedido
 */
class DetalleDevolucion extends Model
{
    protected $table      = 'detalle_devoluciones';
    protected $primaryKey = 'id_detalle_devolucion';

    protected $casts = [
        'id_devolucion'     => 'int',
        'id_detalle_pedido' => 'int',
        'cantidad_devuelta' => 'int',
    ];

    protected $fillable = [
        'id_devolucion',
        'id_detalle_pedido',
        'cantidad_devuelta',
    ];

    public function devolucion()
    {
        return $this->belongsTo(Devolucion::class, 'id_devolucion');
    }

    public function detallePedido()
    {
        return $this->belongsTo(DetallePedido::class, 'id_detalle_pedido');
    }
}
