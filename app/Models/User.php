<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'users';
    
    protected $fillable = [
        'name',
        'role',
        'email',
        'password',
         // Agregado: permite asignar roles al crear usuarios
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relación con proyectos.
     */
    public function proyectos()
    {
        return $this->belongsToMany(Proyecto::class, 'proyecto_usuario', 'usuario_id', 'proyecto_id');
    }

    
    /**
     * Relación con tareas.
     */
    public function tareas()
    {
        return $this->belongsToMany(Tarea::class, 'tarea_usuario', 'usuario_id', 'tarea_id')
            ->withPivot('asignado_en');
    }

    /**
     * Métodos para verificar roles.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'administrador';
    }

    public function isProjectManager(): bool
    {
        return $this->role === 'jefe';
    }

    public function isMember(): bool
    {
        return $this->role === 'miembro';
    }
}
