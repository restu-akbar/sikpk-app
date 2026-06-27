<script setup lang="ts">
import { toast } from 'vue-sonner';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { reactive, ref } from 'vue';

import PasswordInput from '@/components/PasswordInput.vue';
import FormField from '@/components/form/FormField.vue';
import FieldLabel from '@/components/form/FieldLabel.vue';

import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';

import { generateDecryption } from '@/lib/crypto';
import { useCryptoStore } from '@/lib/crypto/store';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

const processing = ref(false);

const form = reactive({
    email: '',
    password: '',
    remember: false,
});

async function submit() {
    const cryptoStore = useCryptoStore();
    processing.value = true;

    try {
        const response = await axios.post('/satgas/login', form);
        const user = response.data.user;

        if (response.data.must_change_password) {
            window.location.href = '/satgas/getting-started';
            return;
        }

        const crypto = response.data.crypto;

        const privateKey = await generateDecryption({
            password: form.password,
            emek_password: crypto.emek_password,
            emek_password_salt: crypto.emek_password_salt,
            encrypted_private_key: crypto.encrypted_private_key,
        });

        cryptoStore.activate(privateKey, user.id);
        router.visit('/satgas/dashboard');
    } catch (error: any) {
        if (error.response?.status === 422) {
            toast.error(
                error.response.data.message ?? 'Email atau kata sandi salah.',
            );
            return;
        }

        toast.error('Terjadi kesalahan. Silakan coba lagi.');
    } finally {
        processing.value = false;
    }
}
</script>

<template>
    <Head title="Masuk" />

    <div class="flex flex-col gap-8">
        <!-- Page heading -->
        <div class="flex flex-col gap-4">
            <h2
                class="font-display text-4xl font-bold tracking-tight text-foreground"
            >
                Masuk Satgas
            </h2>
            <p class="text-base leading-relaxed text-muted-foreground">
                Hanya untuk anggota Satuan Tugas Polban yang telah ditugaskan.
            </p>
        </div>

        <!-- Status message from server (e.g. password reset success) -->
        <div
            v-if="status"
            class="rounded-md bg-green-50 px-4 py-3 text-base font-medium text-green-700 dark:bg-green-950/50 dark:text-green-300"
        >
            {{ status }}
        </div>

        <!-- Login form: gap-6 = 24px antar field -->
        <form @submit.prevent="submit" novalidate class="flex flex-col gap-6">
            <!-- Email field -->
            <FormField
                v-model="form.email"
                label="Email Polban"
                required
                id="email"
                type="email"
                name="email"
                autofocus
                :tabindex="1"
                autocomplete="email"
                placeholder="r.satgas@polban.ac.id"
                class="h-12 text-base"
            />

            <!-- Password field -->
            <div>
                <FieldLabel required>Kata sandi</FieldLabel>
                <PasswordInput
                    v-model="form.password"
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="Kata sandi"
                    :class="['h-12 text-base']"
                />
            </div>

            <!-- Submit button: h-14 = 56px (4px * 14) -->
            <Button
                type="submit"
                variant="brand-accent"
                size="lg"
                class="mt-2 h-14 w-full font-display text-lg font-semibold"
                :tabindex="3"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                Masuk Dashboard
            </Button>

            <!-- Forgot password link: right-aligned -->
            <div v-if="canResetPassword" class="text-right">
                <Link
                    href="/satgas/forgot-password"
                    :tabindex="4"
                    class="text-base font-medium text-brand underline-offset-4 transition-colors hover:text-brand/80 hover:underline"
                >
                    Lupa kata sandi?
                </Link>
            </div>
        </form>
    </div>
</template>
