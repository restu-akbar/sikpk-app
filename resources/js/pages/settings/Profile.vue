<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { requestEdit } from '@/actions/App/Http/Controllers/Settings/ProfileController';
</script>

<template>
    <Head title="Profile settings" />

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Profile information"
            description="Untuk keamanan, link edit profil akan dikirim ke email Anda."
        />

        <Form
            :action="requestEdit().url"
            :method="requestEdit().method"
            v-slot="{ processing }"
        >
            <Button :disabled="processing"> Kirim Link Edit Profil </Button>
        </Form>
    </div>
</template>
