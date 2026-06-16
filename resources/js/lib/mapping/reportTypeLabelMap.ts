const reportTypeLabelMap: Record<string, Record<string, string>> = {
    Klarifikasi: {
        notulensi: 'Notulensi Klarifikasi Pelapor',
        documentation: 'Dokumentasi Klarifikasi Pelapor',
    },

    Pemeriksaan: {
        periksa_saksi: 'Formulir Pemeriksaan Saksi',
        periksa_pelapor: 'Formulir Pemeriksaan Pelapor',
        periksa_terlapor: 'Formulir Pemeriksaan Terlapor',
        documentation: 'Dokumentasi Pemeriksaan',
    },

    Kesimpulan: {
        kesimpulan_rekomendasi: 'Formulir Kesimpulan dan Rekomendasi',
        penyampaian_hasil: 'Surat BAP Penyampaian Hasil',
        pernyataan_pelaku: 'Surat Pernyataan Pelaku',
    },

    Pasca: {
        pemulihan_korban: 'Formulir Pemulihan Korban',
        pemulihan_nama_baik: 'Formulir Pemulihan Nama Baik',
    },
};

export function getReportLabel(type: string, subtype: string): string {
    return reportTypeLabelMap?.[type]?.[subtype] ?? 'Tidak diketahui';
}
