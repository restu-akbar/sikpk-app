export interface DistributionItem {
    label: string;
    total: number;
}

export interface StackedSeries {
    label: string;
    data: number[];
}

export interface CrosstabData {
    labels: string[];
    series: StackedSeries[];
}

export interface DashboardAnalytics {
    filter: {
        year: string;
        semester: string;
        availableYears: number[];
    };
    trend: DistributionItem[];
    laporan: {
        masuk: number;
        dibatalkan: number;
        berlangsung: number;
        selesai: number;
    };
    jenisKekerasan: DistributionItem[];
    demografi: {
        pelaporPeran: DistributionItem[];
        pelaporJurusan: DistributionItem[];
        korbanPeranGender: CrosstabData;
        korbanJurusan: DistributionItem[];
        terlaporPeranGender: CrosstabData;
        terlaporJurusan: DistributionItem[];
    };
    disabilitas: {
        korban: number;
        terlapor: number;
    };
}
