<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
        'activo',
        'avatar_color',
        'foto_perfil',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function reunionesComoDocente(): HasMany
    {
        return $this->hasMany(Reunion::class, 'docente_id');
    }

    public function participaciones(): HasMany
    {
        return $this->hasMany(ParticipanteReunion::class, 'user_id');
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(ChatReunion::class, 'user_id');
    }

    public function isRole(string|array $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        return in_array($this->role?->slug, $roles, true);
    }
}
