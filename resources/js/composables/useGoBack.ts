import { router } from '@inertiajs/vue3';

export function useGoBack(fallback = '/') {
    const goBack = () => {
        if (window.history.length > 1) {
            window.history.back();
        } else {
            router.visit(fallback);
        }
    };

    return {
        goBack,
    };
}
