<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int         $id_factura
 * @property int|null    $id_pedido
 * @property int|null    $id_usuario
 * @property string      $numero
 * @property string      $estado
 * @property \Carbon\Carbon|null $fecha_emision
 * @property \Carbon\Carbon|null $fecha_vencimiento
 * @property string      $moneda
 * @property float       $subtotal
 * @property float       $descuento
 * @property float       $impuesto
 * @property float       $costo_envio
 * @property float       $total
 * @property string|null $notas
 */

class Factura extends Model
{
    protected $table = 'facturas';
    protected $primaryKey = 'id_factura';
    public $timestamps = false;

    protected $casts = [
        'id_pedido'         => 'int',
        'id_usuario'        => 'int',
        'subtotal'          => 'float',
        'descuento'         => 'float',
        'impuesto'          => 'float',
        'costo_envio'       => 'float',
        'total'             => 'float',
        'fecha_emision'     => 'datetime',
        'fecha_vencimiento' => 'date',
    ];

    protected $fillable = [
        'id_pedido',
        'id_usuario',
        'numero',
        'estado',
        'fecha_emision',
        'fecha_vencimiento',
        'moneda',
        'subtotal',
        'descuento',
        'impuesto',
        'costo_envio',
        'total',
        'notas',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleFactura::class, 'id_factura');
    }
}
