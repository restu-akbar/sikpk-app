<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportSubject extends Model
{
    use HasUuids;

    protected $fillable = [
        'report_id',
        'jenis',
        'nama',
        'nomor_identitas',
        'nomor_wa',
        'jenis_kelamin',
        'peran_akademik',
        'jurusan',
        'prodi',
        'angkatan',
        'disabilitas',
    ];

    protected function casts(): array
    {
        return [
            'disabilitas' => 'boolean',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function scopeKorban(Builder $query): Builder
    {
        return $query->where('jenis', 'korban');
    }

    public function scopeTerlapor(Builder $query): Builder
    {
        return $query->where('jenis', 'terlapor');
    }
}
