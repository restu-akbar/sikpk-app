<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import ChangePasswordForm from '@/components/ChangePasswordForm.vue';
import { update } from '@/actions/App/Http/Controllers/Auth/ChangePasswordController';
import { edit } from '@/routes/settings/security';
import { setTemporaryError } from '@/lib/utils';
import { generateEncryption } from '@/lib/crypto';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Security settings',
                href: edit(),
            },
        ],
    },
});
const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',

    emek_password: '',
    emek_password_salt: '',
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
        const encryption = await generateEncryption({
            mode: 'change',
            oldPassword: form.current_password,
            newPassword: form.password,

            emek_password: page.props.auth.user.emek_password,
            emek_password_salt: page.props.auth.user.emek_password_salt,
        });

        form.emek_password = encryption.emek_password;
        form.emek_password_salt = encryption.emek_password_salt;

        form.submit('put', update['/settings/security'].url(), {
            preserveScroll: true,

            onSuccess: () => {
                form.reset();
            },

            onError: () => {
                form.reset(
                    'current_password',
                    'password',
                    'password_confirmation',
                );
            },
        });
    } catch (error) {
        console.error(error);
    }
};
</script>

<template>
    <Head title="Security settings" />
    <h1 class="sr-only">Security settings</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Update password"
            description="Ensure your account is using a long, random password to stay secure"
        />

        <ChangePasswordForm
            :form="form"
            :on-submit="submit"
            submit-label="Save password"
            processing-label="Saving..."
        />
    </div>
</template>
