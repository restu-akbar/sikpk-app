<script setup lang="ts">
import { Head, Form } from '@inertiajs/vue3';
import { Eye, EyeOff } from 'lucide-vue-next';
import { ref } from 'vue';

import { update } from '@/actions/App/Http/Controllers/Auth/ChangePasswordController';

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);
</script>

<template>
    <Head title="Change Password" />

    <div class="flex min-h-screen items-center justify-center bg-muted/30 px-4">
        <div
            class="w-full max-w-md rounded-2xl border bg-background p-8 shadow-sm"
        >
            <div class="mb-6 space-y-2 text-center">
                <h1 class="text-2xl font-bold tracking-tight">
                    Change Your Password
                </h1>

                <p class="text-sm text-muted-foreground">
                    Untuk alasan keamanan, silakan ganti password sesuai
                    preferensi anda sebelum lanjut.
                </p>
            </div>

            <Form v-bind="update.form()" v-slot="form" class="space-y-5">
                <!-- Current Password -->
                <div class="space-y-2">
                    <label class="text-sm font-medium">
                        Current Password
                    </label>

                    <div class="relative">
                        <input
                            name="current_password"
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
                <div class="space-y-2">
                    <label class="text-sm font-medium"> New Password </label>

                    <div class="relative">
                        <input
                            name="password"
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
                            name="password_confirmation"
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
            </Form>
        </div>
    </div>
</template>
