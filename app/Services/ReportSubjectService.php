<?php

namespace App\Services;

use App\Models\Report;
use App\Models\ReportSubject;

class ReportSubjectService
{
    public function syncFromHandlingForm(Report $report, array $input, ?string $subtype): void
    {
        $this->syncJenisKekerasan($report, $input);

        match ($subtype) {
            'notulensi' => $this->syncNotulensi($report, $input),
            'periksa_pelapor' => $this->syncPeriksaPelapor($report, $input),
            'periksa_terlapor' => $this->syncPeriksaTerlapor($report, $input),
            default => null,
        };
    }

    protected function syncJenisKekerasan(Report $report, array $input): void
    {
        $jenisKekerasan = $input['jenisKekerasan']
            ?? $input['pelapor']['jenisKekerasan'] ?? null
            ?? $input['terlapor']['jenisKekerasan'] ?? null;

        if ($jenisKekerasan && $jenisKekerasan !== $report->jenis_kekerasan) {
            $report->update(['jenis_kekerasan' => $jenisKekerasan]);
        }
    }

    protected function syncNotulensi(Report $report, array $input): void
    {
        $isFirstNotulensi = $report->reportDocuments()
            ->where('subtype', 'notulensi')
            ->doesntExist();

        if (($input['status'] ?? null) !== 'korban' || !$isFirstNotulensi) {
            return;
        }

        ReportSubject::updateOrCreate(
            ['report_id' => $report->id, 'jenis' => 'korban'],
            [
                ...$this->mapIdentity($input, hasGender: false),
                'jenis_kelamin' => $input['jenisKelamin'] ?? null,
            ]
        );
    }

    protected function syncPeriksaPelapor(Report $report, array $input): void
    {
        if (empty($input['korban']['nama'] ?? null)) {
            return;
        }

        ReportSubject::updateOrCreate(
            ['report_id' => $report->id, 'jenis' => 'korban'],
            $this->mapIdentity($input['korban'])
        );
    }

    protected function syncPeriksaTerlapor(Report $report, array $input): void
    {
        if (empty($input['terlapor'])) {
            return;
        }

        ReportSubject::updateOrCreate(
            ['report_id' => $report->id, 'jenis' => 'terlapor'],
            $this->mapIdentity($input['terlapor'])
        );
    }

    protected function mapIdentity(array $data, bool $hasGender = true): array
    {
        return [
            'nama' => $data['nama'] ?? null,
            'nomor_identitas' => $data['nomorIdentitas'] ?? null,
            'nomor_wa' => $data['whatsapp'] ?? null,
            'jenis_kelamin' => $hasGender ? ($data['status'] ?? null) : null,
            'peran_akademik' => $data['civitas'] ?? null,
            'jurusan' => $data['jurusan'] ?? null,
            'prodi' => $data['prodi'] ?? null,
            'angkatan' => $data['angkatan'] ?? null,
            'disabilitas' => (bool) ($data['disabilitas'] ?? false),
        ];
    }
}
