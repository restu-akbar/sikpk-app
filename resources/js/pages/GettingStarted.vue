<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { update } from '@/routes/satgas/getting-started';
import {
    setTemporaryError,
    downloadRecoveryFile,
    handleEdit,
} from '@/lib/utils';
import { generateEncryption } from '@/lib/crypto';
import ChangePasswordForm from '@/components/ChangePasswordForm.vue';

const page = usePage();
const user = page.props.auth.user;

const showRecoveryDialog = ref(false);
const pendingRecoveryCode = ref('');

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',

    public_key: '',
    encrypted_private_key: '',

    emek_password: '',
    emek_password_salt: '',

    emek_recovery: '',
    emek_recovery_salt: '',
});

const submit = async () => {
    try {
        form.clearErrors();

        if (!form.current_password) {
            setTemporaryError(
                form,
                'current_password',
                'Password saat ini wajib diisi.',
            );
            return;
        }
        if (!form.password) {
            setTemporaryError(form, 'password', 'Password baru wajib diisi.');
            return;
        }
        if (form.password !== form.password_confirmation) {
            form.setError(
                'password_confirmation',
                'Konfirmasi password harus sama.',
            );
            return;
        }

        const encryption = await generateEncryption({
            mode: 'initial',
            password: form.password,
        });

        form.public_key = encryption.public_key;
        form.encrypted_private_key = encryption.encrypted_private_key;
        form.emek_password = encryption.emek_password;
        form.emek_password_salt = encryption.emek_password_salt;
        form.emek_recovery = encryption.emek_recovery;
        form.emek_recovery_salt = encryption.emek_recovery_salt;

        const recoveryCode = encryption.recovery_code;

        form.submit(update(), {
            preserveState: true,
            onSuccess: () => {
                setTimeout(() => {
                    pendingRecoveryCode.value = recoveryCode;
                    showRecoveryDialog.value = true;
                }, 100);
            },
        });
    } catch (error) {
        console.error(error);
    }
};

const handleDownloadRecovery = () => {
    downloadRecoveryFile(pendingRecoveryCode.value, user.name);

    handleEdit(
        '/satgas/getting-started/complete',
        {},
        {
            success: 'Setup user berhasil',
            error: 'Setup user gagal',
        },
    );
};
</script>

<template>
    <Head title="Change Password" />

    <div class="flex min-h-screen items-center justify-center bg-muted/30 px-4">
        <div
            class="w-full max-w-md rounded-2xl border bg-background p-8 shadow-sm"
        >
            <div class="mb-6 space-y-2 text-center">
                <h1 class="text-2xl font-bold tracking-tight">
                    Ganti Password Anda
                </h1>

                <p class="text-sm text-muted-foreground">
                    Untuk alasan keamanan, silakan ganti password sesuai
                    preferensi anda sebelum lanjut.
                </p>
            </div>

            <ChangePasswordForm :form="form" :on-submit="submit" />
        </div>

        <div
            v-if="showRecoveryDialog"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
        >
            <div
                class="w-full max-w-md rounded-2xl bg-background p-6 shadow-xl"
            >
                <div class="space-y-4">
                    <div>
                        <h2 class="text-lg font-semibold">
                            Simpan Recovery Key Anda
                        </h2>

                        <p class="mt-2 text-sm text-muted-foreground">
                            Recovery key digunakan untuk memulihkan akses ke
                            data terenkripsi anda apabila lupa password.
                        </p>
                    </div>

                    <div
                        class="rounded-lg border border-yellow-500/30 bg-yellow-500/10 p-4 text-sm"
                    >
                        <ul class="list-disc space-y-1 pl-5">
                            <li>File ini hanya dapat diunduh sekali.</li>
                            <li>
                                Jangan bagikan recovery key kepada siapapun.
                            </li>
                            <li>Simpan di tempat yang aman.</li>
                            <li>
                                Jika recovery key hilang dan password lupa, akun
                                anda tidak dapat dipulihkan.
                            </li>
                        </ul>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-lg bg-primary px-4 py-2 text-sm text-primary-foreground"
                            @click="handleDownloadRecovery"
                        >
                            Download Recovery Key
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
