<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tarea extends Model
{
    use HasFactory;

    public const ESTADOS = [
        'Pendiente',
        'En progreso',
        'Completada',
    ];

    protected $table = 'tareas';

    protected $fillable = [
        'usuario_id',
        'nombre',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date:Y-m-d',
        'fecha_fin' => 'date:Y-m-d',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function scopeDeUsuario($q, int $userId)
    {
        return $q->where('usuario_id', $userId);
    }

    public function enRango($q, ?string $desde, ?string $hasta)
    {
        if ($desde) {
            $q->whereDate('fecha_fin', '>=', $desde);
        }
        if ($hasta) {
            $q->whereDate('fecha_inicio', '<=', $hasta);
        }
        return $q;
    }
}