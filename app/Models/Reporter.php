<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Reporter extends Authenticatable
{
    use Notifiable, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'name',
        'email',
        'whatsapp',
        'status_civitas',
        'jurusan',
        'prodi',
        'disabilitas',
    ];

    protected function casts(): array
    {
        return [
            'disabilitas' => 'array',
        ];
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
}
