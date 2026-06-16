export const progressColor = (progress) => {
    switch (progress) {
        case 'Klarifikasi':
            return {
                badge: 'bg-blue-100 text-blue-700',
                dot: 'bg-blue-500',
                border: 'border-l-blue-500',
            };

        case 'Pemeriksaan':
            return {
                badge: 'bg-yellow-100 text-yellow-700',
                dot: 'bg-yellow-500',
                border: 'border-l-yellow-500',
            };

        case 'Kesimpulan':
            return {
                badge: 'bg-purple-100 text-purple-700',
                dot: 'bg-purple-500',
                border: 'border-l-purple-500',
            };

        case 'Pasca':
            return {
                badge: 'bg-green-100 text-green-700',
                dot: 'bg-green-500',
                border: 'border-l-green-500',
            };

        case 'Laporan Dihentikan':
            return {
                badge: 'bg-red-100 text-red-700',
                dot: 'bg-red-500',
                border: 'border-l-red-500',
            };

        default:
            return {
                badge: 'bg-gray-100 text-gray-700',
                dot: 'bg-gray-500',
                border: 'border-l-gray-500',
            };
    }
};
