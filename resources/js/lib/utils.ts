import { router } from '@inertiajs/vue3';
import type { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';
import { toast } from 'vue-sonner';

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

    const content = `${recoveryCode}`;

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

export function handleCreate(resourceRoute: string, data: any) {
    router.post(resourceRoute, data, {
        onSuccess: () => {
            toast.success('Data berhasil ditambahkan');
        },

        onError: () => {
            toast.error('Gagal menambahkan data');
        },
    });
}

export function handleEdit(
    url: string,
    data?: any,
    options?: {
        success?: string;
        error?: string;
        onSuccess?: () => void;
        onError?: () => void;
    },
) {
    router.put(url, data ?? {}, {
        onSuccess: () => {
            toast.success(options?.success ?? 'Data berhasil diperbarui');

            options?.onSuccess?.();
        },

        onError: () => {
            toast.error(options?.error ?? 'Gagal memperbarui data');

            options?.onError?.();
        },
    });
}

export function handleDelete(resourceRoute: string, row: any) {
    router.delete(`${resourceRoute}/${row.id}`, {
        onSuccess: () => {
            toast.success('Data berhasil dihapus');
        },

        onError: () => {
            toast.error('Gagal menghapus data');
        },
    });
}
