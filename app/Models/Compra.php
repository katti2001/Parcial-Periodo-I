<?php
/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Compra
 *
 * @property int $id_compra
 * @property int|null $id_proveedor
 * @property Carbon|null $fecha_compra
 * @property float $total_compra
 * @property string|null $numero_factura_proveedor
 * @property string|null $estado
 *
 * @property Proveedor|null $proveedor
 * @property Collection|DetalleCompra[] $detalle_compras
 *
 * @package App\Models
 */
class Compra extends Model
{
    protected $table = 'compras';
    protected $primaryKey = 'id_compra';
    public $timestamps = false;

    protected $casts = [
        'id_proveedor' => 'int',
        'total_compra' => 'float',
        'fecha_compra' => 'datetime'
    ];

    protected $fillable = [
        'id_proveedor',
        'fecha_compra',
        'total_compra',
        'numero_factura_proveedor',
        'estado'
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }

    public function detalle_compras()
    {
        return $this->hasMany(DetalleCompra::class, 'id_compra');
    }
}
