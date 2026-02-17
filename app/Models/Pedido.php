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
 * @property float $total
 * @property string|null $estado_pago
 * @property string|null $paypal_order_id
 * @property Carbon|null $fecha_pedido
 * 
 * @property Usuario|null $usuario
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
		'id_usuario' => 'int',
		'total' => 'float',
		'fecha_pedido' => 'datetime'
	];

	protected $fillable = [
		'id_usuario',
		'total',
		'estado_pago',
		'paypal_order_id',
		'fecha_pedido'
	];

	public function usuario()
	{
		return $this->belongsTo(Usuario::class, 'id_usuario');
	}

	public function detalle_pedidos()
	{
		return $this->hasMany(DetallePedido::class, 'id_pedido');
	}
}
