<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportDocumentAttachment extends Model
{
    use HasUuids;

    protected $fillable = [
        'report_document_id',
        'path',
        'edeks',
        'original_filename',
        'mime_type',
        'attachment_type',
        'size',
    ];

    protected $casts = [
        'edeks' => 'array',
    ];

    public function reportDocument(): BelongsTo
    {
        return $this->belongsTo(
            ReportDocument::class
        );
    }

    public static function toCreatePayload(
        array $item,
        string $path
    ): array {
        return [
            'path' => $path,
            'edeks' => isset($item['edeks'])
                ? json_decode($item['edeks'], true)
                : null,
            'original_filename' => $item['filename'] ?? null,
            'mime_type' => $item['mime_type'] ?? null,
            'attachment_type' => $item['attachment_type'] ?? null,
            'size' => $item['size'] ?? null,
        ];
    }
}
