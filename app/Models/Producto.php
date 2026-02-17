<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Producto
 * 
 * @property int $id_producto
 * @property string $sku
 * @property string $nombre
 * @property float $precio
 * @property int $stock
 * @property string|null $argumentos_venta
 * @property int|null $id_categoria
 * 
 * @property Categoria|null $categoria
 * @property Collection|DetallePedido[] $detalle_pedidos
 * @property Collection|ImagenesProducto[] $imagenes_productos
 *
 * @package App\Models
 */
class Producto extends Model
{
	protected $table = 'productos';
	protected $primaryKey = 'id_producto';
	public $timestamps = false;

	protected $casts = [
		'precio' => 'float',
		'stock' => 'int',
		'id_categoria' => 'int'
	];

	protected $fillable = [
		'sku',
		'nombre',
		'precio',
		'stock',
		'argumentos_venta',
		'id_categoria'
	];

	public function categoria()
	{
		return $this->belongsTo(Categoria::class, 'id_categoria');
	}

	public function detalle_pedidos()
	{
		return $this->hasMany(DetallePedido::class, 'id_producto');
	}

	public function imagenes_productos()
	{
		return $this->hasMany(ImagenesProducto::class, 'id_producto');
	}
}
