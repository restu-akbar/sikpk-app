<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { reactive, ref } from 'vue';
import { Eye, EyeOff } from 'lucide-vue-next';

import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';

import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

import { generateDecryption } from '@/lib/crypto';
import { useCryptoStore } from '@/lib/crypto/store';

defineOptions({
    layout: {
        title: 'Log in to your account',
        description: 'Enter your email and password below to log in',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

const showPassword = ref(false);

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
            window.location.href = '/getting-started';
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

        router.visit('/dashboard');
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
    <Head title="Log in" />

    <div
        v-if="status"
        class="mb-4 text-center text-sm font-medium text-green-600"
    >
        {{ status }}
    </div>

    <form @submit.prevent="submit" class="flex flex-col gap-6">
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="email">Email address</Label>
                <Input
                    v-model="form.email"
                    id="email"
                    type="email"
                    name="email"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="email"
                    placeholder="email@example.com"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password">Password</Label>

                    <TextLink v-if="canResetPassword" href="/forgot-password">
                        Forgot password?
                    </TextLink>
                </div>

                <div class="relative">
                    <PasswordInput
                        v-model="form.password"
                        id="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Password"
                        :type="showPassword ? 'text' : 'password'"
                    />

                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-muted-foreground"
                    >
                        <Eye v-if="!showPassword" class="h-4 w-4" />

                        <EyeOff v-else class="h-4 w-4" />
                    </button>
                </div>

                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <Label for="remember" class="flex items-center space-x-3">
                    <Checkbox
                        id="remember"
                        :checked="form.remember"
                        @update:checked="form.remember = !!$event"
                    />
                    <span>Remember me</span>
                </Label>
            </div>

            <Button
                type="submit"
                class="mt-4 w-full"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                Log in
            </Button>
        </div>
    </form>
</template>
