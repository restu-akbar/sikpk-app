<?php

namespace App\Services;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReportService
{
    public function store(array $data, Request $request): Report
    {
        return DB::transaction(function () use ($data, $request) {

            $report = Report::create([
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

                'agreed' => true,
            ]);

            foreach ($request->input('bukti', []) as $index => $item) {

                $file = $request->file("bukti.$index.file");

                $path = Storage::disk('private')->putFileAs(
                    'reports',
                    $file,
                    Str::uuid() . '.enc'
                );

                $report->evidences()->create([
                    'path' => $path,
                    'edeks' => $item['edeks'],
                ]);
            }

            return $report;
        });
    }
}
