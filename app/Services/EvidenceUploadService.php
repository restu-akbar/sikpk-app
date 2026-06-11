<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EvidenceUploadService
{
    public function store(array $bukti, $relation): void
    {
        foreach ($bukti as $item) {

            $file = $item['file'] ?? null;

            if (!$file) {
                continue;
            }

            $path = Storage::disk('private')->putFileAs(
                'reports',
                $file,
                Str::uuid() . '.enc'
            );

            $relation->create([
                'path' => $path,
                'edeks' => isset($item['edeks'])
                    ? json_decode($item['edeks'], true)
                    : null,
                'original_filename' => $item['filename'] ?? null,
                'mime_type' => $item['mime_type'] ?? null,
                'size' => $item['size'] ?? null,
            ]);
        }
    }
}
