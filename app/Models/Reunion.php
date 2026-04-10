<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reunion extends Model
{
    use HasFactory;

    protected $table = 'reuniones';

    protected $fillable = [
        'docente_id',
        'title',
        'description',
        'scheduled_at',
        'started_at',
        'ended_at',
        'estado',
        'duration_minutes',
        'access_code',
        'cover_gradient',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'docente_id');
    }

    public function participantes(): HasMany
    {
        return $this->hasMany(ParticipanteReunion::class, 'reunion_id');
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(ChatReunion::class, 'reunion_id');
    }

    public function grabaciones(): HasMany
    {
        return $this->hasMany(Grabacion::class, 'reunion_id');
    }
}
