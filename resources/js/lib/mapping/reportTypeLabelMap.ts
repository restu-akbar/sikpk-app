const reportTypeLabelMap: Record<
    string,
    Record<string, Record<string, string>>
> = {
    Klarifikasi: {
        notulensi: {
            document: 'Notulensi Klarifikasi Pelapor',
            documentation: 'Dokumentasi Klarifikasi Pelapor',
        },
    },

    Pemeriksaan: {
        periksa_saksi: {
            document: 'Formulir Pemeriksaan Saksi',
            documentation: 'Dokumentasi Pemeriksaan Saksi',
        },
        periksa_pelapor: {
            document: 'Formulir Pemeriksaan Pelapor',
            documentation: 'Dokumentasi Pemeriksaan Pelapor',
        },
        periksa_terlapor: {
            document: 'Formulir Pemeriksaan Terlapor',
            documentation: 'Dokumentasi Pemeriksaan Terlapor',
        },
    },

    Kesimpulan: {
        kesimpulan_rekomendasi: {
            document: 'Formulir Kesimpulan dan Rekomendasi',
        },
    },

    Pasca: {
        pemulihan_korban: {
            document: 'Formulir Pemulihan Korban',
        },
        pemulihan_nama_baik: {
            document: 'Formulir Pemulihan Nama Baik',
        },
    },
};

export function getReportLabel(
    type: string,
    subtype: string,
    attachmentType: string = 'document',
): string {
    return (
        reportTypeLabelMap?.[type]?.[subtype]?.[attachmentType] ??
        'Tidak diketahui'
    );
}
