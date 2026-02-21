<?php
/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Proveedor
 *
 * @property int $id_proveedor
 * @property string $nombre_empresa
 * @property string|null $contacto
 * @property string|null $telefono
 * @property string|null $email
 *
 * @property Collection|Compra[] $compras
 *
 * @package App\Models
 */
class Proveedor extends Model
{
    protected $table = 'proveedores';
    protected $primaryKey = 'id_proveedor';
    public $timestamps = false;

    protected $fillable = [
        'nombre_empresa',
        'contacto',
        'telefono',
        'email'
    ];

    public function compras()
    {
        return $this->hasMany(Compra::class, 'id_proveedor');
    }
}
