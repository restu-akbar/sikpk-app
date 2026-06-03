<?php

namespace App\Models;

use App\Models\Reporter;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Report extends Model
{
    use HasUuids;

    protected $fillable = [
        'reporter_id',
        'handled_by',
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
        'progress',
    ];

    protected function casts(): array
    {
        return [
            'waktu_kejadian' => 'datetime',
            'disabilitas' => 'array',
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(Reporter::class);
    }

    public function handlers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'report_handlers'
        );
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(ReportEvidence::class);
    }
}
