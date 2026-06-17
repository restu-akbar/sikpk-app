<?php

namespace App\Services;

use App\Models\ReportDocumentAttachment;
use App\Models\ReportEvidence;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    public function store(array $files, $relation, string $folder): void
    {
        if (isset($files['file'])) {
            $files = [$files];
        }

        foreach ($files as $item) {
            $storedPath = Storage::putFileAs(
                "reports/{$folder}",
                $item['file'],
                Str::uuid() . '.enc'
            );

            $model = $relation->getRelated();

            if (method_exists($model, 'storeUploadedFile')) {
                $model->storeUploadedFile(
                    $relation,
                    $item,
                    $storedPath
                );

                continue;
            }

            $relation->create(
                $model->toCreatePayload(
                    $item,
                    $storedPath
                )
            );
        }
    }

    public function show(string $table, string $id)
    {
        $model = match ($table) {
            'evidence' => ReportEvidence::class,
            'document' => ReportDocumentAttachment::class,
            default => abort(404),
        };

        $file = $model::findOrFail($id);

        return Storage::response($file->path);
    }
}
