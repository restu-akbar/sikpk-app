<script setup lang="ts">
import { computed, watch } from 'vue';
import type { InertiaForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

interface ChangePasswordFormFields {
    current_password: string;
    password: string;
    password_confirmation: string;
    public_key: string;
    encrypted_private_key: string;
    emek_password: string;
    emek_password_salt: string;
    emek_recovery: string;
    emek_recovery_salt: string;
    [key: string]: unknown;
}

const props = withDefaults(
    defineProps<{
        form: InertiaForm<ChangePasswordFormFields>;
        onSubmit: () => void | Promise<void>;
        submitLabel?: string;
        processingLabel?: string;
    }>(),
    {
        submitLabel: 'Ubah Password',
        processingLabel: 'Mengubah...',
    },
);

const passwordMessage = computed(() => {
    if (!props.form.password) return '';

    const messages: string[] = [];

    if (props.form.password.length < 8) {
        messages.push('minimal 8 karakter');
    }

    const chars: string[] = [];

    if (!/[A-Z]/.test(props.form.password)) chars.push('huruf besar');
    if (!/[a-z]/.test(props.form.password)) chars.push('huruf kecil');
    if (!/\d/.test(props.form.password)) chars.push('angka');
    if (!/[^A-Za-z0-9]/.test(props.form.password)) chars.push('simbol');

    if (chars.length) messages.push(chars.join(', '));

    if (!messages.length) return '';

    return `Password harus mengandung ${messages.join(' dan ')}.`;
});

watch(
    () => [props.form.password, props.form.password_confirmation],
    () => {
        if (
            props.form.password_confirmation &&
            props.form.password !== props.form.password_confirmation
        ) {
            props.form.setError(
                'password_confirmation',
                'Konfirmasi password harus sama dengan password baru.',
            );
        } else {
            props.form.clearErrors('password_confirmation');
        }
    },
);

defineExpose({ passwordMessage });
</script>

<template>
    <form @submit.prevent="onSubmit" class="flex flex-col">

        <!-- Fields: gap-4 (16px) antar field -->
        <div class="flex flex-col gap-4">

            <!-- Kata sandi lama -->
            <div class="flex flex-col gap-2">
                <Label for="current_password" class="text-base font-medium">
                    Kata sandi lama
                </Label>
                <PasswordInput
                    id="current_password"
                    name="current_password"
                    v-model="form.current_password"
                    class="h-12 w-full text-base"
                    autocomplete="current-password"
                    placeholder="Kata sandi lama"
                />
                <InputError :message="form.errors.current_password" />
            </div>

            <!-- Kata sandi baru -->
            <div class="flex flex-col gap-2">
                <Label for="password" class="text-base font-medium">
                    Kata sandi baru
                </Label>
                <PasswordInput
                    id="password"
                    name="password"
                    v-model="form.password"
                    class="h-12 w-full text-base"
                    autocomplete="new-password"
                    placeholder="Kata sandi baru"
                />
                <InputError :message="passwordMessage || form.errors.password" />
            </div>

            <!-- Konfirmasi kata sandi -->
            <div class="flex flex-col gap-2">
                <Label for="password_confirmation" class="text-base font-medium">
                    Konfirmasi kata sandi
                </Label>
                <PasswordInput
                    id="password_confirmation"
                    name="password_confirmation"
                    v-model="form.password_confirmation"
                    class="h-12 w-full text-base"
                    autocomplete="new-password"
                    placeholder="Konfirmasi kata sandi baru"
                />
                <InputError :message="form.errors.password_confirmation" />
            </div>

        </div>

        <!-- Submit: mt-6 (24px) = sama dengan gap heading-ke-form di halaman -->
        <Button
            type="submit"
            variant="brand-accent"
            size="lg"
            class="mt-6 h-14 w-full font-display text-base font-semibold"
            :disabled="form.processing"
        >
            <Spinner v-if="form.processing" />
            {{ form.processing ? processingLabel : submitLabel }}
        </Button>

    </form>
</template>
