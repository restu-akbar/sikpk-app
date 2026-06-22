import { defineStore } from 'pinia';
import { shallowRef, ref } from 'vue';

const SESSION_TIMEOUT = 15 * 60 * 1000;

export const useCryptoStore = defineStore('crypto', () => {
    const privateKey = shallowRef<CryptoKey | null>(null);
    const userId = ref<string | null>(null);
    const ready = shallowRef(false);

    let expirationTimer: ReturnType<typeof setTimeout> | null = null;

    function activate(key: CryptoKey, id: string) {
        clearExpirationTimer();

        privateKey.value = key;
        userId.value = id;
        ready.value = true;

        expirationTimer = setTimeout(() => {
            clear();
            console.warn('Crypto session expired. Keys have been cleared.');
        }, SESSION_TIMEOUT);
    }

    function clear() {
        clearExpirationTimer();
        privateKey.value = null;
        userId.value = null;
        ready.value = false;
    }

    function clearExpirationTimer() {
        if (expirationTimer) {
            clearTimeout(expirationTimer);
            expirationTimer = null;
        }
    }

    function extendSession() {
        if (ready.value && privateKey.value) {
            clearExpirationTimer();
            expirationTimer = setTimeout(() => {
                clear();
                console.warn('Crypto session extended and will expire later.');
            }, SESSION_TIMEOUT);
        }
    }

    return {
        privateKey,
        userId,
        ready,
        activate,
        clear,
        extendSession,
    };
});
