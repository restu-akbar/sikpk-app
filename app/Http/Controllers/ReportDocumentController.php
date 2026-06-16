<?php

namespace App\Http\Controllers;

use Illuminate\Validation\ValidationException;
use App\Helpers\Toast;
use App\Models\Report;
use App\Services\FileService;
use Illuminate\Http\Request;

class ReportDocumentController extends Controller
{
    public function __construct(
        protected FileService $fileService
    ) {
    }

    public function show($document)
    {
        return $this->fileService->show('document', $document);
    }

    public function store(Request $request, string $report)
    {
        try {
            $documents = $request->input('document', []);

            if ($documents && array_is_list($documents) === false) {
                $documents = [$documents];
            }

            foreach ($documents as $index => &$document) {
                $document['file'] = $request->file("document.$index.file");
            }

            unset($document);
            $validated = validator(
                ['document' => $documents],
                [
                    'document' => ['nullable', 'array'],
                    'document.*.file' => ['required', 'file'],
                    'document.*.edeks' => ['required', 'string'],
                    'document.*.filename' => ['required', 'string'],
                    'document.*.mime_type' => ['required', 'string'],
                    'document.*.size' => ['required', 'integer'],
                    'document.*.type' => ['required', 'string'],
                    'document.*.subtype' => ['required', 'string'],
                    'document.*.attachment_type' => ['required', 'string'],
                ]
            )->validate();

            $report = Report::findOrFail($report);

            $this->fileService->store(
                $validated['document'] ?? [],
                $report->reportDocuments(),
                'documents',
            );

            return back()->with(
                'toast',
                Toast::success('File berhasil disimpan'),
            );
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();

            return back()
                ->withInput()
                ->withErrors($e->errors())
                ->with(
                    'toast',
                    Toast::error(
                        $firstError ?? 'Silakan periksa kembali form Anda.'
                    )
                );
        }
    }
    public function download(String $document)
    {
        $this->fileService->download('document', $document);
    }

}
