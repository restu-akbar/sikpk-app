<script setup lang="ts">
import { computed, watch } from 'vue';

import FormSectionTitle from '../form/FormSectionTitle.vue';
import FormField from '../form/FormField.vue';
import DropdownField from '../form/DropdownField.vue';
import FieldLabel from '../form/FieldLabel.vue';
import ErrorField from '../form/ErrorField.vue';
import { statusCivitasOptions } from '@/constants/statusCivitasOptions';
import {
    jurusanList,
    prodiList,
    getProdiByJurusan,
} from '@/constants/jurusanProdi';
import {
    nomorIdentitasRules,
} from '@/constants/nomorIdentitasRules';

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
        disabled?: boolean;
        showNomorIdentitas?: boolean;
        showAngkatan?: boolean;
        showJenisKelaminForKorban?: boolean;
        civitasOptions?: { value: string; label: string }[];
    }>(),
    {
        isAdditional: false,
        isOptional: false,
        showWhatsapp: true,
        isGender: false,
        disabled: false,
        showNomorIdentitas: false,
        showAngkatan: false,
        showJenisKelaminForKorban: false,
        civitasOptions: () => statusCivitasOptions,
    },
);

const showJenisKelamin = computed(() => props.showJenisKelaminForKorban);

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

const showJurusan = computed(() =>
    ['mahasiswa', 'dosen'].includes(form.value.civitas),
);
const showProdi = computed(() => form.value.civitas === 'mahasiswa');
const showAngkatan = computed(
    () => props.showAngkatan && form.value.civitas === 'mahasiswa',
);

const filteredProdi = computed(() =>
    form.value.jurusan ? getProdiByJurusan(form.value.jurusan) : prodiList,
);

const defaultIdentitasRule = {
    label: 'Nomor Identitas',
    pattern: /.*/,
    maxLength: 32,
    placeholder: 'Pilih peran terlebih dahulu',
    hint: '',
};

const identitasRule = computed(
    () => nomorIdentitasRules[form.value.civitas] ?? defaultIdentitasRule,
);

const nomorIdentitasLabel = computed(
    () => `${identitasRule.value.label} ${props.subject}`,
);

watch(
    () => form.value.civitas,
    (val, oldVal) => {
        if (oldVal === undefined) return;

        if (!['mahasiswa', 'dosen'].includes(val)) {
            form.value.jurusan = '';
            form.value.prodi = '';
        } else if (val === 'dosen') {
            form.value.prodi = '';
        }

        if (val !== 'mahasiswa' && 'angkatan' in form.value) {
            form.value.angkatan = '';
        }

        if ('nomorIdentitas' in form.value) {
            form.value.nomorIdentitas = '';
        }
    },
);

watch(
    () => form.value.jurusan,
    (val, oldVal) => {
        if (oldVal === undefined) return;
        form.value.prodi = '';
    },
);

</script>

<template>
    <section>
        <FormSectionTitle :title="title" />

        <div
            class="mb-4 grid gap-4"
            :class="showJenisKelamin ? 'grid-cols-3' : 'grid-cols-2'"
        >
            <FormField
                name="nama"
                v-model="form.nama"
                :label="namaLabel"
                :error="props.stepErrors.nama"
                :required="required"
                :disabled="props.disabled"
            />

            <div>
                <FieldLabel :required="required">
                    {{ statusLabel }}
                </FieldLabel>

                <div
                    class="mt-2 flex h-10 items-center gap-6 rounded-md"
                    :class="{ 'opacity-60': props.disabled }"
                >
                    <template v-if="!props.isGender">
                        <label class="flex cursor-pointer items-center gap-2">
                            <input
                                v-model="form.status"
                                type="radio"
                                value="korban"
                                class="h-4 w-4"
                                :disabled="props.disabled"
                            />
                            <span class="text-sm">Korban</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-2">
                            <input
                                v-model="form.status"
                                type="radio"
                                value="saksi"
                                class="h-4 w-4"
                                :disabled="props.disabled"
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
                                :disabled="props.disabled"
                            />
                            <span class="text-sm">Laki-laki</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-2">
                            <input
                                v-model="form.status"
                                type="radio"
                                value="perempuan"
                                class="h-4 w-4"
                                :disabled="props.disabled"
                            />
                            <span class="text-sm">Perempuan</span>
                        </label>
                    </template>
                </div>
                <ErrorField :error="props.stepErrors.status" />
            </div>

            <div v-if="showJenisKelamin">
                <FieldLabel :required="required && form.status === 'korban'">
                    Jenis Kelamin {{ props.subject }}
                </FieldLabel>

                <div
                    class="mt-2 flex h-10 items-center gap-6 rounded-md"
                    :class="{ 'opacity-60': props.disabled }"
                >
                    <label class="flex cursor-pointer items-center gap-2">
                        <input
                            v-model="form.jenisKelamin"
                            type="radio"
                            value="laki-laki"
                            class="h-4 w-4"
                            :disabled="props.disabled"
                        />
                        <span class="text-sm">Laki-laki</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-2">
                        <input
                            v-model="form.jenisKelamin"
                            type="radio"
                            value="perempuan"
                            class="h-4 w-4"
                            :disabled="props.disabled"
                        />
                        <span class="text-sm">Perempuan</span>
                    </label>
                </div>
                <ErrorField :error="props.stepErrors.jenisKelamin" />
            </div>
        </div>

        <div
            class="mb-4 grid gap-4"
            :class="props.showNomorIdentitas ? 'grid-cols-3' : 'grid-cols-2'"
        >
            <div :class="{ 'col-span-2': !props.showWhatsapp && !props.showNomorIdentitas }">
                <DropdownField
                    name="civitas"
                    v-model="form.civitas"
                    :label="peranLabel"
                    placeholder="Pilih peran..."
                    :options="props.civitasOptions"
                    :error="props.stepErrors.civitas"
                    :required="required"
                    :disabled="props.disabled"
                />
            </div>

            <FormField
                v-if="props.showNomorIdentitas"
                name="nomorIdentitas"
                v-model="form.nomorIdentitas"
                :label="nomorIdentitasLabel"
                :placeholder="identitasRule.placeholder"
                :hint="identitasRule.hint"
                :pattern="identitasRule.pattern"
                :max-length="identitasRule.maxLength"
                :error="props.stepErrors.nomorIdentitas"
                :required="required"
                :disabled="props.disabled"
            />

            <FormField
                v-if="props.showWhatsapp"
                name="whatsapp"
                v-model="form.whatsapp"
                label="Nomor WhatsApp Aktif"
                :error="props.stepErrors.whatsapp"
                :required="required"
                :disabled="props.disabled"
            />
        </div>

        <div
            v-if="showJurusan || showProdi || showAngkatan"
            class="mb-4 grid gap-4"
            :class="showAngkatan ? 'grid-cols-3' : 'grid-cols-2'"
        >
            <DropdownField
                v-if="showJurusan"
                name="jurusan"
                v-model="form.jurusan"
                label="Jurusan"
                placeholder="Pilih jurusan..."
                :options="
                    jurusanList.map((j) => ({ label: j.name, value: j.name }))
                "
                :error="props.stepErrors.jurusan"
                :required="required"
                :disabled="props.disabled"
            />

            <DropdownField
                v-if="showProdi"
                name="prodi"
                v-model="form.prodi"
                label="Prodi"
                placeholder="Pilih program studi..."
                :options="
                    filteredProdi.map((p) => ({
                        label: `${p.degreeLevel} ${p.name}`,
                        value: `${p.degreeLevel} ${p.name}`,
                    }))
                "
                :error="props.stepErrors.prodi"
                :required="required"
                :disabled="props.disabled"
            />

            <FormField
                v-if="showAngkatan"
                name="angkatan"
                v-model="form.angkatan"
                label="Angkatan"
                :error="props.stepErrors.angkatan"
                :required="required"
                :disabled="props.disabled"
            />
        </div>

        <div v-if="props.isAdditional" class="grid grid-cols-2 gap-4">
            <FormField
                name="domisili"
                v-model="form.domisili"
                :label="domisiliLabel"
                :error="props.stepErrors.domisili"
                :required="required"
                :disabled="props.disabled"
            />

            <FormField
                name="kontakLain"
                v-model="form.kontakLain"
                label="Kontak Pihak Lain"
                :error="props.stepErrors.kontakLain"
                :required="required"
                :disabled="props.disabled"
            />
        </div>
    </section>
</template>
