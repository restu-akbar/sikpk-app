export type NomorIdentitasRule = {
    label: string;
    pattern: RegExp;
    maxLength: number;
    placeholder: string;
    hint: string;
};

export const nomorIdentitasRules: Record<string, NomorIdentitasRule> = {
    mahasiswa: {
        label: 'NIM',
        pattern: /^\d{9}$/,
        maxLength: 9,
        placeholder: 'Mis. 231511089',
        hint: 'NIM terdiri dari 9 digit angka',
    },
    dosen: {
        label: 'NIP',
        pattern: /^\d{18}$/,
        maxLength: 18,
        placeholder: 'Mis. 198501012010121001',
        hint: 'NIP terdiri dari 18 digit angka',
    },
    tendik: {
        label: 'NIK',
        pattern: /^\d{16}$/,
        maxLength: 16,
        placeholder: 'Mis. 3201011234560001',
        hint: 'NIK terdiri dari 16 digit angka sesuai KTP',
    },
};