<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
	protected $table = 'pedidos';
	protected $primaryKey = 'id_pedido';
	public $timestamps = false;

	protected $casts = [
		'id_usuario'      => 'int',
		'id_cupon'        => 'int',
		'total'           => 'float',
		'subtotal'        => 'float',
		'monto_descuento' => 'float',
		'costo_envio'     => 'float',
		'fecha_pedido'    => 'datetime'
	];

	protected $fillable = [
		'id_usuario',
		'id_cupon',
		'total',
		'subtotal',
		'monto_descuento',
		'costo_envio',
		'estado_pago',
		'paypal_order_id',
		'paypal_payer_id',
		'moneda',
		'estado_pedido',
		'fecha_pedido'
	];

	public function usuario()
	{
		return $this->belongsTo(Usuario::class, 'id_usuario');
	}

	public function cupon()
	{
		return $this->belongsTo(Cupon::class, 'id_cupon');
	}

    public function detalle_pedidos()
    {
        return $this->hasMany(DetallePedido::class, 'id_pedido');
    }

    public function devolucion()
    {
        return $this->hasOne(Devolucion::class, 'id_pedido');
    }

    public function factura()
    {
        return $this->hasOne(Factura::class, 'id_pedido');
    }
}
