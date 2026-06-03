<?php

namespace App\Services;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

            $report = Report::create([
                'reporter_id' => auth('google')->check()
                    ? auth('google')->id()
                    : null,
                'nama' => $data['nama'],
                'whatsapp' => $data['whatsapp'],

                'status_pelapor' => $data['statusPelapor'],
                'status_civitas' => $data['statusCivitas'] ?? null,

                'nama_terlapor' => $data['namaTerlapor'],
                'status_terlapor' => $data['statusTerlapor'],

                'jenis_kekerasan' => $data['jenisKekerasan'],

                'tempat_kejadian' => $data['tempatKejadian'],
                'waktu_kejadian' => $data['waktuKejadian'],

                'kronologi' => $data['kronologi'],

                'disabilitas' => $data['disabilitas'],
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

            return $report;
        });
    }
}
