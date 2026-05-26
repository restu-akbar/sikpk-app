<script setup lang="ts">
import { computed, watch } from 'vue';
import type { InertiaForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';

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
        submitLabel: 'Save password',
        processingLabel: 'Saving...',
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
    <form @submit.prevent="onSubmit" class="space-y-6">
        <!-- Current Password -->
        <div class="grid gap-2">
            <Label for="current_password">Current password</Label>
            <PasswordInput
                id="current_password"
                name="current_password"
                v-model="form.current_password"
                class="mt-1 block w-full"
                autocomplete="current-password"
                placeholder="Current password"
            />
            <InputError :message="form.errors.current_password" />
        </div>

        <!-- New Password -->
        <div class="grid gap-2">
            <Label for="password">New password</Label>
            <PasswordInput
                id="password"
                name="password"
                v-model="form.password"
                class="mt-1 block w-full"
                autocomplete="new-password"
                placeholder="New password"
            />
            <InputError :message="passwordMessage || form.errors.password" />
        </div>

        <!-- Confirm Password -->
        <div class="grid gap-2">
            <Label for="password_confirmation">Confirm password</Label>
            <PasswordInput
                id="password_confirmation"
                name="password_confirmation"
                v-model="form.password_confirmation"
                class="mt-1 block w-full"
                autocomplete="new-password"
                placeholder="Confirm password"
            />
            <InputError :message="form.errors.password_confirmation" />
        </div>

        <!-- Submit -->
        <div class="flex items-center gap-4">
            <Button :disabled="form.processing" type="submit">
                {{ form.processing ? processingLabel : submitLabel }}
            </Button>
        </div>
    </form>
</template>
