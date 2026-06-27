<script setup lang="ts">
import FileUploadField from './form/FileUploadField.vue';
import FormSectionTitle from './form/FormSectionTitle.vue';

defineProps<{ modelValue: File[] }>();
defineEmits(['update:modelValue']);
</script>

<template>
    <section>
        <FormSectionTitle title="Lampiran Bukti Pendukung" />
        <FileUploadField
            :model-value="modelValue"
            @update:model-value="$emit('update:modelValue', $event)"
            label="Unggah bukti digital (opsional)"
            hint="Privasi dan keamanan data Anda menjadi prioritas..."
        />
    </section>
</template>
