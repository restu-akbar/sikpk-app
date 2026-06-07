export type Disabilitas =
    | 'tidak_ada'
    | 'penglihatan'
    | 'pendengaran'
    | 'fisik'
    | 'lainnya';

export type AudioRecording = {
    id: string;
    report_id: string;
    path: string;
    mime_type: string | null;
    size: number | null;
    duration: number | null;
    order: number;
    created_at: string;
    updated_at: string;
};

export type Report = {
    id: string;

    nama: string;
    whatsapp: string;

    statusPelapor: string;
    statusCivitas: string;

    namaTerlapor: string;
    statusTerlapor: string;

    jenisKekerasan: string;
    tempatKejadian: string | null;
    waktuKejadian: string | null;

    kronologi: string | null;

    disabilitas: Disabilitas[];

    bukti: string[];
    audio_recordings?: AudioRecording[];

    agreed: boolean;

    created_at: string;
    updated_at: string;
};

export type ReportForm = {
    nama: string;
    whatsapp: string;

    statusPelapor: string;
    statusCivitas: string;

    namaTerlapor: string;
    statusTerlapor: string;

    jenisKekerasan: string;
    tempatKejadian: string;
    waktuKejadian: string;

    kronologi: string;

    disabilitas: Disabilitas[];

    bukti: File[];

    agreed: boolean;
};

export const REJECTED_REASON_MAPPING = {
    'Laporan Baru': [
        {
            value: 'ranah_satgas',
            label: 'Bukan Ranah Satgas',
        },
        {
            value: 'unit_lain',
            label: 'Dialihkan ke Unit Lain',
        },
    ],

    'Pemeriksaan': [
        {
            value: 'ranah_satgas',
            label: 'Bukan Ranah Satgas',
        },
        {
            value: 'unit_lain',
            label: 'Dialihkan ke Unit Lain',
        },
        {
            value: 'bukti_tidak_cukup',
            label: 'Bukti Tidak Cukup',
        },
    ],
} as const;
