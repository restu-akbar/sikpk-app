<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { update } from '@/routes/password';
import { generateEncryption } from '@/lib/crypto';
import { readRecoveryFile } from '@/lib/crypto/readRecoveryFile';
import { validateNewPassword } from '@/lib/validations';

const props = defineProps<{
    token: string;
    email: string;
    emek_recovery: string;
    emek_recovery_salt: string;
}>();

defineOptions({
    layout: {
        title: 'Reset password',
        description: 'Upload file recovery kamu untuk melanjutkan.',
    },
});

type Step = 'upload' | 'new-password';

const step = ref<Step>('upload');
const recoveryCode = ref('');
const fileError = ref('');
const isVerifying = ref(false);
const newPassword = ref('');
const confirmPassword = ref('');
const passwordError = ref('');
const isSubmitting = ref(false);
const result = ref<{
    emek_password: string;
    emek_password_salt: string;
} | null>(null);

async function handleFileChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;

    fileError.value = '';
    isVerifying.value = true;

    try {
        const code = await readRecoveryFile(file);

        await generateEncryption({
            mode: 'recovery',
            recoveryCode: code,
            newPassword: '__probe__',
            emek_recovery: props.emek_recovery,
            emek_recovery_salt: props.emek_recovery_salt,
        });

        recoveryCode.value = code;
        step.value = 'new-password';
    } catch {
        fileError.value =
            'File recovery tidak valid atau tidak cocok dengan akun ini.';
    } finally {
        isVerifying.value = false;
    }
}

async function handleSubmit() {
    passwordError.value = '';

    const validationError = validateNewPassword(
        newPassword.value,
        confirmPassword.value,
    );

    if (validationError) {
        passwordError.value = validationError;
        return;
    }

    isSubmitting.value = true;

    try {
        const encrypted = await generateEncryption({
            mode: 'recovery',
            recoveryCode: recoveryCode.value,
            newPassword: newPassword.value,
            emek_recovery: props.emek_recovery,
            emek_recovery_salt: props.emek_recovery_salt,
        });

        result.value = encrypted;

        router.post(
            update(),
            {
                token: props.token,
                email: props.email,
                password: newPassword.value,
                password_confirmation: newPassword.value,
                emek_password: encrypted.emek_password,
                emek_password_salt: encrypted.emek_password_salt,
            },
            {
                onError: (errors) => {
                    const emailError = errors.email;

                    if (
                        emailError?.includes('token') ||
                        emailError?.includes('expired')
                    ) {
                        passwordError.value =
                            'Link reset password sudah tidak berlaku. Silakan lakukan permintaan reset password kembali.';
                        return;
                    }

                    passwordError.value =
                        errors.password ||
                        errors.email ||
                        'Gagal mereset password.';
                },
            },
        );
    } catch {
        passwordError.value = 'Terjadi kesalahan. Silakan coba lagi.';
    } finally {
        isSubmitting.value = false;
    }
}
</script>

<template>
    <Head title="Reset password" />

    <div class="space-y-6">
        <!-- ── Step 1: Upload file recovery ── -->
        <template v-if="step === 'upload'">
            <p class="text-sm text-muted-foreground">
                Upload file <code>.txt</code> recovery yang anda simpan saat
                pertama kali login.
            </p>

            <div class="grid gap-2">
                <Label for="recovery-file">File Recovery</Label>
                <input
                    id="recovery-file"
                    type="file"
                    accept=".txt"
                    class="block w-full cursor-pointer text-sm file:mr-4 file:rounded file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-medium file:text-primary-foreground hover:file:bg-primary/90"
                    :disabled="isVerifying"
                    @change="handleFileChange"
                />
                <div
                    v-if="isVerifying"
                    class="flex items-center gap-2 text-sm text-muted-foreground"
                >
                    <Spinner class="size-4" />
                    Memverifikasi file...
                </div>
                <InputError :message="fileError" />
            </div>
        </template>

        <!-- ── Step 2: Input password baru ── -->
        <template v-else-if="step === 'new-password'">
            <p class="text-sm text-muted-foreground">
                File recovery valid. Masukkan password baru anda.
            </p>

            <form class="space-y-6" @submit.prevent="handleSubmit">
                <div class="grid gap-2">
                    <Label for="new-password">Password Baru</Label>
                    <PasswordInput
                        id="new-password"
                        v-model="newPassword"
                        name="new_password"
                        class="mt-1 block w-full"
                        required
                        autocomplete="new-password"
                        autofocus
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="confirm-password">Konfirmasi Password</Label>
                    <PasswordInput
                        id="confirm-password"
                        v-model="confirmPassword"
                        name="confirm_password"
                        class="mt-1 block w-full"
                        required
                        autocomplete="new-password"
                    />
                    <InputError :message="passwordError" />
                </div>

                <Button class="w-full" type="submit" :disabled="isSubmitting">
                    <Spinner v-if="isSubmitting" />
                    Simpan Password Baru
                </Button>
            </form>
        </template>
    </div>
</template>
