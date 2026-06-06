export type Disabilitas =
    | 'tidak_ada'
    | 'penglihatan'
    | 'pendengaran'
    | 'fisik'
    | 'lainnya';

export type Report = {
    id: string;

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

    bukti: string[];

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
