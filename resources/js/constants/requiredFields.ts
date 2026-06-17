export const requiredFields = {
    jenisKekerasan: 'Jenis kekerasan',
    nama: 'Nama',
    statusPelapor: 'Status pelapor',
    statusCivitas: 'Status civitas',
    whatsapp: 'Nomor WhatsApp',
    jurusan: 'Jurusan',
    prodi: 'Program studi',
    catatanKlarifikasi: 'Catatan klarifikasi',
} as const;

export type RequiredFormKeys = keyof typeof requiredFields;
