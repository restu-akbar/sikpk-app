<?php

namespace App\Http\Controllers;

use App\Models\ReportEvidence;
use App\Services\EvidenceUploadService;
use Illuminate\Validation\ValidationException;
use App\Helpers\Toast;
use App\Models\Report;
use Illuminate\Http\Request;

class EvidenceController extends Controller
{
    public function __construct(
        protected EvidenceUploadService $evidenceUploadService
    ) {}
    public function show(ReportEvidence $evidence)
    {
        return response()->file(
            storage_path('app/private/' . $evidence->path)
        );
    }

    public function store(Request $request, $id)
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

            $report = Report::findOrFail($id);
            $this->evidenceUploadService->store(
                $validated['bukti'] ?? [],
                $report->evidences()
            );

            return back()
                ->with(
                    'toast',
                    Toast::success('File berhasil disimpan'),
                );
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();
            return back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('toast', Toast::error(
                    $firstError ?? 'Silakan periksa kembali form Anda.'
                ));
        }
    }
}
