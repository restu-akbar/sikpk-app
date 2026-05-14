import { defineStore } from 'pinia';
import { shallowRef } from 'vue';

export const useCryptoStore = defineStore('crypto', () => {
    const privateKey = shallowRef<CryptoKey | null>(null);
    const ready = shallowRef(false);
    function activate(key: CryptoKey) {
        privateKey.value = key;
        ready.value = true;
    }

    function clear() {
        privateKey.value = null;
        ready.value = false;
    }

    return {
        privateKey,
        ready,

        activate,
        clear,
    };
});
