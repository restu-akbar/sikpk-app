<?php

namespace App\Services;

use App\Models\ReportDocument;
use App\Models\ReportEvidence;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    public function store(array $bukti, $relation, string $folder): void
    {
        foreach ($bukti as $item) {

            if (!isset($item['file'])) {
                continue;
            }

            $storedPath = Storage::putFileAs(
                "reports/{$folder}",
                $item['file'],
                Str::uuid() . '.enc'
            );

            $relation->create(
                $relation->getRelated()->toCreatePayload($item, $storedPath)
            );
        }
    }

    public function show($table, $id)
    {
        $model = match ($table) {
            'evidence' => ReportEvidence::class,
            'document' => ReportDocument::class,
            default => abort(404),
        };

        $file = $model::findOrFail($id);

        return Storage::response($file->path);
    }
}
