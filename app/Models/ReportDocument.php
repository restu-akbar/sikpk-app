<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ReportDocument extends Model
{
    use HasUuids;

    protected $fillable = [
        'report_id',
        'path',
        'edeks',
        'original_filename',
        'mime_type',
        'type',
        'subtype',
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

    public function toCreatePayload(array $item, string $path): array
    {
        return [
            'path' => $path,
            'edeks' => isset($item['edeks'])
                ? json_decode($item['edeks'], true)
                : null,
            'original_filename' => $item['filename'] ?? null,
            'mime_type' => $item['mime_type'] ?? null,
            'type' => $item['type'] ?? 'clarification',
            'subtype' => $item['subtype'] ?? 'generated_pdf',
        ];
    }
}
