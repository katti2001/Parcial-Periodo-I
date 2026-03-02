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
 * @property string $sku_base
 * @property string $nombre
 * @property string|null $descripcion
 * @property float $precio_venta_base
 * @property int|null $id_categoria
 * @property int|null $id_equipo
 * @property bool $activo
 *
 * @property Categoria|null $categoria
 * @property Equipo|null $equipo
 * @property Collection|DetalleCompra[] $detalle_compras
 * @property Collection|DetallePedido[] $detalle_pedidos
 * @property Collection|ImagenesProducto[] $imagenes_productos
 * @property Collection|Kardex[] $kardex
 *
 * @package App\Models
 */
class Producto extends Model
{
	protected $table = 'productos';
	protected $primaryKey = 'id_producto';
	public $timestamps = false;

	protected $casts = [
		'precio_venta_base' => 'float',
		'id_categoria'      => 'int',
		'id_equipo'         => 'int',
		'activo'            => 'bool'
	];

	protected $fillable = [
		'sku_base',
		'nombre',
		'descripcion',
		'precio_venta_base',
		'id_categoria',
		'id_equipo',
		'activo'
	];

	public function categoria()
	{
		return $this->belongsTo(Categoria::class, 'id_categoria');
	}

	public function equipo()
	{
		return $this->belongsTo(Equipo::class, 'id_equipo');
	}

	public function detalle_compras()
	{
		return $this->hasMany(DetalleCompra::class, 'id_producto');
	}

	public function getPrecioCalculadoAttribute()
	{
		$primerLote = $this->detalle_compras()->orderBy('id_detalle_compra', 'asc')->first();
		$primerCosto = $primerLote ? (float) $primerLote->costo_unitario : 0;

		$loteActual = $this->detalle_compras()->where('cantidad_restante', '>', 0)
			->orderBy('id_detalle_compra', 'asc')->first();

		if ($loteActual && $primerCosto > 0) {
			$margen = $this->precio_venta_base / $primerCosto;
			return round($loteActual->costo_unitario * $margen, 2);
		}

		return (float) $this->precio_venta_base;
	}

	public function detalle_pedidos()
	{
		return $this->hasMany(DetallePedido::class, 'id_producto');
	}

	public function imagenes_productos()
	{
		return $this->hasMany(ImagenesProducto::class, 'id_producto');
	}

	public function kardex()
	{
		return $this->hasMany(Kardex::class, 'id_producto');
	}
}
