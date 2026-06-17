<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportDocument extends Model
{
    use HasUuids;

    protected $fillable = [
        'report_id',
        'type',
        'subtype',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(
            ReportDocumentAttachment::class
        );
    }

    public function storeUploadedFile(
        $relation,
        array $item,
        string $storedPath
    ): void {
        $attachmentType = $item['attachment_type'] ?? null;
        $documentId = $item['document_id'] ?? null;

        if ($attachmentType === 'document') {
            $document = $relation->create([
                'type' => $item['type'],
                'subtype' => $item['subtype'],
            ]);
        } else {
            if ($documentId) {
                $document = $relation->findOrFail($documentId);
            } else {
                $document = $relation->firstOrCreate([
                    'type' => $item['type'],
                    'subtype' => $item['subtype'],
                ]);
            }
        }

        $document->attachments()->create(
            ReportDocumentAttachment::toCreatePayload(
                $item,
                $storedPath
            )
        );
    }
}
