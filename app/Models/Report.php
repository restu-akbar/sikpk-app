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
        'status_pelapor',
        'nama_terlapor',
        'status_terlapor',
        'jenis_kekerasan',
        'tempat_kejadian',
        'waktu_kejadian',
        'kronologi',
        'progress',
        'case_number',
        'team_number',
    ];

    protected function casts(): array
    {
        return [
            'waktu_kejadian' => 'datetime',
        ];
    }

    protected $attributes = [
        'progress' => 'Laporan Baru',
    ];

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

    public function audioRecordings(): HasMany
    {
        return $this->hasMany(AudioRecording::class)->orderBy('order');
    }

    public function reportLogs(): HasMany
    {
        return $this->hasMany(ReportLog::class)
            ->orderBy('created_at');
    }

    public function latestLog()
    {
        return $this->hasOne(ReportLog::class)
            ->latestOfMany();
    }
}
