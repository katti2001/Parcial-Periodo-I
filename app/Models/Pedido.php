<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Pedido
 *
 * @property int $id_pedido
 * @property int|null $id_usuario
 * @property int|null $id_cupon
 * @property float $total
 * @property float $subtotal
 * @property float $monto_descuento
 * @property float $costo_envio
 * @property string|null $estado_pago
 * @property string|null $paypal_order_id
 * @property string|null $paypal_payer_id
 * @property string $moneda
 * @property string|null $estado_pedido
 * @property Carbon|null $fecha_pedido
 *
 * @property Usuario|null $usuario
 * @property Cupon|null $cupon
 * @property Collection|DetallePedido[] $detalle_pedidos
 *
 * @package App\Models
 */
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
}
