import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, onMounted } from 'vue';
import { generateDecryption } from '@/lib/crypto';
import { useCryptoStore } from '@/lib/crypto/store';

export function useCryptoUnlock() {
    const page = usePage();
    const user = page.props.auth.user;

    const cryptoStore = useCryptoStore();

    const showUnlockDialog = ref(false);
    const unlockLoading = ref(false);
    const unlockError = ref('');

    onMounted(() => {
        if (!cryptoStore.ready) {
            showUnlockDialog.value = true;
        }
    });

    async function unlockCrypto(password: string) {
        unlockLoading.value = true;
        unlockError.value = '';

        try {
            const { data } = await axios.get('/satgas/crypto');

            const privateKey = await generateDecryption({
                password,
                emek_password: data.emek_password,
                emek_password_salt: data.emek_password_salt,
                encrypted_private_key: data.encrypted_private_key,
            });

            cryptoStore.activate(privateKey, user.id);

            showUnlockDialog.value = false;
        } catch (error) {
            unlockError.value = 'Kata sandi salah';
        } finally {
            unlockLoading.value = false;
        }
    }

    function cancelUnlock() {
        router.visit('/satgas/dashboard');
    }

    return {
        showUnlockDialog,
        unlockLoading,
        unlockError,
        unlockCrypto,
        cancelUnlock,
    };
}
