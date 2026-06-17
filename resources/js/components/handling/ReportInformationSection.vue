<script setup lang="ts">
import FormSectionTitle from '../form/FormSectionTitle.vue';
import FieldReadonly from '../form/FieldReadonly.vue';
import DropdownField from '../form/DropdownField.vue';

const props = withDefaults(
    defineProps<{
        caseNumber: string;
        reportDate: string;
        clarificationDate: string;
        error?: string;
        jenisKekerasanOptions: Array<any>;
        showJenisKekerasan?: boolean;
    }>(),
    {
        showJenisKekerasan: true,
    },
);

const jenisKekerasan = defineModel<string>('jenisKekerasan');
</script>

<template>
    <section>
        <FormSectionTitle title="1. INFORMASI LAPORAN" />

        <div class="mb-4 grid grid-cols-3 gap-4">
            <FieldReadonly label="No. Laporan" :value="caseNumber" />

            <FieldReadonly label="Tanggal Melapor" :value="reportDate" />

            <FieldReadonly
                label="Tanggal Klarifikasi (Hari Ini)"
                :value="clarificationDate"
            />
        </div>

        <DropdownField
            v-if="showJenisKekerasan"
            name="jenisKekerasan"
            v-model="jenisKekerasan"
            label="Jenis dugaan kekerasan"
            placeholder="Pilih jenis dugaan kekerasan..."
            :options="jenisKekerasanOptions"
            required
            :error="error"
        />
    </section>
</template>
