<?php
/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Talla
 *
 * @property int $id_talla
 * @property string $nombre
 *
 * @property Collection|DetalleCompra[] $detalle_compras
 * @property Collection|DetallePedido[] $detalle_pedidos
 * @property Collection|Kardex[] $kardex
 *
 * @package App\Models
 */
class Talla extends Model
{
    protected $table = 'tallas';
    protected $primaryKey = 'id_talla';
    public $timestamps = false;

    protected $fillable = [
        'nombre'
    ];

    public function detalle_compras()
    {
        return $this->hasMany(DetalleCompra::class, 'id_talla');
    }

    public function detalle_pedidos()
    {
        return $this->hasMany(DetallePedido::class, 'id_talla');
    }

    public function kardex()
    {
        return $this->hasMany(Kardex::class, 'id_talla');
    }
}
