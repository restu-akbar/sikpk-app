export const progressStatusMap = {
    'Laporan Baru': {
        title: 'Laporan Menunggu Tindak Lanjut',
        description:
            'Laporan Anda telah diterima dan sedang menunggu peninjauan awal oleh Satgas.',
        color: 'blue',
    },

    Klarifikasi: {
        title: 'Kasus Sedang Ditangani',
        description:
            'Satgas sedang melakukan klarifikasi dan pengumpulan informasi yang diperlukan terkait laporan Anda.',
        color: 'orange',
    },

    Pemeriksaan: {
        title: 'Kasus Sedang Ditangani',
        description:
            'Satgas sedang melakukan pemeriksaan dan pendalaman terhadap laporan yang disampaikan.',
        color: 'orange',
    },

    Kesimpulan: {
        title: 'Kasus Sedang Ditangani',
        description:
            'Satgas sedang menyusun kesimpulan berdasarkan hasil pemeriksaan dan klarifikasi yang telah dilakukan.',
        color: 'orange',
    },

    Pasca: {
        title: 'Pendampingan Pasca Penanganan',
        description:
            'Kasus telah memasuki tahap pasca penanganan. Satgas tetap melakukan pemantauan dan pendampingan sesuai kebutuhan.',
        color: 'green',
    },

    Selesai: {
        title: 'Kasus Selesai',
        description:
            'Seluruh proses penanganan laporan telah selesai dilaksanakan.',
        color: 'green',
    },

    'Laporan Dihentikan': {
        title: 'Penanganan Dihentikan',
        description:
            'Proses penanganan laporan dihentikan sesuai hasil evaluasi dan ketentuan yang berlaku.',
        color: 'gray',
    },

    'Laporan Ditolak': {
        title: 'Laporan Ditolak',
        description:
            'Laporan tidak dapat diproses lebih lanjut karena tidak memenuhi kriteria penanganan Satgas.',
        color: 'red',
    },
};
