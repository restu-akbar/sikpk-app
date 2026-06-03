<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import type { ReportForm } from '@/types';
import { store } from '@/routes/reports';

import Agreement from '@/components/Agreement.vue';
import FormCardSection from '@/components/form/FormCardSection.vue';
import FormField from '@/components/form/FormField.vue';
import ButtonGroup from '@/components/form/ButtonGroup.vue';
import FormSectionTitle from '@/components/form/FormSectionTitle.vue';
import DropdownField from '@/components/form/DropdownField.vue';
import TextareaField from '@/components/form/TextareaField.vue';
import FileUploadField from '@/components/form/FileUploadField.vue';

import { disabilityOptions } from '@/constants/disability';
import {
    statusCivitasOptions,
    statusOptions,
} from '@/constants/statusCivitasOptions';
import { jenisKekerasanOptions } from '@/constants/jenisKekerasanOptions';
import { phonePattern } from '@/constants/phonePattern';

import { getPublicKeys } from '@/lib/crypto/getPublicKeys';
import { encryptFile } from '@/lib/mediaCrypto';

const selectedMethod = ref<'form' | 'voice'>('form');
const currentStep = ref(1);

const steps = [
    {
        title: 'Data Pelapor',
        desc: 'Identitas dan status civitas',
    },
    {
        title: 'Detail Kejadian',
        desc: 'Kronologi, terlapor, dan lokasi',
    },
    {
        title: 'Konfirmasi & Kirim',
        desc: 'Tinjau ulang dan kirim laporan',
    },
];

const stepErrors = ref<Record<string, string>>({});

const form = useForm<ReportForm>({
    nama: '',
    whatsapp: '',

    statusPelapor: '',
    statusCivitas: '',

    namaTerlapor: '',
    statusTerlapor: '',

    jenisKekerasan: '',
    tempatKejadian: '',
    waktuKejadian: '',

    kronologi: '',

    disabilitas: ['tidak_ada'],

    bukti: [],

    agreed: false,
});

const scrollToField = (field: string) => {
    const element =
        document.querySelector(`[name="${field}"]`) ??
        document.querySelector(`[data-field="${field}"]`);

    element?.scrollIntoView({
        behavior: 'smooth',
        block: 'center',
    });
};

const stepValidators = {
    1: [
        ['nama', 'Nama wajib diisi'],
        ['whatsapp', 'No WhatsApp wajib diisi'],
        ['statusPelapor', 'Status pelapor wajib dipilih'],
        ['statusCivitas', 'Status civitas akademik wajib dipilih'],
    ],

    2: [
        ['namaTerlapor', 'Nama terlapor wajib diisi'],
        ['statusTerlapor', 'Status terlapor wajib dipilih'],
        ['jenisKekerasan', 'Jenis kekerasan wajib dipilih'],
        ['tempatKejadian', 'Tempat kejadian wajib diisi'],
        ['waktuKejadian', 'Waktu kejadian wajib diisi'],
        ['kronologi', 'Kronologi kejadian wajib diisi'],
    ],
    3: [['agreed', 'Pernyataan persetujuan harus diceklis']],
} as const;

const validateStep = (step: 1 | 2 | 3) => {
    const newErrors = { ...stepErrors.value };
    let isValid = true;

    for (const [field, message] of stepValidators[step]) {
        const value = form[field];

        const isEmpty =
            value === null ||
            value === undefined ||
            value === '' ||
            value === false;

        if (isEmpty) {
            newErrors[field] = message;
            scrollToField(field);
            isValid = false;
            break;
        }
        if (field === 'whatsapp' && value) {
            const regex =
                phonePattern instanceof RegExp
                    ? phonePattern
                    : new RegExp(phonePattern);

            if (!regex.test(value)) {
                newErrors[field] = 'Format nomor WhatsApp tidak valid';
                scrollToField(field);
                isValid = false;
                break;
            }
        }
    }

    stepErrors.value = newErrors;

    return isValid;
};

const nextStep = () => {
    if (currentStep.value === 1 && !validateStep(1)) return;
    if (currentStep.value === 2 && !validateStep(2)) return;
    if (currentStep.value === 3 && !validateStep(3)) return;

    stepErrors.value = {};
    currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const watchedFields = [
    'nama',
    'whatsapp',
    'statusPelapor',
    'statusCivitas',
    'namaTerlapor',
    'statusTerlapor',
    'jenisKekerasan',
    'tempatKejadian',
    'waktuKejadian',
    'kronologi',
    'agreed',
] as const;

watchedFields.forEach((field) => {
    watch(
        () => form[field],
        (newValue) => {
            if (
                newValue !== '' &&
                newValue !== false &&
                newValue !== null &&
                newValue !== undefined
            ) {
                const newErrors = { ...stepErrors.value };
                delete newErrors[field];
                stepErrors.value = newErrors;
            }
        },
    );
});

const toggleDisability = (value: Disabilitas) => {
    if (value === 'tidak_ada') {
        form.disabilitas = ['tidak_ada'];
        return;
    }

    const idx = form.disabilitas.indexOf(value);

    const tidakAdaIdx = form.disabilitas.indexOf('tidak_ada');

    if (tidakAdaIdx !== -1) {
        form.disabilitas.splice(tidakAdaIdx, 1);
    }

    if (idx !== -1) {
        form.disabilitas.splice(idx, 1);

        if (form.disabilitas.length === 0) {
            form.disabilitas = ['tidak_ada'];
        }
    } else {
        form.disabilitas.push(value);
    }
};

const display = (value?: string) => value || '-';

const summaryItems = computed(() => [
    {
        label: 'Status Pelapor',
        value:
            statusOptions.find((item) => item.value === form.statusPelapor)
                ?.label ?? '-',
    },
    {
        label: 'Pelapor Sebagai',
        value: display(form.statusCivitas),
    },
    {
        label: 'Jenis Dugaan',
        value: display(form.jenisKekerasan),
    },
    {
        label: 'Tempat Kejadian',
        value: display(form.tempatKejadian),
    },
    {
        label: 'Waktu Kejadian',
        value: display(form.waktuKejadian),
    },
    {
        label: 'Terlapor',
        value: display(form.namaTerlapor),
    },
]);

const handleSubmit = async () => {
    stepErrors.value = {};

    if (!validateStep(3)) {
        return;
    }

    try {
        const publicKeys = await getPublicKeys();

        const encryptedFiles = await Promise.all(
            form.bukti.map((file) => {
                return encryptFile(file, publicKeys);
            }),
        );

        const formData = new FormData();

        formData.append('nama', form.nama);
        formData.append('whatsapp', form.whatsapp);
        formData.append('statusPelapor', form.statusPelapor);
        formData.append('statusCivitas', form.statusCivitas);
        formData.append('namaTerlapor', form.namaTerlapor);
        formData.append('statusTerlapor', form.statusTerlapor);
        formData.append('jenisKekerasan', form.jenisKekerasan);
        formData.append('tempatKejadian', form.tempatKejadian);
        formData.append('waktuKejadian', form.waktuKejadian);
        formData.append('kronologi', form.kronologi);
        formData.append('agreed', form.agreed ? '1' : '0');
        form.disabilitas.forEach((d) => formData.append('disabilitas[]', d));

        encryptedFiles.forEach((item, index) => {
            formData.append(`bukti[${index}][file]`, item.encryptedData);
            formData.append(`bukti[${index}][filename]`, item.filename);
            formData.append(`bukti[${index}][mime_type]`, item.mimeType);
            formData.append(`bukti[${index}][size]`, item.size.toString());
            formData.append(
                `bukti[${index}][edeks]`,
                JSON.stringify(item.edeks),
            );
        });

        router.post(store().url, formData, {
            onSuccess: () => {
                form.reset();
                currentStep.value = 1;
                stepErrors.value = {};
                selectedMethod.value = 'form';
            },
            onError: () => {},
        });
    } catch (error) {
        console.error(error);
    }
};
</script>

<template>
    <div
        class="flex min-h-screen flex-col overflow-y-auto"
        style="font-family: 'Plus Jakarta Sans', sans-serif"
    >
        <!-- Main Content -->
        <main class="mx-auto w-full max-w-4xl flex-1 px-4 py-10">
            <!-- Page Header -->
            <div class="mb-10 text-center">
                <h1 class="mb-3 text-3xl font-bold">Buat Laporan</h1>
                <p class="mx-auto max-w-md text-sm leading-relaxed">
                    Pilih metode pelaporan yang paling nyaman. Identitas Anda
                    dilindungi dan hanya dapat diakses oleh Satgas PPK.
                </p>
            </div>

            <MethodSelector v-model="selectedMethod" :options="methods" />

            <div class="my-6 rounded-2xl border border-gray-200 p-6 shadow-sm">
                <!-- STEP INDICATOR -->
                <div class="border-b border-gray-100 p-6">
                    <div
                        class="mx-auto flex max-w-lg items-center justify-between"
                    >
                        <template v-for="(step, index) in steps" :key="index">
                            <div class="flex flex-col items-center gap-1.5">
                                <!-- Circle -->
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold transition-all"
                                    :class="[
                                        currentStep > index + 1
                                            ? 'bg-[#1A5BA6] text-white'
                                            : currentStep === index + 1
                                              ? 'border-2 border-[#1A5BA6] bg-[#1A5BA6] text-white'
                                              : 'border-2 border-gray-300 text-gray-400',
                                    ]"
                                >
                                    <svg
                                        v-if="currentStep > index + 1"
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2.5"
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>
                                    <span v-else>{{ index + 1 }}</span>
                                </div>

                                <span
                                    class="text-center text-xs font-semibold"
                                    :class="
                                        currentStep >= index + 1
                                            ? 'text-[#2563EB]'
                                            : 'text-gray-500'
                                    "
                                >
                                    {{ step.title }}
                                </span>

                                <span
                                    class="max-w-[120px] text-center text-[10px] text-gray-400"
                                >
                                    {{ step.desc }}
                                </span>
                            </div>

                            <div
                                v-if="index < steps.length - 1"
                                class="mx-3 mb-5 h-0.5 flex-1"
                                :class="
                                    currentStep > index + 1
                                        ? 'bg-[#2563EB]'
                                        : 'bg-gray-200'
                                "
                            />
                        </template>
                    </div>
                </div>

                <!-- Form Card -->
                <form @submit.prevent="handleSubmit">
                    <div class="p-8">
                        <!-- STEP 1 -->
                        <div v-if="currentStep === 1">
                            <!-- Section: Identitas Pelapor -->
                            <FormCardSection>
                                <FormSectionTitle title="Identitas Anda" />
                                <div
                                    class="grid grid-cols-1 gap-5 md:grid-cols-2"
                                >
                                    <FormField
                                        name="nama"
                                        v-model="form.nama"
                                        label="Nama lengkap pelapor"
                                        required
                                        :error="stepErrors.nama"
                                        placeholder="Mis. Annisa Putri"
                                    />

                                    <FormField
                                        name="whatsapp"
                                        v-model="form.whatsapp"
                                        label="Nomor WhatsApp aktif"
                                        required
                                        :error="stepErrors.whatsapp"
                                        placeholder="Mis. 0812-3456-7890"
                                        :pattern="phonePattern"
                                        hint="Format: 08xxxxxxxxxx, 10-13 panjang angka"
                                    />
                                </div>

                                <div class="mt-5 flex flex-col gap-2">
                                    <ButtonGroup
                                        name="statusPelapor"
                                        label="Status Pelapor"
                                        v-model="form.statusPelapor"
                                        :options="statusOptions"
                                        required
                                        :error="stepErrors.statusPelapor"
                                    />
                                </div>
                            </FormCardSection>

                            <!-- Section: Status Pelapor -->
                            <FormCardSection>
                                <FormSectionTitle
                                    title="Pelapor sebagai (Civitas Akademika)"
                                />
                                <DropdownField
                                    name="statusCivitas"
                                    v-model="form.statusCivitas"
                                    label="Anda saat ini berstatus sebagai"
                                    placeholder="Pilih status civitas akademika..."
                                    :options="statusCivitasOptions"
                                    :error="stepErrors.statusCivitas"
                                    required
                                />
                            </FormCardSection>

                            <!-- Section: Kebutuhan Khusus -->
                            <FormCardSection>
                                <FormSectionTitle title="Kebutuhan Khusus" />
                                <div>
                                    <FieldLabel :required="required">
                                        {{ label }}
                                    </FieldLabel>
                                    <div class="flex flex-wrap gap-2.5">
                                        <button
                                            type="button"
                                            v-for="opt in disabilityOptions"
                                            :key="opt.value"
                                            @click="toggleDisability(opt.value)"
                                            :class="[
                                                'rounded-lg border-2 px-4 py-2 text-sm font-medium transition-all',
                                                form.disabilitas.includes(
                                                    opt.value,
                                                )
                                                    ? 'border-[#2563EB] bg-blue-50 text-[#2563EB]'
                                                    : 'border-gray-300 text-gray-600 hover:border-gray-400',
                                            ]"
                                        >
                                            {{ opt.label }}
                                        </button>
                                    </div>
                                    <ErrorField
                                        :error="form.errors.disabilitas"
                                    />
                                    <p class="mt-3 text-[11px] text-gray-400">
                                        Informasi ini membantu Satgas
                                        menyediakan akomodasi yang sesuai pada
                                        tahap klarifikasi.
                                    </p>
                                </div>
                            </FormCardSection>
                        </div>

                        <!-- STEP 2 -->
                        <div v-if="currentStep === 2">
                            <h2 class="mb-1 text-xl font-bold">
                                Detail Kejadian
                            </h2>
                            <p class="mb-8 text-sm text-gray-500">
                                Sampaikan apa yang terjadi seakurat mungkin.
                                Hindari mencantumkan data pribadi pihak lain
                                jika belum diperlukan.
                            </p>

                            <FormCardSection>
                                <FormSectionTitle title="Identitas Terlapor" />
                                <div
                                    class="grid grid-cols-1 gap-5 md:grid-cols-2"
                                >
                                    <FormField
                                        name="namaTerlapor"
                                        v-model="form.namaTerlapor"
                                        label="Nama dugaan terlapor"
                                        required
                                        :error="stepErrors.namaTerlapor"
                                        placeholder="Nama / inisial pihak terlapor"
                                        hint="Boleh inisial bila Anda belum siap menyebut nama lengkap."
                                    />
                                    <DropdownField
                                        name="statusTerlapor"
                                        v-model="form.statusTerlapor"
                                        label="Terlapor sebagai"
                                        placeholder="Pilih status terlapor..."
                                        :options="statusCivitasOptions"
                                        :error="stepErrors.statusTerlapor"
                                        required
                                    />
                                </div>
                            </FormCardSection>

                            <!-- Section: Jenis & Tempat Kejadian -->
                            <FormCardSection>
                                <FormSectionTitle
                                    title="Jenis & Tempat Kejadian"
                                />

                                <div class="mb-5">
                                    <DropdownField
                                        name="jenisKekerasan"
                                        v-model="form.jenisKekerasan"
                                        label="Jenis dugaan kekerasan"
                                        placeholder="Pilih jenis dugaan kekerasan..."
                                        :options="jenisKekerasanOptions"
                                        :error="stepErrors.jenisKekerasan"
                                        required
                                    />
                                </div>

                                <div
                                    class="grid grid-cols-1 gap-5 md:grid-cols-2"
                                >
                                    <FormField
                                        name="tempatKejadian"
                                        v-model="form.tempatKejadian"
                                        label="Tempat kejadian"
                                        required
                                        :error="stepErrors.tempatKejadian"
                                        placeholder="Mis. Gedung Elektro, R. 304"
                                    />
                                    <FormField
                                        name="waktuKejadian"
                                        v-model="form.waktuKejadian"
                                        type="datetime-local"
                                        label="Waktu kejadian"
                                        required
                                        :error="stepErrors.waktuKejadian"
                                    />
                                </div>
                            </FormCardSection>

                            <!-- Section: Kronologi -->
                            <FormCardSection>
                                <FormSectionTitle title="Kronologi" />

                                <TextareaField
                                    name="kronologi"
                                    v-model="form.kronologi"
                                    label="Kronologi kejadian"
                                    placeholder="Jelaskan apa yang terjadi: latar belakang, kronologi, pihak yang terlibat, dan dampak yang Anda rasakan."
                                    rows="6"
                                    required
                                    hint="Tulis sejelas mungkin."
                                    :error="stepErrors.kronologi"
                                />
                            </FormCardSection>

                            <!-- Section: Bukti -->
                            <FormCardSection>
                                <FormSectionTitle title="Bukti Pendukung" />
                                <FileUploadField
                                    v-model="form.bukti"
                                    label="Unggah bukti digital berupa gambar, dokumen, video, atau audio untuk mendukung laporan yang Anda sampaikan. (opsional)"
                                    hint="Privasi dan keamanan data Anda menjadi prioritas. Seluruh bukti digital disimpan secara terenkripsi dan dikelola secara terbatas sesuai kebutuhan penanganan kasus."
                                />
                            </FormCardSection>
                        </div>

                        <!-- STEP 3 -->
                        <div v-if="currentStep === 3">
                            <div class="flex flex-col gap-2">
                                <!-- Header -->
                                <div>
                                    <h1
                                        class="text-2xl font-bold text-gray-900"
                                    >
                                        Konfirmasi & Kirim Laporan
                                    </h1>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Periksa kembali ringkasan laporan Anda
                                        sebelum dikirim ke Satgas PPK .
                                    </p>
                                </div>

                                <!-- Ringkasan Laporan -->
                                <FormCardSection>
                                    <div class="flex flex-col gap-6">
                                        <span
                                            class="text-xs font-semibold tracking-widest text-gray-400 uppercase"
                                        >
                                            Ringkasan Laporan
                                        </span>

                                        <div
                                            class="grid grid-cols-1 sm:grid-cols-2"
                                        >
                                            <div
                                                v-for="(
                                                    item, index
                                                ) in summaryItems"
                                                :key="item.label"
                                                :class="[
                                                    'py-4',
                                                    index <
                                                    summaryItems.length - 2
                                                        ? 'border-b border-dashed border-gray-200'
                                                        : '',
                                                    index % 2 === 1
                                                        ? 'sm:pl-6'
                                                        : '',
                                                ]"
                                            >
                                                <p
                                                    class="mb-1 text-xs font-semibold tracking-widest text-gray-400 uppercase"
                                                >
                                                    {{ item.label }}
                                                </p>

                                                <p
                                                    class="text-sm text-gray-800"
                                                >
                                                    {{ item.value }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </FormCardSection>

                                <!-- Pernyataan -->
                                <Agreement
                                    v-model="form.agreed"
                                    title="Saya menyatakan bahwa seluruh informasi yang saya sampaikan dalam laporan ini adalah benar sesuai pengetahuan saya dan disampaikan dengan itikad baik. "
                                    description="Saya memahami konsekuensi atas penyampaian informasi yang tidak benar atau sengaja menyesatkan."
                                    :error="stepErrors.agreed"
                                />
                            </div>
                        </div>
                        <div
                            class="mt-4 flex items-center justify-between border-t border-[#ECE8E2] bg-[#FDFCFB] p-8 px-8 py-5 dark:border-gray-800 dark:bg-gray-950"
                        >
                            <button
                                type="button"
                                @click="prevStep"
                                :disabled="currentStep === 1"
                                class="flex items-center gap-2 rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium transition-all hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 19l-7-7 7-7"
                                    />
                                </svg>

                                Kembali
                            </button>

                            <button
                                v-if="currentStep < 3"
                                type="button"
                                @click="nextStep"
                                class="flex items-center gap-2 rounded-lg bg-[#F97316] px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-orange-600"
                            >
                                Lanjut
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5l7 7-7 7"
                                    />
                                </svg>
                            </button>

                            <button
                                v-else
                                type="submit"
                                class="rounded-lg bg-orange-500 px-6 py-2.5 text-sm font-semibold text-white hover:bg-orange-600"
                            >
                                Kirim Laporan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
</template>
