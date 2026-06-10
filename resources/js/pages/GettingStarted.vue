<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { update } from '@/routes/satgas/getting-started';
import { setTemporaryError, downloadRecoveryFile } from '@/lib/utils';
import { generateEncryption } from '@/lib/crypto';
import ChangePasswordForm from '@/components/ChangePasswordForm.vue';
import { handleEdit } from '@/lib/handleRequest';
import { complete } from '@/routes/satgas/getting-started';

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
    handleEdit(null, complete());
};
</script>

<template>
    <Head title="Ganti Password" />

    <!-- Right panel content: heading + form -->
    <div class="flex flex-col gap-6">
        <!-- Heading -->
        <div class="flex flex-col gap-4">
            <h2
                class="font-display text-4xl font-bold tracking-tight text-foreground"
            >
                Ganti Password
            </h2>
            <p class="text-base leading-relaxed text-muted-foreground">
                Untuk alasan keamanan, silakan ganti password sesuai preferensi
                anda sebelum lanjut.
            </p>
        </div>

        <!-- Password form -->
        <ChangePasswordForm
            :form="form"
            :on-submit="submit"
            submit-label="Ubah Password"
            processing-label="Mengubah..."
        />
    </div>

    <!-- Recovery key dialog: fixed overlay, outside layout flow -->
    <div
        v-if="showRecoveryDialog"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4 backdrop-blur-sm"
    >
        <div class="w-full max-w-md rounded-2xl bg-background p-8 shadow-2xl">
            <div class="flex flex-col gap-6">
                <!-- Header -->
                <div class="flex flex-col gap-2">
                    <h2 class="font-display text-xl font-bold text-foreground">
                        Simpan Recovery Key Anda
                    </h2>
                    <p class="text-sm leading-relaxed text-muted-foreground">
                        Recovery key digunakan untuk memulihkan akses ke data
                        terenkripsi anda apabila lupa password.
                    </p>
                </div>

                <!-- Warning list -->
                <div
                    class="rounded-xl border border-amber-500/30 bg-amber-500/10 px-5 py-4 text-sm text-amber-700"
                >
                    <ul class="list-disc space-y-1 pl-4">
                        <li>File ini hanya dapat diunduh sekali.</li>
                        <li>Jangan bagikan recovery key kepada siapapun.</li>
                        <li>Simpan di tempat yang aman.</li>
                        <li>
                            Jika recovery key hilang dan password lupa, akun
                            anda tidak dapat dipulihkan.
                        </li>
                    </ul>
                </div>

                <!-- Action -->
                <div class="flex justify-end">
                    <button
                        type="button"
                        class="h-12 rounded-xl bg-brand-accent px-6 font-display text-base font-semibold text-white transition-colors hover:bg-brand-accent/90"
                        @click="handleDownloadRecovery"
                    >
                        Download Recovery Key
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
