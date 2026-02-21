<?php
/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DetalleCompra
 *
 * @property int $id_detalle_compra
 * @property int $id_compra
 * @property int $id_producto
 * @property int $id_talla
 * @property int $cantidad_comprada
 * @property int $cantidad_restante
 * @property float $costo_unitario
 *
 * @property Compra $compra
 * @property Producto $producto
 * @property Talla $talla
 *
 * @package App\Models
 */
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
