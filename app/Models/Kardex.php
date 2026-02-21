<?php
/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Kardex
 *
 * @property int $id_movimiento
 * @property int $id_producto
 * @property int $id_talla
 * @property string $tipo_movimiento
 * @property int $cantidad
 * @property Carbon|null $fecha
 * @property string|null $referencia
 *
 * @property Producto $producto
 * @property Talla $talla
 *
 * @package App\Models
 */
class Kardex extends Model
{
    protected $table = 'kardex';
    protected $primaryKey = 'id_movimiento';
    public $timestamps = false;

    protected $casts = [
        'id_producto' => 'int',
        'id_talla'    => 'int',
        'cantidad'    => 'int',
        'fecha'       => 'datetime'
    ];

    protected $fillable = [
        'id_producto',
        'id_talla',
        'tipo_movimiento',
        'cantidad',
        'fecha',
        'referencia'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function talla()
    {
        return $this->belongsTo(Talla::class, 'id_talla');
    }
}
