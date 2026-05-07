<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Eye, EyeOff } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { update } from '@/actions/App/Http/Controllers/Auth/ChangePasswordController';
import { setTemporaryError, downloadRecoveryFile } from '@/lib/utils';
import { generateEncryptionContext } from '@/lib/crypto';

const page = usePage();
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const user = page.props.auth.user;

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
const passwordMessage = computed(() => {
    if (!form.password) {
        return '';
    }

    const messages = [];

    if (form.password.length < 8) {
        messages.push('minimal 8 karakter');
    }

    const chars = [];

    if (!/[A-Z]/.test(form.password)) {
        chars.push('huruf besar');
    }

    if (!/[a-z]/.test(form.password)) {
        chars.push('huruf kecil');
    }

    if (!/\d/.test(form.password)) {
        chars.push('angka');
    }

    if (!/[^A-Za-z0-9]/.test(form.password)) {
        chars.push('simbol');
    }

    if (chars.length) {
        messages.push(chars.join(', '));
    }

    if (!messages.length) {
        return '';
    }

    return `Password harus mengandung ${messages.join(' dan ')}.`;
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

        if (!form.password_confirmation) {
            setTemporaryError(
                form,
                'password_confirmation',
                'Konfirmasi password wajib diisi.',
            );

            return;
        }

        if (form.password !== form.password_confirmation) {
            form.setError(
                'password_confirmation',
                'Konfirmasi password harus sama dengan password baru.',
            );

            return;
        }

        if (passwordMessage.value) {
            form.setError('password', passwordMessage.value);

            return;
        }

        const encryption = await generateEncryptionContext(form.password);

        form.public_key = encryption.public_key;

        form.encrypted_private_key = encryption.encrypted_private_key;

        form.emek_password = encryption.emek_password;

        form.emek_password_salt = encryption.emek_password_salt;

        form.emek_recovery = encryption.emek_recovery;

        form.emek_recovery_salt = encryption.emek_recovery_salt;

        const recoveryCode = encryption.recovery_code;

        form.submit(update(), {
            onSuccess: () => {
                downloadRecoveryFile(recoveryCode, user.name);
                form.reset();
            },
        });
    } catch (error) {
        console.error(error);
    }
};

watch(
    () => [form.password, form.password_confirmation],
    () => {
        if (
            form.password_confirmation &&
            form.password !== form.password_confirmation
        ) {
            form.setError(
                'password_confirmation',
                'Konfirmasi password harus sama dengan password baru.',
            );
        } else {
            form.clearErrors('password_confirmation');
        }
    },
);
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

            <form @submit.prevent="submit" class="space-y-5">
                <!-- Current Password -->
                <div class="space-y-2">
                    <label class="text-sm font-medium">
                        Current Password
                    </label>

                    <div class="relative">
                        <input
                            v-model="form.current_password"
                            :type="showCurrentPassword ? 'text' : 'password'"
                            class="w-full rounded-lg border bg-background px-3 py-2 pr-10 text-sm"
                            autocomplete="current-password"
                        />

                        <button
                            type="button"
                            @click="showCurrentPassword = !showCurrentPassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-muted-foreground"
                        >
                            <Eye v-if="!showCurrentPassword" class="h-4 w-4" />

                            <EyeOff v-else class="h-4 w-4" />
                        </button>
                    </div>

                    <p
                        v-if="form.errors.current_password"
                        class="text-sm text-red-500"
                    >
                        {{ form.errors.current_password }}
                    </p>
                </div>

                <!-- New Password -->
                <!-- New Password -->
                <div class="space-y-2">
                    <label class="text-sm font-medium"> New Password </label>

                    <div class="relative">
                        <input
                            v-model="form.password"
                            :type="showNewPassword ? 'text' : 'password'"
                            class="w-full rounded-lg border bg-background px-3 py-2 pr-10 text-sm"
                            autocomplete="new-password"
                        />

                        <button
                            type="button"
                            @click="showNewPassword = !showNewPassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-muted-foreground"
                        >
                            <Eye v-if="!showNewPassword" class="h-4 w-4" />

                            <EyeOff v-else class="h-4 w-4" />
                        </button>
                    </div>

                    <p v-if="passwordMessage" class="text-sm text-red-500">
                        {{ passwordMessage }}
                    </p>

                    <!-- Backend Error -->
                    <p v-if="form.errors.password" class="text-sm text-red-500">
                        {{ form.errors.password }}
                    </p>
                </div>

                <!-- Confirm Password -->
                <div class="space-y-2">
                    <label class="text-sm font-medium">
                        Confirm Password
                    </label>

                    <div class="relative">
                        <input
                            v-model="form.password_confirmation"
                            :type="showConfirmPassword ? 'text' : 'password'"
                            class="w-full rounded-lg border bg-background px-3 py-2 pr-10 text-sm"
                            autocomplete="new-password"
                        />

                        <button
                            type="button"
                            @click="showConfirmPassword = !showConfirmPassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-muted-foreground"
                        >
                            <Eye v-if="!showConfirmPassword" class="h-4 w-4" />

                            <EyeOff v-else class="h-4 w-4" />
                        </button>
                    </div>

                    <p
                        v-if="form.errors.password_confirmation"
                        class="text-sm text-red-500"
                    >
                        {{ form.errors.password_confirmation }}
                    </p>
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-50"
                >
                    {{
                        form.processing
                            ? 'Updating Password...'
                            : 'Update Password'
                    }}
                </button>
            </form>
        </div>
    </div>
</template>
