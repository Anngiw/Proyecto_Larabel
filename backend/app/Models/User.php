<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Nombre exacto de tu tabla en PostgreSQL
    protected $table = 'usuarios';

    // Campos permitidos para asignación masiva
    protected $fillable = [
        'nombre',
        'correo',
        'password',
    ];

    // Ocultar la contraseña y el remember_token
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}