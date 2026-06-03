export type Jurusan = {
    name: string;
};

export type Prodi = {
    jurusanName: string;
    name: string;
    degreeLevel: 'D3' | 'D4' | 'S2';
};

export const jurusanList: Jurusan[] = [
    { name: 'Teknik Sipil' },
    { name: 'Teknik Mesin' },
    { name: 'Teknik Refrigerasi dan Tata Udara' },
    { name: 'Teknik Konversi Energi' },
    { name: 'Teknik Elektro' },
    { name: 'Teknik Kimia' },
    { name: 'Teknik Komputer dan Informatika' },
    { name: 'Akuntansi' },
    { name: 'Administrasi Niaga' },
    { name: 'Bahasa Inggris' },
];

export const prodiList: Prodi[] = [
    // Teknik Sipil
    { jurusanName: 'Teknik Sipil', name: 'Teknik Konstruksi Sipil', degreeLevel: 'D3' },
    { jurusanName: 'Teknik Sipil', name: 'Teknik Konstruksi Gedung', degreeLevel: 'D3' },
    { jurusanName: 'Teknik Sipil', name: 'Teknik Perancangan Jalan dan Jembatan', degreeLevel: 'D4' },
    { jurusanName: 'Teknik Sipil', name: 'Teknik Perawatan dan Perbaikan Gedung', degreeLevel: 'D4' },
    { jurusanName: 'Teknik Sipil', name: 'Rekayasa Infrastruktur', degreeLevel: 'S2' },

    // Teknik Mesin
    { jurusanName: 'Teknik Mesin', name: 'Teknik Mesin', degreeLevel: 'D3' },
    { jurusanName: 'Teknik Mesin', name: 'Teknik Aeronautika', degreeLevel: 'D3' },
    { jurusanName: 'Teknik Mesin', name: 'Teknik Perancangan dan Konstruksi Mesin', degreeLevel: 'D4' },
    { jurusanName: 'Teknik Mesin', name: 'Proses Manufaktur', degreeLevel: 'D4' },

    // Teknik Refrigerasi dan Tata Udara
    { jurusanName: 'Teknik Refrigerasi dan Tata Udara', name: 'Teknik Pendingin dan Tata Udara', degreeLevel: 'D3' },
    { jurusanName: 'Teknik Refrigerasi dan Tata Udara', name: 'Teknik Pendingin dan Tata Udara', degreeLevel: 'D4' },

    // Teknik Konversi Energi
    { jurusanName: 'Teknik Konversi Energi', name: 'Teknik Konversi Energi', degreeLevel: 'D3' },
    { jurusanName: 'Teknik Konversi Energi', name: 'Teknologi Pembangkit Tenaga Listrik', degreeLevel: 'D4' },
    { jurusanName: 'Teknik Konversi Energi', name: 'Teknik Konservasi Energi', degreeLevel: 'D4' },

    // Teknik Elektro
    { jurusanName: 'Teknik Elektro', name: 'Teknik Elektronika', degreeLevel: 'D3' },
    { jurusanName: 'Teknik Elektro', name: 'Teknik Listrik', degreeLevel: 'D3' },
    { jurusanName: 'Teknik Elektro', name: 'Teknik Telekomunikasi', degreeLevel: 'D3' },
    { jurusanName: 'Teknik Elektro', name: 'Teknik Elektronika', degreeLevel: 'D4' },
    { jurusanName: 'Teknik Elektro', name: 'Teknik Telekomunikasi', degreeLevel: 'D4' },
    { jurusanName: 'Teknik Elektro', name: 'Teknik Otomasi Industri', degreeLevel: 'D4' },

    // Teknik Kimia
    { jurusanName: 'Teknik Kimia', name: 'Teknik Kimia', degreeLevel: 'D3' },
    { jurusanName: 'Teknik Kimia', name: 'Analis Kimia', degreeLevel: 'D3' },
    { jurusanName: 'Teknik Kimia', name: 'Teknik Kimia Produksi Bersih', degreeLevel: 'D4' },

    // Teknik Komputer dan Informatika
    { jurusanName: 'Teknik Komputer dan Informatika', name: 'Teknik Informatika', degreeLevel: 'D3' },
    { jurusanName: 'Teknik Komputer dan Informatika', name: 'Teknik Informatika', degreeLevel: 'D4' },

    // Akuntansi
    { jurusanName: 'Akuntansi', name: 'Akuntansi', degreeLevel: 'D3' },
    { jurusanName: 'Akuntansi', name: 'Keuangan dan Perbankan', degreeLevel: 'D3' },
    { jurusanName: 'Akuntansi', name: 'Akuntansi Manajemen Pemerintahan', degreeLevel: 'D4' },
    { jurusanName: 'Akuntansi', name: 'Akuntansi', degreeLevel: 'D4' },
    { jurusanName: 'Akuntansi', name: 'Keuangan Syariah', degreeLevel: 'D4' },
    { jurusanName: 'Akuntansi', name: 'Keuangan dan Perbankan Syariah', degreeLevel: 'S2' },

    // Administrasi Niaga
    { jurusanName: 'Administrasi Niaga', name: 'Administrasi Bisnis', degreeLevel: 'D3' },
    { jurusanName: 'Administrasi Niaga', name: 'Manajemen Pemasaran', degreeLevel: 'D3' },
    { jurusanName: 'Administrasi Niaga', name: 'Usaha Perjalanan Wisata', degreeLevel: 'D3' },
    { jurusanName: 'Administrasi Niaga', name: 'Manajemen Aset', degreeLevel: 'D4' },
    { jurusanName: 'Administrasi Niaga', name: 'Administrasi Bisnis', degreeLevel: 'D4' },
    { jurusanName: 'Administrasi Niaga', name: 'Manajemen Pemasaran', degreeLevel: 'D4' },
    { jurusanName: 'Administrasi Niaga', name: 'Destinasi Pariwisata', degreeLevel: 'D4' },
    { jurusanName: 'Administrasi Niaga', name: 'Pemasaran, Inovasi, dan Teknologi', degreeLevel: 'S2' },

    // Bahasa Inggris
    { jurusanName: 'Bahasa Inggris', name: 'Bahasa Inggris', degreeLevel: 'D3' },
    { jurusanName: 'Bahasa Inggris', name: 'Bahasa Inggris untuk Komunikasi Bisnis dan Profesional', degreeLevel: 'D4' },
];

export function getProdiByJurusan(jurusanName: string): Prodi[] {
    return prodiList.filter((p) => p.jurusanName === jurusanName);
}