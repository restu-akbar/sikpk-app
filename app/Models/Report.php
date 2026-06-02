<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Report extends Model
{
    use HasUuids;

    protected $fillable = [
        'nama',
        'whatsapp',
        'status_pelapor',
        'status_civitas',
        'nama_terlapor',
        'status_terlapor',
        'jenis_kekerasan',
        'tempat_kejadian',
        'waktu_kejadian',
        'kronologi',
        'disabilitas',
        'agreed',
    ];

    protected function casts(): array
    {
        return [
            'waktu_kejadian' => 'datetime',
            'disabilitas' => 'array',
            'agreed' => 'boolean',
        ];
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(ReportEvidence::class);
    }
}
