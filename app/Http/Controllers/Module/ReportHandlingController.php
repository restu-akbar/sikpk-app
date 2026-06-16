<?php

namespace App\Http\Controllers\Module;

use App\Helpers\Toast;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ReportService;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReportHandlingController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected ReportService $reportService
    ) {
    }

    public function data()
    {
        return $this->reportService->index(false);
    }

    public function show($id)
    {
        $report = $this->reportService->show(
            $id,
            ['reportLogs', 'reporter', 'handlers', 'reportDocuments'],
            true
        );

        $report->members = $report->handlers->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'academic_role' => $user->academic_role,
                'department' => $user->department,
            ];
        })->values();

        unset($report->handlers);
        return Inertia::render('satgas/reports/handling/ShowReport', [
            'report' => $report,
        ]);
    }

    public function index()
    {
        $reports = $this->reportService->index(false, ['handlers', 'reporter'], true);

        $reports = $reports->map(function ($report) {

            return [
                'id' => $report->id,
                'caseNumber' => $report->case_number ?? $report->id,
                'title' => $report->jenis_kekerasan,
                'status' => $report->status ?? $report->progress,
                'reportDate' => $report->created_at,
                'reporter' => $report->reporter?->name,
                'progress' => $report->progress,
                'teamNumber' => $report->team_number,

                'members' => $report->handlers->map(function ($user) {
                    return [
                        'name' => $user->name,
                        'initials' => collect(explode(' ', $user->name))
                            ->map(fn ($n) => strtoupper(substr($n, 0, 1)))
                            ->join(''),
                    ];
                })->values(),
            ];
        });
        return Inertia::render('satgas/reports/handling/Index', [
            'reports' => $reports,
        ]);
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
        $this->reportService->store($validated, $request);

        return redirect()->route('landing')->with('toast', [
            'type' => 'success',
            'message' => 'Laporan Anda berhasil disimpan. Terima kasih telah melapor.',
        ]);
    }

    public function update(
        Request $request,
        User $user
    ) {
        $validated = $request->validate([
            'name' => ['required'],
            'email' => [
                'required',
                'email',
                Rule::unique('users')
                    ->ignore($user->id),
            ]
        ]);

        $this->userService->updateAnggota(
            $user,
            $validated
        );

        return back()->with(
            'success',
            'User berhasil diupdate',
        );
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
        return back()->with('toast', Toast::success('Berhasil assign anggota'));
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
