import { User } from './auth';

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

export type ReportSubject = {
    id: string;
    report_id: string;
    jenis: 'korban' | 'terlapor';
    nama: string | null;
    nomor_identitas: string | null;
    nomor_wa: string | null;
    jenis_kelamin: string | null;
    peran_akademik: string | null;
    jurusan: string | null;
    prodi: string | null;
    angkatan: string | null;
    disabilitas: boolean;
};

export type Report = {
    id: string;

    statusPelapor: string;

    namaTerlapor: string;
    case_number: string;
    team_number: string;
    statusTerlapor: string;
    progress: string;

    status_pelapor: string;
    nama_terlapor: string;
    status_terlapor: string;

    jenis_kekerasan: string;
    tempat_kejadian: string;
    waktu_kejadian: string;

    kronologi: string;

    reporter: any;
    report_logs: ReportLog[];
    report_documents: any;
    disabilitas: Disabilitas[];
    members: User[];
    bukti: string[];
    audio_recordings?: AudioRecording[];

    has_notulensi?: boolean;
    korban?: ReportSubject | null;
    terlapor?: ReportSubject | null;

    agreed: boolean;

    created_at: string;
    updated_at: string;
};

export type ReportForm = {
    nama: string;
    whatsapp: string;
    jurusan: string;
    prodi: string;
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

export const DEFAULT_REJECTED_REASONS = [
    {
        value: 'ranah_satgas',
        label: 'Bukan Ranah Satgas',
    },
    {
        value: 'unit_lain',
        label: 'Dialihkan ke Unit Lain',
    },
    {
        value: 'tidak_berkenan',
        label: 'Pelapor tidak berkenan melanjutkan',
    },
] as const;

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
} as const;

export interface Attachment {
    file: File | null;
    filename: string;
    mimeType: string;
    size: number;
    edeks: Record<string, any>;
    type: string;
    subtype: string;
}

interface ReportLog {
    id: string;
    progress: string;
    created_at: string;
}
