import { usePage } from '@inertiajs/vue3';
import { watch, nextTick } from 'vue';
import { toast } from 'vue-sonner';

export function useFlashToast() {
    const page = usePage();

    watch(
        () => page.props?.flash?.toast,
        (data) => {
            if (!data) return;

            nextTick(() => {
                switch (data.type) {
                    case 'error':
                        toast.error(data.message);
                        break;
                    case 'success':
                        toast.success(data.message);
                        break;
                    default:
                        toast(data.message);
                }
            });
        },
        { immediate: true },
    );
}
