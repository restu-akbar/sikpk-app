import { defineStore } from 'pinia';
import { shallowRef, ref } from 'vue';

export const useCryptoStore = defineStore('crypto', () => {
    const privateKey = shallowRef<CryptoKey | null>(null);
    const userId = ref<string | null>(null);
    const ready = shallowRef(false);

    function activate(key: CryptoKey, id: string) {
        privateKey.value = key;
        userId.value = id;
        ready.value = true;
    }

    function clear() {
        privateKey.value = null;
        userId.value = null;
        ready.value = false;
    }

    return {
        privateKey,
        userId,
        ready,
        activate,
        clear,
    };
});
