<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Categoria
 * 
 * @property int $id_categoria
 * @property string $nombre
 * @property string|null $descripcion
 * 
 * @property Collection|Producto[] $productos
 *
 * @package App\Models
 */
class Categoria extends Model
{
	protected $table = 'categorias';
	protected $primaryKey = 'id_categoria';
	public $timestamps = false;

	protected $fillable = [
		'nombre',
		'descripcion'
	];

	public function productos()
	{
		return $this->hasMany(Producto::class, 'id_categoria');
	}
}
