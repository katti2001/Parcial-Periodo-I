<?php
/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Equipo
 *
 * @property int $id_equipo
 * @property string $nombre
 * @property string|null $pais
 *
 * @property Collection|Producto[] $productos
 *
 * @package App\Models
 */
class Equipo extends Model
{
    protected $table = 'equipos';
    protected $primaryKey = 'id_equipo';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'pais'
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_equipo');
    }
}
