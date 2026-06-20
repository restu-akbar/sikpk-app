<?php

namespace App\Services;

use App\Models\AudioRecording;
use App\Models\Report;
use App\Models\ReportEvidence;
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
    protected function query(array $with = [], bool $handler = false): Builder
    {
        $query = Report::query();

        if (! empty($with)) {
            $query->with($with);
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

    public function index(bool $paginate = true, array $with = [], bool $handler = false)
    {
        $query = $this->query($with, $handler)->latest();

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
        return DB::transaction(function () use ($data, $request) {
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
                    // Hanya whatsapp dan disabilitas yang boleh diubah
                    $reporter->update([
                        'whatsapp'    => $data['whatsapp'],
                        'disabilitas' => $data['disabilitas'] ?? null,
                    ]);
                }
            }

            $year = now()->year;
            $sequence = Report::whereYear('created_at', $year)->count() + 1;
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
    }

    public function assignHandlers($request, $id)
    {
        $request->validate([
            'anggota' => ['required', 'array'],
            'anggota.*' => ['uuid'],

            'edek_updates' => ['required', 'array'],
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
    }

    public function rejectReport(Request $request, string $id): void
    {
        $request->validate([
            'type' => ['required', Rule::in(['stop', 'reject'])],
            'reason' => ['required', 'string'],
        ]);

        $report = Report::findOrFail($id);

        $report->update([
            'progress' => $request->type === 'stop'
                ? 'Laporan Dihentikan'
                : 'Laporan Ditolak',
            'reject_reason' => $request->reason,
        ]);
    }

    public function update(Report $report, array $data): Report
    {
        $validated = $this->validate($data);

        $report->update($validated);

        return $report->fresh();
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
