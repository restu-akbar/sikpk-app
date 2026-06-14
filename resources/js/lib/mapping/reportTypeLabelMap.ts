const reportTypeLabelMap: Record<string, Record<string, string>> = {
    clarification: {
        generated_pdf: 'Notulensi Klarifikasi Pelapor',
        documentation: 'Dokumentasi Klarifikasi Pelapor',
        uploaded_pdf: 'Lampiran Klarifikasi Pelapor',
    },

    inspection: {
        generated_pdf: 'Notulensi Pemeriksaan',
        documentation: 'Dokumentasi Pemeriksaan',
        uploaded_pdf: 'Lampiran Pemeriksaan',
    },

    conclusion: {
        generated_pdf: 'Notulensi Kesimpulan',
        documentation: 'Dokumentasi Kesimpulan',
        uploaded_pdf: 'Lampiran Kesimpulan',
    },

    post: {
        generated_pdf: 'Notulensi Pasca Kegiatan',
        documentation: 'Dokumentasi Pasca Kegiatan',
        uploaded_pdf: 'Lampiran Pasca Kegiatan',
    },
};

export function getReportLabel(type: string, subtype: string): string {
    return reportTypeLabelMap?.[type]?.[subtype] ?? 'Tidak diketahui';
}
