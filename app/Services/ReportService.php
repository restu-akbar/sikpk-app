<?php

namespace App\Services;

use App\Mail\NewReportMail;
use App\Mail\TeamAssignedMail;
use App\Models\Report;
use App\Models\ReportEvidence;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReportService
{
    public function __construct(
        protected MailService $mailService
    ) {
    }

    private const PROGRESS_FLOW = [
        'Laporan Baru', 'Klarifikasi', 'Pemeriksaan', 'Kesimpulan', 'Pasca', 'Selesai',
    ];

    private const REQUIRED_DOCUMENTS = [
        'Klarifikasi' => [
            'notulensi' => ['document' => 1, 'documentation' => 2],
        ],
        'Pemeriksaan' => [
            'periksa_pelapor' => ['document' => 1, 'documentation' => 3],
            'periksa_terlapor' => ['document' => 1, 'documentation' => 3],
        ],
        'Kesimpulan' => [
            'kesimpulan_rekomendasi' => ['document' => 1, 'documentation' => 2],
        ],
    ];

    protected function query(
        array $with = [],
        bool $handler = false,
        array $progressIn = [],
        array $progressNotIn = [],
    ): Builder {
        $query = Report::query();

        if (! empty($with)) {
            $query->with($with);
        }

        if (! empty($progressIn)) {
            $query->whereIn('progress', $progressIn);
        }

        if (! empty($progressNotIn)) {
            $query->whereNotIn('progress', $progressNotIn);
        }

        if (auth('google')->check()) {
            $query->where('reporter_id', auth('google')->id());
        }

        if (auth('web')->check()) {
            $user = auth('web')->user();

            if ($handler) {
                $query->whereHas('handlers', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });

                return $query;
            }
        }

        return $query;
    }

    public function index(
        bool $paginate = true,
        array $with = [],
        bool $handler = false,
        array $progressIn = [],
        array $progressNotIn = [],
    ) {
        $query = $this->query($with, $handler, $progressIn, $progressNotIn)->latest();

        return $paginate
            ? $query->paginate(10)
            : $query->get();
    }

    public function show(string $id, array $with = [], bool $handler = false): Report
    {
        return $this->query($with, $handler)
            ->with($with)
            ->where('id', $id)
            ->firstOrFail();
    }
    public function store(array $data, Request $request): Report
    {
        $report = DB::transaction(function () use ($data, $request) {
            $reporter   = auth('google')->user();
            $reporterId = $reporter?->id;

            if ($reporter) {
                $isFirstReport = !$reporter->reports()->exists();

                if ($isFirstReport) {
                    $reporter->update([
                        'name'           => $data['nama'] ?? '',
                        'whatsapp'       => $data['whatsapp'],
                        'status_civitas' => $data['statusCivitas'] ?? null,
                        'jurusan'        => $data['jurusan'] ?? null,
                        'prodi'          => $data['prodi'] ?? null,
                        'disabilitas'    => $data['disabilitas'] ?? null,
                    ]);
                } else {
                    $reporter->update([
                        'whatsapp'    => $data['whatsapp'],
                        'disabilitas' => $data['disabilitas'] ?? null,
                    ]);
                }
            }

            $year = now()->year;
            $lastSequence = Report::whereYear('created_at', $year)
                ->where('case_number', 'like', '#%/PPK/' . $year)
                ->lockForUpdate()
                ->pluck('case_number')
                ->map(fn ($caseNumber) => (int) substr($caseNumber, 1, 3))
                ->max();
            $sequence = ($lastSequence ?? 0) + 1;
            $caseNumber = '#' . str_pad($sequence, 3, '0', STR_PAD_LEFT) . '/PPK/' . $year;

            $report = Report::create([
                'reporter_id'   => $reporterId,
                'case_number'   => $caseNumber,

                'status_pelapor' => $data['statusPelapor'],

                'nama_terlapor'  => $data['namaTerlapor'],
                'status_terlapor' => $data['statusTerlapor'],

                'jenis_kekerasan' => $data['jenisKekerasan'],

                'tempat_kejadian' => $data['tempatKejadian'] ?? null,
                'waktu_kejadian'  => $data['waktuKejadian'] ?? null,

                'kronologi' => $data['kronologi'] ?? null,
            ]);

            foreach ($request->input('audio_recordings', []) as $index => $item) {
                $file = $request->file("audio_recordings.$index.file");

                $path = Storage::putFileAs(
                    'audio-recordings',
                    $file,
                    Str::uuid() . '.' . ($file->guessExtension() ?? 'webm')
                );

                $report->audioRecordings()->create([
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'duration' => $item['duration'] ?? null,
                    'order' => $item['order'],
                ]);
            }

            return $report;
        });

        User::each(function ($user) use ($report) {
            $this->mailService->send(
                $user->email,
                new NewReportMail($user, $report)
            );
        });

        return $report;
    }

    public function assignHandlers($request, $id)
    {
        $request->validate([
            'anggota' => ['required', 'array'],
            'anggota.*' => ['uuid'],

            'edek_updates' => ['array'],
            'edek_updates.*.evidence_id' => ['required', 'uuid'],
            'edek_updates.*.edeks' => ['required', 'array'],
        ]);

        $report = Report::findOrFail($id);

        $year = now()->year;
        $teamSequence = Report::whereYear('created_at', $year)
            ->whereNotNull('team_number')
            ->count() + 1;
        $teamNumber = 'TIM-' . $year . '-' . str_pad($teamSequence, 3, '0', STR_PAD_LEFT);

        $report->update([
            'progress' => 'Klarifikasi',
            'team_number' => $teamNumber,
        ]);

        $report->handlers()->sync($request->anggota);
        foreach ($request->edek_updates as $update) {
            $evidence = ReportEvidence::findOrFail(
                $update['evidence_id']
            );

            $existingEdeks = $evidence->edeks ?? [];

            $evidence->update([
                'edeks' => array_merge(
                    $existingEdeks,
                    $update['edeks']
                ),
            ]);
        }
        $users = User::whereIn('id', $request->anggota)->get();

        foreach ($users as $user) {
            $this->mailService->send(
                $user->email,
                new TeamAssignedMail(
                    name: $user->name,
                    teamNumber: $report->team_number,
                    reportTitle: $report->jenis_kekerasan,
                )
            );
        }
    }

    public function rejectReport(Request $request, string $id): void
    {
        $request->validate([
            'type' => ['required', Rule::in(['stop', 'reject'])],
            'reason' => ['required', 'string'],
            'note' => ['sometimes', 'string', 'nullable'],
        ]);

        $report = Report::findOrFail($id);

        $report->update([
            'progress' => $request->type === 'stop'
                ? 'Laporan Dihentikan'
                : 'Laporan Ditolak',
            'rejected_reason' => $request->reason,
            'note_reason' => $request->note,
        ]);
    }

    public function update(Report $report, array $data): Report
    {
        $validated = $this->validate($data);

        if (array_key_exists('progress', $validated)) {
            $this->assertCanTransitionProgress($report, $validated['progress']);
        }

        $report->update($validated);

        return $report->fresh();
    }

    private function assertCanTransitionProgress(Report $report, string $newProgress): void
    {
        $current = $report->progress;

        if (in_array($current, Report::ARCHIVED_PROGRESS, true)) {
            throw ValidationException::withMessages([
                'progress' => 'Laporan ini sudah final dan tidak dapat diubah lagi.',
            ]);
        }

        if ($newProgress === $current) {
            return;
        }

        $isExitTransition = in_array($newProgress, ['Laporan Dihentikan', 'Laporan Ditolak'], true);

        if (! $isExitTransition) {
            $currentIndex = array_search($current, self::PROGRESS_FLOW, true);
            $nextIndex = array_search($newProgress, self::PROGRESS_FLOW, true);

            if ($currentIndex === false || $nextIndex === false || $nextIndex !== $currentIndex + 1) {
                throw ValidationException::withMessages([
                    'progress' => 'Tahapan penanganan tidak valid.',
                ]);
            }
        }

        $this->assertDocumentsComplete($report, $current);
    }

    private function assertDocumentsComplete(Report $report, string $progress): void
    {
        foreach ($this->requiredDocumentsFor($progress) as $subtype => $rules) {
            $docs = $report->reportDocuments()
                ->where('type', $progress)
                ->where('subtype', $subtype)
                ->with('attachments')
                ->get();

            if ($docs->count() < ($rules['document'] ?? 0)) {
                throw ValidationException::withMessages([
                    'progress' => "Dokumen wajib pada tahap {$progress} belum lengkap.",
                ]);
            }

            $minDocumentation = $rules['documentation'] ?? 0;

            foreach ($docs as $doc) {
                $count = $doc->attachments
                    ->where('attachment_type', 'documentation')
                    ->count();

                if ($count < $minDocumentation) {
                    throw ValidationException::withMessages([
                        'progress' => "Dokumentasi pada tahap {$progress} belum lengkap.",
                    ]);
                }
            }
        }
    }

    private function requiredDocumentsFor(string $progress): array
    {
        return self::REQUIRED_DOCUMENTS[$progress] ?? [];
    }

    public function isKetuaTim(Report $report, string $userId): bool
    {
        $ketuaId = DB::table('report_handlers')
            ->where('report_id', $report->id)
            ->value('user_id');

        return $ketuaId && $ketuaId === $userId;
    }

    private function validate(array $data): array
    {
        $validator = Validator::make($data, [
            'reporter_id' => ['sometimes', 'integer', 'exists:users,id'],
            'status_pelapor' => ['sometimes', 'string', 'max:50'],
            'nama_terlapor' => ['sometimes', 'string', 'max:255'],
            'status_terlapor' => ['sometimes', 'string', 'max:50'],
            'jenis_kekerasan' => ['sometimes', 'string', 'max:100'],
            'tempat_kejadian' => ['sometimes', 'string', 'max:255'],
            'waktu_kejadian' => ['sometimes', 'date'],
            'kronologi' => ['sometimes', 'string'],
            'progress' => ['sometimes', 'string', 'max:50'],
            'rejected_reason' => ['sometimes', 'string', 'nullable'],
            'note_reason' => ['sometimes', 'string', 'nullable'],
            'type' => ['sometimes', 'string', 'nullable'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
