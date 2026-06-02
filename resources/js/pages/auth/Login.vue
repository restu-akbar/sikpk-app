<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { reactive, ref } from 'vue';

import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

import { generateDecryption } from '@/lib/crypto';
import { useCryptoStore } from '@/lib/crypto/store';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

const processing = ref(false);

const errors = reactive<{
    email?: string;
    password?: string;
}>({});

const form = reactive({
    email: '',
    password: '',
    remember: false,
});

async function submit() {
    const cryptoStore = useCryptoStore();
    processing.value = true;
    errors.email = undefined;
    errors.password = undefined;

    try {
        const response = await axios.post('/satgas/login', form);

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

        cryptoStore.activate(privateKey);
        router.visit('/satgas/dashboard');
    } catch (error: any) {
        if (error.response?.status === 422) {
            const raw = error.response.data.errors;
            Object.keys(raw).forEach((key) => {
                (errors as any)[key] = Array.isArray(raw[key])
                    ? raw[key][0]
                    : raw[key];
            });
        }
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
            <h2 class="font-display text-4xl font-bold tracking-tight text-foreground">
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
        <form @submit.prevent="submit" class="flex flex-col gap-6">

            <!-- Email field -->
            <div class="flex flex-col gap-2">
                <Label for="email" class="text-base font-medium">Email Polban</Label>
                <Input
                    v-model="form.email"
                    id="email"
                    type="email"
                    name="email"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="email"
                    placeholder="r.satgas@polban.ac.id"
                    class="h-12 text-base"
                />
                <InputError :message="errors.email" />
            </div>

            <!-- Password field -->
            <div class="flex flex-col gap-2">
                <Label for="password" class="text-base font-medium">Kata sandi</Label>
                <PasswordInput
                    v-model="form.password"
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="Kata sandi"
                    class="h-12 text-base"
                />
                <InputError :message="errors.password" />
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
                    class="text-base font-medium text-brand transition-colors underline-offset-4 hover:underline hover:text-brand/80"
                >
                    Lupa kata sandi?
                </Link>
            </div>

        </form>
    </div>
</template>
