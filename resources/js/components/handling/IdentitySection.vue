<script setup lang="ts">
import { computed } from 'vue';

import FormSectionTitle from '../form/FormSectionTitle.vue';
import FormField from '../form/FormField.vue';
import FieldLabel from '../form/FieldLabel.vue';
import ErrorField from '../form/ErrorField.vue';

const form = defineModel<any>('form', {
    required: true,
});

const props = withDefaults(
    defineProps<{
        step: number;
        subject: string;
        stepErrors: Record<string, string>;
        isAdditional?: boolean;
        isOptional?: boolean;
        showWhatsapp?: boolean;
        isGender?: boolean;
    }>(),
    {
        isAdditional: false,
        isOptional: false,
        showWhatsapp: true,
        isGender: false,
    },
);

const title = computed(
    () =>
        `${props.step}. Identitas ${props.subject}${
            props.isOptional ? ' (Opsional)' : ''
        }`,
);

const required = computed(() => !props.isOptional);

const namaLabel = computed(() => `Nama lengkap ${props.subject}`);

const statusLabel = computed(() =>
    props.isGender
        ? `Jenis Kelamin ${props.subject}`
        : `Status ${props.subject}`,
);

const peranLabel = computed(() => `Peran ${props.subject}`);

const domisiliLabel = computed(() => `Domisili ${props.subject}`);
</script>

<template>
    <section>
        <FormSectionTitle :title="title" />

        <div class="mb-4 grid grid-cols-2 gap-4">
            <FormField
                name="nama"
                v-model="form.nama"
                :label="namaLabel"
                :error="props.stepErrors.nama"
                :required="required"
            />

            <div>
                <FieldLabel :required="required">
                    {{ statusLabel }}
                </FieldLabel>

                <div class="mt-2 flex h-10 items-center gap-6 rounded-md">
                    <template v-if="!props.isGender">
                        <label class="flex cursor-pointer items-center gap-2">
                            <input
                                v-model="form.status"
                                type="radio"
                                value="korban"
                                class="h-4 w-4"
                            />
                            <span class="text-sm">Korban</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-2">
                            <input
                                v-model="form.status"
                                type="radio"
                                value="saksi"
                                class="h-4 w-4"
                            />
                            <span class="text-sm">Saksi</span>
                        </label>
                    </template>

                    <template v-else>
                        <label class="flex cursor-pointer items-center gap-2">
                            <input
                                v-model="form.status"
                                type="radio"
                                value="laki-laki"
                                class="h-4 w-4"
                            />
                            <span class="text-sm">Laki-laki</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-2">
                            <input
                                v-model="form.status"
                                type="radio"
                                value="perempuan"
                                class="h-4 w-4"
                            />
                            <span class="text-sm">Perempuan</span>
                        </label>
                    </template>
                </div>
                <ErrorField :error="props.stepErrors.status" />
            </div>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-4">
            <div :class="{ 'col-span-2': !props.showWhatsapp }">
                <FormField
                    name="civitas"
                    v-model="form.civitas"
                    :label="peranLabel"
                    :error="props.stepErrors.civitas"
                    :required="required"
                />
            </div>

            <FormField
                v-if="props.showWhatsapp"
                name="whatsapp"
                v-model="form.whatsapp"
                label="Nomor WhatsApp Aktif"
                :error="props.stepErrors.whatsapp"
                :required="required"
            />
        </div>

        <div class="mb-4 grid grid-cols-2 gap-4">
            <FormField
                name="jurusan"
                v-model="form.jurusan"
                label="Jurusan"
                :error="props.stepErrors.jurusan"
                :required="required"
            />

            <FormField
                name="prodi"
                v-model="form.prodi"
                label="Prodi"
                :error="props.stepErrors.prodi"
                :required="required"
            />
        </div>

        <div v-if="props.isAdditional" class="grid grid-cols-2 gap-4">
            <FormField
                name="domisili"
                v-model="form.domisili"
                :label="domisiliLabel"
                :error="props.stepErrors.domisili"
                :required="required"
            />

            <FormField
                name="kontakLain"
                v-model="form.kontakLain"
                label="Kontak Pihak Lain"
                :error="props.stepErrors.kontakLain"
                :required="required"
            />
        </div>
    </section>
</template>
