<?php

namespace App\Http\Controllers;

use App\Helpers\Toast;
use App\Models\Report;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FileController extends Controller
{
    public function __construct(
        protected FileService $fileService
    ) {
    }

    public function show(Request $request, String $document)
    {
        return $this->fileService->show($request->type, $document);
    }

    public function store(Request $request, String $id)
    {
        try {
            $validated = $request->validate([
                'bukti' => ['nullable', 'array'],
                'bukti.*.file' => ['required'],
                'bukti.*.edeks' => ['required', 'string'],
                'bukti.*.filename' => ['required', 'string'],
                'bukti.*.mime_type' => ['required', 'string'],
                'bukti.*.size' => ['required', 'integer'],
            ]);
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();

            return back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('toast', Toast::error(
                    $firstError ?? 'Terjadi kesalahan saat menyimpan bukti.'
                ));
        }
        $report = Report::findOrFail($id);
        $this->fileService->store(
            $validated['bukti'] ?? [],
            $report->reportEvidences(),
            "evidences"
        );

        return response()->json([
            'type' => 'success',
            'message' => 'Bukti berhasil disimpan.',
        ]);
    }
}
