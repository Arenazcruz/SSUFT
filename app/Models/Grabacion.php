<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grabacion extends Model
{
    use HasFactory;

    protected $table = 'grabaciones';

    protected $fillable = [
        'reunion_id',
        'title',
        'description',
        'url',
        'published_at',
        'duration_minutes',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function reunion(): BelongsTo
    {
        return $this->belongsTo(Reunion::class, 'reunion_id');
    }
}
