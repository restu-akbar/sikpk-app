<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ReportEvidence extends Model
{
    use HasUuids;
    protected $table = 'report_evidences';
    protected $fillable = [
        'report_id',
        'path',
        'edeks',
        'original_filename',
        'mime_type',
        'size',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    protected function casts(): array
    {
        return [
            'edeks' => 'array',
        ];
    }
}
