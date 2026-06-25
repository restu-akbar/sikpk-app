export const requiredFields = {
    jenisKekerasan: 'Jenis kekerasan',
    nama: 'Nama',
    statusPelapor: 'Status pelapor',
    statusCivitas: 'Status civitas',
    whatsapp: 'Nomor WhatsApp',
    jurusan: 'Jurusan',
    prodi: 'Program studi',
    catatanKlarifikasi: 'Catatan klarifikasi',
    kronologi: 'Kronologi kejadian',
    ciriFisik: 'Ciri Fisik',
    alasan: 'Alasan pengaduan',
    domisili: 'Domisili',
    kontakLain: 'Kontak Pihak Lain',
    jenisKelamin: 'Jenis kelamin',
} as const;

export const rootRequiredFields = {
    jenisKekerasan: 'Jenis kekerasan',
    kronologi: 'Kronologi kejadian',
    ciriFisik: 'Ciri fisik',
    alasan: 'Alasan pengaduan',
    kebutuhanKorban: 'Kebutuhan korban',
};

export const pelaporRequiredFields = {
    nama: 'Nama pelapor',
    status: 'Status pelapor',
    civitas: 'Status civitas',
    whatsapp: 'Nomor WhatsApp',
    jurusan: 'Jurusan',
    prodi: 'Program studi',
    domisili: 'Domisili',
    kontakLain: 'Kontak pihak lain',
};
export const clarifyRequiredFields = {
    jenisKekerasan: 'Jenis kekerasan',
    nama: 'Nama',
    status: 'Status pelapor',
    civitas: 'Status civitas',
    whatsapp: 'Nomor WhatsApp',
    jurusan: 'Jurusan',
    prodi: 'Program studi',
    catatanKlarifikasi: 'Catatan klarifikasi',
    jenisKelamin: 'Jenis kelamin',
} as const;
export type RequiredFormKeys = keyof typeof requiredFields;
