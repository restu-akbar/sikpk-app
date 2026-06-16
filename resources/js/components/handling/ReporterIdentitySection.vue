<script setup lang="ts">
import FormSectionTitle from '../form/FormSectionTitle.vue';
import FormField from '../form/FormField.vue';
import FieldLabel from '../form/FieldLabel.vue';
import ErrorField from '../form/ErrorField.vue';

const form = defineModel<any>('form', {
    required: true,
});

defineProps<{
    stepErrors: Record<string, string>;
}>();
</script>

<template>
    <section>
        <FormSectionTitle title="2. IDENTITAS PELAPOR" />

        <div class="mb-4 grid grid-cols-2 gap-4">
            <FormField
                name="nama"
                v-model="form.nama"
                label="Nama lengkap pelapor"
                :error="stepErrors.nama"
                required
            />

            <div>
                <FieldLabel required> Status Pelapor </FieldLabel>

                <div class="mt-2 flex h-10 items-center gap-6 rounded-md">
                    <label class="flex cursor-pointer items-center gap-2">
                        <input
                            v-model="form.statusPelapor"
                            type="radio"
                            value="korban"
                            class="h-4 w-4"
                        />
                        <span class="text-sm">Korban</span>
                    </label>

                    <label class="flex cursor-pointer items-center gap-2">
                        <input
                            v-model="form.statusPelapor"
                            type="radio"
                            value="saksi"
                            class="h-4 w-4"
                        />
                        <span class="text-sm">Saksi</span>
                    </label>
                </div>

                <ErrorField :error="stepErrors.statusPelapor" />
            </div>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-4">
            <FormField
                name="civitas"
                v-model="form.statusCivitas"
                label="Peran Pelapor"
                :error="stepErrors.statusCivitas"
                required
            />

            <FormField
                name="whatsapp"
                v-model="form.whatsapp"
                label="Nomor WhatsApp Aktif"
                :error="stepErrors.whatsapp"
                required
            />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <FormField
                name="jurusan"
                v-model="form.jurusan"
                label="Jurusan"
                :error="stepErrors.jurusan"
                required
            />

            <FormField
                name="prodi"
                v-model="form.prodi"
                label="Prodi"
                :error="stepErrors.prodi"
                required
            />
        </div>
    </section>
</template>
