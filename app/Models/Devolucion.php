<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Devolucion
 *
 * @property int         $id_devolucion
 * @property int         $id_pedido
 * @property int         $id_usuario
 * @property string      $estado          solicitado|aprobado|rechazado
 * @property string      $motivo
 * @property string|null $descripcion
 * @property float|null  $monto_reembolso
 * @property string|null $paypal_refund_id
 * @property string|null $notas_admin
 * @property Carbon      $fecha_solicitud
 * @property Carbon|null $fecha_resolucion
 *
 * @property Pedido                       $pedido
 * @property Usuario                      $usuario
 * @property Collection|DetalleDevolucion[] $detalles
 */
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

    // ── Motivos legibles ────────────────────────────────────────────────────────
    public const MOTIVOS = [
        'producto_defectuoso'        => 'Producto defectuoso',
        'talla_incorrecta'           => 'Talla incorrecta',
        'no_corresponde_descripcion' => 'No corresponde a la descripción',
        'no_llego'                   => 'No llegó el pedido',
        'cambio_opinion'             => 'Cambio de opinión',
    ];

    // Motivos donde el producto NO regresa al stock (está dañado)
    public const MOTIVOS_SIN_STOCK = ['producto_defectuoso'];

    // ── Scopes ──────────────────────────────────────────────────────────────────
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

    // ── Helpers ─────────────────────────────────────────────────────────────────

    /** El producto puede regresar al inventario */
    public function regresaAlStock(): bool
    {
        return !in_array($this->motivo, self::MOTIVOS_SIN_STOCK);
    }

    public function motivoLegible(): string
    {
        return self::MOTIVOS[$this->motivo] ?? $this->motivo;
    }

    // ── Relaciones ──────────────────────────────────────────────────────────────
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
