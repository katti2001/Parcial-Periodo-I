<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DetallePedido
 *
 * @property int $id_detalle_pedido
 * @property int $id_pedido
 * @property int $id_producto
 * @property int $id_talla
 * @property int $cantidad
 * @property float $precio_venta_unitario
 *
 * @property Pedido $pedido
 * @property Producto $producto
 * @property Talla $talla
 *
 * @package App\Models
 */
class DetallePedido extends Model
{
	protected $table = 'detalle_pedidos';
	protected $primaryKey = 'id_detalle_pedido';
	public $timestamps = false;

	protected $casts = [
		'id_pedido'             => 'int',
		'id_producto'           => 'int',
		'id_talla'              => 'int',
		'cantidad'              => 'int',
		'precio_venta_unitario' => 'float'
	];

	protected $fillable = [
		'id_pedido',
		'id_producto',
		'id_talla',
		'cantidad',
		'precio_venta_unitario'
	];

	public function pedido()
	{
		return $this->belongsTo(Pedido::class, 'id_pedido');
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
