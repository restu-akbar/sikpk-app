import type { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}

export const setTemporaryError = (
    form: {
        setError: (field: string, message: string) => void;

        clearErrors: (...fields: string[]) => void;
    },

    field: string,
    message: string,
    duration = 3000,
) => {
    form.setError(field, message);

    setTimeout(() => {
        form.clearErrors(field);
    }, duration);
};

export const downloadRecoveryFile = (
    recoveryCode: string,
    userName: string,
) => {
    const safeName = userName
        .toLowerCase()
        .replace(/\s+/g, '-')
        .replace(/[^a-z0-9-_]/g, '');

    const content = `
Recovery Code

Simpan file ini dengan aman.
Kode ini digunakan untuk memulihkan akun Anda.

Recovery Code:
${recoveryCode}

Jangan bagikan kode ini kepada siapa pun.
`;

    const blob = new Blob([content], {
        type: 'text/plain;charset=utf-8',
    });

    const url = window.URL.createObjectURL(blob);

    const link = document.createElement('a');

    link.href = url;

    link.download = `recovery-code-${safeName}.txt`;

    document.body.appendChild(link);

    link.click();

    document.body.removeChild(link);

    window.URL.revokeObjectURL(url);
};
