import { router } from '@inertiajs/vue3';

type ActionOptions = {
    success?: string;
    error?: string;
    onSuccess?: () => void;
    onError?: () => void;
};

export function handleCreate(
    form: any,
    route: { url: string; method: string },
    options?: ActionOptions,
) {
    form.post(route.url, {
        onSuccess: () => options?.onSuccess?.(),
        onError: () => options?.onError?.(),
    });
}

export function handleEdit(
    form?: any | null,
    route: { url: string; method: string },
    options?: ActionOptions,
) {
    const callbacks = {
        onSuccess: () => options?.onSuccess?.(),
        onError: () => options?.onError?.(),
    };

    if (form) {
        form.put(route, callbacks);
    } else {
        router.put(route, {}, callbacks);
    }
}

export function handleDelete(
    route: { url: string; method: string },
    options?: ActionOptions,
) {
    router.delete(route.url, {
        onSuccess: () => options?.onSuccess?.(),
        onError: () => options?.onError?.(),
    });
}
