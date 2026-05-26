<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';

import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const page = usePage();

const user = computed(() => page.props.auth.user);
</script>

<template>
    <Head title="Secure Profile Edit" />

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Edit Profil"
            description="Perbarui nama dan email Anda."
        />

        <Form
            v-bind="ProfileController.update.form()"
            class="space-y-6"
            v-slot="{ processing, errors, recentlySuccessful }"
        >
            <div class="grid gap-2">
                <Label for="name"> Nama </Label>

                <Input
                    id="name"
                    name="name"
                    type="text"
                    :default-value="user.name"
                    autocomplete="name"
                    required
                    placeholder="Nama lengkap"
                />

                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email"> Email </Label>

                <Input
                    id="email"
                    name="email"
                    type="email"
                    :default-value="user.email"
                    autocomplete="email"
                    required
                    placeholder="Alamat email"
                />

                <InputError :message="errors.email" />
            </div>

            <div class="flex items-center gap-4">
                <Button type="submit" :disabled="processing"> Simpan </Button>

                <p v-if="recentlySuccessful" class="text-sm text-green-600">
                    Profil berhasil diperbarui.
                </p>
            </div>
        </Form>
    </div>
</template>
