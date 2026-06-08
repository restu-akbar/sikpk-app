<?php

namespace App\Services;

use App\Models\AudioRecording;
use App\Models\Report;
use App\Models\ReportEvidence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ReportService
{
    public function index()
    {
        $query = Report::with(['evidences', 'reporter']);

        if (auth('google')->check()) {
            return $query
                ->where('reporter_id', auth('google')->id())
                ->latest()
                ->paginate(10);
        }

        if (auth('web')->check()) {
            $user = auth('web')->user();
            if ($user->role === 'anggota') {
                $query->where('reporter_id', $user->id);
            }
        }

        return $query->latest()->paginate(10);
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

            $report = Report::create([
                'reporter_id'   => $reporterId,

                'status_pelapor' => $data['statusPelapor'],

                'nama_terlapor'  => $data['namaTerlapor'],
                'status_terlapor' => $data['statusTerlapor'],

                'jenis_kekerasan' => $data['jenisKekerasan'],

                'tempat_kejadian' => $data['tempatKejadian'] ?? null,
                'waktu_kejadian'  => $data['waktuKejadian'] ?? null,

                'kronologi' => $data['kronologi'] ?? null,
            ]);

            foreach ($request->input('bukti', []) as $index => $item) {

                $file = $request->file("bukti.$index.file");

                $path = Storage::disk('private')->putFileAs(
                    'reports',
                    $file,
                    Str::uuid() . '.enc'
                );

                $edeks = isset($item['edeks'])
                    ? json_decode($item['edeks'], true)
                    : null;

                $report->evidences()->create([
                    'path' => $path,
                    'edeks' => $edeks,
                    'original_filename' => $item['filename'] ?? null,
                    'mime_type' => $item['mime_type'] ?? null,
                    'size' => $item['size'] ?? null,
                ]);
            }

            foreach ($request->input('audio_recordings', []) as $index => $item) {
                $file = $request->file("audio_recordings.$index.file");

                $path = Storage::disk('private')->putFileAs(
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

        $report->update([
            'progress' => 'Klarifikasi',
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
}
