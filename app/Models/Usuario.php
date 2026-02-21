<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class Usuario
 *
 * @property int $id_usuario
 * @property string $nombre
 * @property string $apellido
 * @property string $email
 * @property string $password
 * @property string|null $telefono
 * @property string|null $direccion_envio
 * @property string|null $rol
 * @property Carbon|null $fecha_registro
 *
 * @property Collection|Pedido[] $pedidos
 *
 * @package App\Models
 */
class Usuario extends Authenticatable
{
	use Notifiable;

	protected $table = 'usuarios';
	protected $primaryKey = 'id_usuario';
	public $timestamps = false;

	protected $casts = [
		'fecha_registro' => 'datetime'
	];

	protected $hidden = [
		'password'
	];

	protected $fillable = [
		'nombre',
		'apellido',
		'email',
		'password',
		'telefono',
		'direccion_envio',
		'rol',
		'fecha_registro'
	];

	public function esAdmin(): bool
	{
		return $this->rol === 'admin';
	}

	public function esAlmacen(): bool
	{
		return $this->rol === 'almacen';
	}

	public function esCliente(): bool
	{
		return $this->rol === 'cliente';
	}

	public function pedidos()
	{
		return $this->hasMany(Pedido::class, 'id_usuario');
	}
}
