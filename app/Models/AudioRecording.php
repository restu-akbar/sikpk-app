<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudioRecording extends Model
{
    use HasUuids;

    protected $fillable = [
        'report_id',
        'path',
        'mime_type',
        'size',
        'duration',
        'order',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
