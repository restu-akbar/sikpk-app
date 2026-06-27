<?php

namespace App\Http\Controllers\Module;

use App\Helpers\Toast;
use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\ReportDocumentAttachment;
use App\Models\ReportEvidence;
use App\Models\User;
use App\Services\FileService;
use App\Services\ReportService;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\UserService;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected ReportService $reportService,
        protected FileService $fileService
    ) {
    }

    public function data()
    {
        return $this->reportService->index(false);
    }

    public function showLogs(String $id)
    {
        return $this->reportService->index(false, ['reportLogs'])->findOrFail($id);
    }

    public function index()
    {
        $reports = $this->reportService->index(
            with: ['reportEvidences', 'reporter', 'handlers', 'audioRecordings', 'korbans'],
            progressNotIn: Report::ARCHIVED_PROGRESS,
        );

        $reports->each(function ($report) {
            $report->korban = $report->korbans->sortByDesc('created_at')->first();
            unset($report->korbans);
        });

        $this->orderHandlersByAssignment($reports);

        return Inertia::render('satgas/reports/Index', [
            'rows' => $reports
        ]);
    }

    public function archive()
    {
        $reports = $this->reportService->index(
            with: [
                'reportLogs',
                'reporter',
                'handlers',
                'reportDocuments.attachments',
                'reportEvidences',
                'audioRecordings',
                'korbans',
                'terlapors',
            ],
            progressIn: Report::ARCHIVED_PROGRESS,
        );

        $reportIds = $reports->pluck('id');
        $pivotRows = DB::table('report_handlers')
            ->whereIn('report_id', $reportIds)
            ->get()
            ->groupBy('report_id');

        $reports->each(function ($report) use ($pivotRows) {
            $report->korban = $report->korbans->first();
            $report->terlapor = $report->terlapors->first();
            unset($report->korbans, $report->terlapors);

            $orderedIds = ($pivotRows[$report->id] ?? collect())->pluck('user_id');
            $handlersById = $report->handlers->keyBy('id');

            $report->members = $orderedIds
                ->filter(fn ($uid) => $handlersById->has($uid))
                ->map(fn ($uid) => $handlersById->get($uid))
                ->map(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'academic_role' => $user->academic_role,
                    'department' => $user->department,
                ])
                ->values();

            unset($report->handlers);
        });

        return Inertia::render('satgas/archive/Index', [
            'rows' => $reports
        ]);
    }

    public function archiveEdeks(Request $request, Report $report)
    {
        if (! in_array($report->progress, Report::ARCHIVED_PROGRESS, true)) {
            return back()->with('toast', Toast::error(
                'Laporan belum berstatus final, edeks belum bisa dipersempit.'
            ));
        }

        $validated = $request->validate([
            'attachments' => ['array'],
            'attachments.*.id' => ['required', 'uuid'],
            'attachments.*.edeks' => ['required', 'array'],
        ]);

        $ketuaIds = User::where('role', 'ketua')->pluck('id')->map(fn ($id) => (string) $id)->all();

        DB::transaction(function () use ($report, $validated, $ketuaIds) {
            foreach ($validated['attachments'] ?? [] as $item) {
                if (array_diff(array_keys($item['edeks']), $ketuaIds)) {
                    throw ValidationException::withMessages([
                        'attachments' => 'Edeks hanya boleh berisi kunci ketua satgas.',
                    ]);
                }

                ReportDocumentAttachment::where('id', $item['id'])
                    ->whereHas(
                        'reportDocument',
                        fn ($q) => $q->where('report_id', $report->id)
                    )
                    ->update(['edeks' => $item['edeks']]);
            }

            ReportEvidence::where('report_id', $report->id)
                ->get()
                ->each(function (ReportEvidence $evidence) use ($ketuaIds) {
                    $filtered = array_intersect_key(
                        $evidence->edeks ?? [],
                        array_flip($ketuaIds)
                    );

                    $evidence->update(['edeks' => $filtered]);
                });
        });

        return back()->with('toast', Toast::success('Edeks tim penanganan berhasil dibersihkan.'));
    }

    private function orderHandlersByAssignment($reports): void
    {
        $reportIds = $reports->pluck('id');
        $pivotRows = DB::table('report_handlers')
            ->whereIn('report_id', $reportIds)
            ->get()
            ->groupBy('report_id');

        $reports->each(function ($report) use ($pivotRows) {
            $orderedIds = ($pivotRows[$report->id] ?? collect())->pluck('user_id');
            if ($orderedIds->isNotEmpty()) {
                $handlersById = $report->handlers->keyBy('id');
                $report->setRelation(
                    'handlers',
                    $orderedIds
                        ->filter(fn ($uid) => $handlersById->has($uid))
                        ->map(fn ($uid) => $handlersById->get($uid))
                        ->values()
                );
            }
        });
    }

    public function create()
    {
        $reporter      = auth('google')->user();
        $isFirstReport = !$reporter || !$reporter->reports()->exists();

        return Inertia::render('reporters/reports/Create', [
            'isFirstReport' => $isFirstReport,
            'reporterData'  => $isFirstReport ? null : [
                'nama'          => $reporter->name,
                'whatsapp'      => $reporter->whatsapp,
                'statusCivitas' => $reporter->status_civitas,
                'jurusan'       => $reporter->jurusan,
                'prodi'         => $reporter->prodi,
                'disabilitas'   => $reporter->disabilitas ?? [],
            ],
        ]);
    }

    public function store(Request $request)
    {
        try {
            $isAudio = $request->input('metode') === 'audio';

            $validated = $request->validate([
                'nama' => ['nullable', 'string', 'max:255'],
                'whatsapp' => ['required', 'string', 'max:30'],
                'statusPelapor' => ['required', 'string'],
                'statusCivitas' => ['required', 'string'],
                'jurusan' => ['nullable', 'string'],
                'prodi' => ['nullable', 'string'],
                'namaTerlapor' => ['required', 'string', 'max:255'],
                'statusTerlapor' => ['required', 'string'],
                'jenisKekerasan' => ['required', 'string'],
                'tempatKejadian' => [$isAudio ? 'nullable' : 'required', 'string'],
                'waktuKejadian' => [$isAudio ? 'nullable' : 'required', 'date'],
                'kronologi' => [$isAudio ? 'nullable' : 'required', 'string'],
                'metode' => ['nullable', 'string', 'in:form,audio'],
                'agreed' => ['accepted'],
                'disabilitas' => ['required', 'array', 'min:1'],

                'bukti' => ['nullable', 'array'],
                'bukti.*.file' => ['required'],
                'bukti.*.edeks' => ['required', 'string'],
                'bukti.*.filename' => ['required', 'string'],
                'bukti.*.mime_type' => ['required', 'string'],
                'bukti.*.size' => ['required', 'integer'],

                'audio_recordings' => [$isAudio ? 'required' : 'nullable', 'array', 'min:1'],
                'audio_recordings.*.file' => ['required', 'file'],
                'audio_recordings.*.duration' => ['nullable', 'integer', 'min:0'],
                'audio_recordings.*.order' => ['required', 'integer', 'min:1'],
            ]);
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();

            return back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('toast', Toast::error(
                    $firstError ?? 'Silakan periksa kembali form Anda.'
                ));
        }
        $report = $this->reportService->store($validated, $request);
        $this->fileService->store(
            $validated['bukti'] ?? [],
            $report->reportEvidences(),
            "evidences"
        );

        return redirect()->route('landing')->with('toast', [
            'type' => 'success',
            'message' => 'Laporan Anda berhasil disimpan. Terima kasih telah melapor.',
        ])->with('reportSubmitted', true);
    }

    public function update(
        Request $request,
        Report $report,
    ) {
        if (
            $request->has('progress') &&
            !$this->reportService->isKetuaTim($report, (string) auth()->id())
        ) {
            return back()->with('toast', Toast::error(
                'Hanya ketua tim yang dapat mengubah tahapan penanganan laporan ini.'
            ));
        }

        $this->reportService->update($report, $request->all());

        return back()->with('toast', Toast::success('Berhasil update laporan'));
    }

    public function destroy(User $user)
    {
        $this->userService->deleteAnggota($user);

        return back()->with(
            'success',
            'User berhasil dihapus',
        );
    }

    public function assign(Request $request, $id)
    {
        $this->reportService->assignHandlers($request, $id);
        return back()->with('toast', Toast::success('Tim Penanganan Berhasil Dibentuk'));
    }

    public function reject(Request $request, $id)
    {
        $this->reportService->rejectReport($request, $id);

        $message = match ($request->type) {
            'stop' => 'Laporan dihentikan',
            'reject' => 'Laporan ditolak',
            default => 'Status laporan diperbarui',
        };

        return back()->with('toast', Toast::success($message));
    }
}
