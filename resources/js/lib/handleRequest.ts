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
    form: any,
    route: { url: string; method: string },
    options?: ActionOptions,
) {
    form.put(route.url, {
        onSuccess: () => options?.onSuccess?.(),
        onError: () => options?.onError?.(),
    });
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
