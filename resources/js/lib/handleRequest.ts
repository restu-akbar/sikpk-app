import { router } from '@inertiajs/vue3';

type ActionOptions = {
    success?: string;
    error?: string;
    onSuccess?: () => void;
    onError?: () => void;
};

type Deletable = {
    id: string | number;
};

export function handleCreate(form: any, url: string, options?: ActionOptions) {
    form.post(url, {
        onSuccess: () => {
            options?.onSuccess?.();
        },

        onError: () => {
            options?.onError?.();
        },
    });
}

export function handleEdit<T>(url: string, data: T, options?: ActionOptions) {
    router.put(url, data, {
        onSuccess: () => {
            options?.onSuccess?.();
        },

        onError: () => {
            options?.onError?.();
        },
    });
}

export function handleDelete<T extends Deletable>(
    resourceRoute: string,
    row: T,
    options?: ActionOptions,
) {
    router.delete(`${resourceRoute}/${row.id}`, {
        onSuccess: () => {
            options?.onSuccess?.();
        },

        onError: () => {
            options?.onError?.();
        },
    });
}
