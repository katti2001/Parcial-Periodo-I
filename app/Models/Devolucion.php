<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class Devolucion extends Model
{
    protected $table      = 'devoluciones';
    protected $primaryKey = 'id_devolucion';

    protected $casts = [
        'id_pedido'        => 'int',
        'id_usuario'       => 'int',
        'monto_reembolso'  => 'float',
        'fecha_solicitud'  => 'datetime',
        'fecha_resolucion' => 'datetime',
    ];

    protected $fillable = [
        'id_pedido',
        'id_usuario',
        'estado',
        'motivo',
        'descripcion',
        'monto_reembolso',
        'paypal_refund_id',
        'notas_admin',
        'fecha_solicitud',
        'fecha_resolucion',
    ];

    public const MOTIVOS = [
        'producto_defectuoso'        => 'Producto defectuoso',
        'talla_incorrecta'           => 'Talla incorrecta',
        'no_corresponde_descripcion' => 'No corresponde a la descripción',
        'no_llego'                   => 'No llegó el pedido',
        'cambio_opinion'             => 'Cambio de opinión',
    ];

    public const MOTIVOS_SIN_STOCK = ['producto_defectuoso'];

    public function scopeSolicitado($query)
    {
        return $query->where('estado', 'solicitado');
    }

    public function scopeAprobado($query)
    {
        return $query->where('estado', 'aprobado');
    }

    public function scopeRechazado($query)
    {
        return $query->where('estado', 'rechazado');
    }

    public function regresaAlStock(): bool
    {
        return !in_array($this->motivo, self::MOTIVOS_SIN_STOCK);
    }

    public function motivoLegible(): string
    {
        return self::MOTIVOS[$this->motivo] ?? $this->motivo;
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleDevolucion::class, 'id_devolucion');
    }
}
