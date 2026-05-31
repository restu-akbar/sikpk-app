export type UseInitialsReturn = {
    getInitials: (fullName?: string) => string;
};

const AVATAR_COLORS = [
    'bg-[#F5821F] text-white',   // orange
    'bg-[#134881] text-white',   // blue
    'bg-[#16a34a] text-white',   // green
    'bg-[#9333ea] text-white',   // purple
    'bg-[#dc2626] text-white',   // red
    'bg-[#0891b2] text-white',   // cyan
    'bg-[#d97706] text-white',   // amber
    'bg-[#db2777] text-white',   // pink
    'bg-[#059669] text-white',   // emerald
    'bg-[#7c3aed] text-white',   // violet
];

export function getAvatarColor(name?: string): string {
    if (!name) return AVATAR_COLORS[0];
    const hash = [...name].reduce((acc, char) => acc + char.charCodeAt(0), 0);
    return AVATAR_COLORS[hash % AVATAR_COLORS.length];
}

export function getInitials(fullName?: string): string {
    if (!fullName) {
        return '';
    }

    const names = fullName.trim().split(' ');

    if (names.length === 0) {
        return '';
    }

    if (names.length === 1) {
        return names[0].charAt(0).toUpperCase();
    }

    return `${names[0].charAt(0)}${names[names.length - 1].charAt(0)}`.toUpperCase();
}

export function useInitials(): UseInitialsReturn {
    return { getInitials };
}
