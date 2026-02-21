<?php
/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Cupon
 *
 * @property int $id_cupon
 * @property string $codigo
 * @property string $tipo_descuento
 * @property float $valor
 * @property Carbon|null $fecha_expiracion
 * @property bool $activo
 *
 * @property Collection|Pedido[] $pedidos
 *
 * @package App\Models
 */
class Cupon extends Model
{
    protected $table = 'cupones';
    protected $primaryKey = 'id_cupon';
    public $timestamps = false;

    protected $casts = [
        'valor'             => 'float',
        'activo'            => 'bool',
        'fecha_expiracion'  => 'datetime'
    ];

    protected $fillable = [
        'codigo',
        'tipo_descuento',
        'valor',
        'fecha_expiracion',
        'activo'
    ];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'id_cupon');
    }
}
