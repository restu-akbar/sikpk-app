<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3';
import { computed, provide, ref, watch } from 'vue';
import { ReportForm } from '@/types/reports';
import { useStep } from '@/composables/useStep';
import { store } from '@/routes/reports';
import Agreement from '@/components/Agreement.vue';
import MethodSelector from '@/components/MethodSelector.vue';
import FormCardSection from '@/components/form/FormCardSection.vue';
import FormField from '@/components/form/FormField.vue';
import ButtonGroup from '@/components/form/ButtonGroup.vue';
import FormSectionTitle from '@/components/form/FormSectionTitle.vue';
import DropdownField from '@/components/form/DropdownField.vue';
import TextareaField from '@/components/form/TextareaField.vue';
import FileUploadField from '@/components/form/FileUploadField.vue';
import VoiceRecorder from '@/components/form/VoiceRecorder.vue';
import type { AudioRecording } from '@/components/form/VoiceRecorder.vue';

import { disabilityOptions } from '@/constants/disability';
import {
    statusCivitasOptions,
    statusTerlaporrOptions,
    statusOptions,
} from '@/constants/statusCivitasOptions';
import { jenisKekerasanOptions } from '@/constants/jenisKekerasanOptions';
import { phonePattern } from '@/constants/phonePattern';
import { methods } from '@/constants/methods';
import {
    jurusanList,
    prodiList,
    getProdiByJurusan,
} from '@/constants/jurusanProdi';

import { getPublicKeys } from '@/lib/crypto/getPublicKeys';
import { encryptFile } from '@/lib/mediaCrypto';

provide('formTheme', 'blue');

const selectedMethod = ref<'form' | 'audio'>('form');
const audioRecordings = ref<AudioRecording[]>([]);

const props = defineProps<{
    isFirstReport: boolean;
    reporterData?: {
        nama: string;
        whatsapp: string;
        statusCivitas: string;
        jurusan: string;
        prodi: string;
        disabilitas: string[];
    } | null;
}>();

const stepsData = [
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

// Menggunakan composable baru untuk management state steps
const { currentStep, steps, nextStep, prevStep } = useStep(stepsData, 3);

const nextButtonLabel = computed(() => {
    const labels: Record<number, string> = {
        1: 'Lanjut ke Detail Kejadian',
        2: 'Lanjut ke Konfirmasi',
    };
    return labels[currentStep.value] ?? 'Lanjut';
});

const stepErrors = ref<Record<string, string>>({});

const form = useForm<ReportForm>({
    nama: props.reporterData?.nama ?? '',
    whatsapp: props.reporterData?.whatsapp ?? '',
    jurusan: props.reporterData?.jurusan ?? '',
    prodi: props.reporterData?.prodi ?? '',

    statusPelapor: '',
    statusCivitas: props.reporterData?.statusCivitas ?? '',

    namaTerlapor: '',
    statusTerlapor: 'tidak_diketahui',

    jenisKekerasan: '',
    tempatKejadian: '',
    waktuKejadian: '',

    kronologi: '',

    disabilitas: props.reporterData?.disabilitas ?? ['tidak_ada'],

    bukti: [],

    agreed: false,
});

const filteredProdi = computed(() =>
    form.jurusan ? getProdiByJurusan(form.jurusan) : prodiList,
);

const scrollToField = (field: string) => {
    const element =
        document.querySelector(`[name="${field}"]`) ??
        document.querySelector(`[data-field="${field}"]`);

    element?.scrollIntoView({
        behavior: 'smooth',
        block: 'center',
    });
};

const isFirstReport = computed(() => props.isFirstReport);

const step1Fields = computed(() => {
    const fields: [string, string][] = [];
    if (isFirstReport.value) {
        fields.push(['nama', 'Nama wajib diisi']);
    }
    fields.push(['whatsapp', 'No WhatsApp wajib diisi']);
    if (isFirstReport.value) {
        fields.push(['jurusan', 'Jurusan wajib dipilih']);
        fields.push(['prodi', 'Program studi wajib dipilih']);
    }
    fields.push(['statusPelapor', 'Status pelapor wajib dipilih']);
    if (isFirstReport.value) {
        fields.push(['statusCivitas', 'Status civitas akademik wajib dipilih']);
    }
    return fields;
});

const stepValidatorsForm = computed(() => ({
    1: step1Fields.value,
    2: [
        ['jenisKekerasan', 'Jenis kekerasan wajib dipilih'],
        ['tempatKejadian', 'Tempat kejadian wajib diisi'],
        ['waktuKejadian', 'Waktu kejadian wajib diisi'],
        ['kronologi', 'Kronologi kejadian wajib diisi'],
    ],
    3: [['agreed', 'Pernyataan persetujuan harus diceklis']],
}));

const stepValidatorsAudio = computed(() => ({
    1: step1Fields.value,
    2: [['jenisKekerasan', 'Jenis kekerasan wajib dipilih']],
    3: [['agreed', 'Pernyataan persetujuan harus diceklis']],
}));

const validateStep = (step: 1 | 2 | 3) => {
    const newErrors = { ...stepErrors.value };
    let isValid = true;

    const validators =
        selectedMethod.value === 'audio'
            ? stepValidatorsAudio.value
            : stepValidatorsForm.value;

    if (step === 2 && selectedMethod.value === 'audio') {
        if (audioRecordings.value.length === 0) {
            newErrors['audioRecordings'] =
                'Minimal satu rekaman suara wajib ditambahkan';
            stepErrors.value = newErrors;
            scrollToField('audioRecordings');
            return false;
        }
        delete newErrors['audioRecordings'];
    }

    for (const [field, message] of validators[step]) {
        const value = form[field as keyof typeof form];

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

// Fungsi diubah namanya agar tidak bentrok dengan fungsi dari useStep
const handleNextStep = () => {
    if (currentStep.value === 1 && !validateStep(1)) return;
    if (currentStep.value === 2 && !validateStep(2)) return;
    if (currentStep.value === 3 && !validateStep(3)) return;

    stepErrors.value = {};
    nextStep();
};

// Fungsi diubah namanya agar tidak bentrok dengan fungsi dari useStep
const handlePrevStep = () => {
    prevStep();
};

const watchedFields = [
    'nama',
    'whatsapp',
    'jurusan',
    'prodi',
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

watch(
    () => form.jurusan,
    () => {
        form.prodi = '';
    },
);
watch(
    () => form.disabilitas,
    (newVal, oldVal) => {
        if (!newVal || !oldVal) return;

        const justAddedTidakAda =
            newVal.includes('tidak_ada') && !oldVal.includes('tidak_ada');

        if (justAddedTidakAda) {
            form.disabilitas = ['tidak_ada'];
        } else if (newVal.length > 1 && newVal.includes('tidak_ada')) {
            form.disabilitas = newVal.filter((val) => val !== 'tidak_ada');
        } else if (newVal.length === 0) {
            form.disabilitas = ['tidak_ada'];
        }
    },
    { deep: true },
);
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

const display = (value?: string) => value || '-';

const formatDateTime = (value: string) => {
    if (!value) return '-';
    const d = new Date(value);
    return d.toLocaleString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const summaryItems = computed(() => {
    const base = [
        {
            label: 'Status Pelapor',
            value:
                statusOptions.find((item) => item.value === form.statusPelapor)
                    ?.label ?? '-',
        },
        {
            label: 'Pelapor Sebagai',
            value:
                statusCivitasOptions.find(
                    (item) => item.value === form.statusCivitas,
                )?.label ?? '-',
        },
        {
            label: 'Jenis Dugaan',
            value:
                jenisKekerasanOptions.find(
                    (item) => item.value === form.jenisKekerasan,
                )?.label ?? '-',
        },
        {
            label: 'Terlapor',
            value:
                display(form.namaTerlapor) === '-'
                    ? 'Tidak Diketahui'
                    : display(form.namaTerlapor),
        },
    ];

    if (selectedMethod.value === 'audio') {
        base.push({
            label: 'Rekaman Suara',
            value:
                audioRecordings.value.length > 0
                    ? `${audioRecordings.value.length} rekaman`
                    : '-',
        });
    } else {
        base.push(
            { label: 'Tempat Kejadian', value: display(form.tempatKejadian) },
            {
                label: 'Waktu Kejadian',
                value: formatDateTime(form.waktuKejadian),
            },
        );
    }

    return base;
});

const handleSubmit = async () => {
    stepErrors.value = {};

    if (!validateStep(3)) {
        return;
    }

    try {
        const publicKeys = await getPublicKeys();

        const encryptedFiles = await Promise.all(
            form.bukti.map((file) => encryptFile(file, publicKeys)),
        );

        const formData = new FormData();

        formData.append('nama', form.nama);
        formData.append('whatsapp', form.whatsapp);
        formData.append('jurusan', form.jurusan);
        formData.append('prodi', form.prodi);
        formData.append('statusPelapor', form.statusPelapor);
        formData.append('statusCivitas', form.statusCivitas);
        formData.append('namaTerlapor', form.namaTerlapor || 'Tidak Diketahui');
        formData.append('statusTerlapor', form.statusTerlapor);
        formData.append('jenisKekerasan', form.jenisKekerasan);
        formData.append(
            'tempatKejadian',
            selectedMethod.value === 'audio'
                ? form.tempatKejadian || ''
                : form.tempatKejadian,
        );
        formData.append(
            'waktuKejadian',
            selectedMethod.value === 'audio'
                ? form.waktuKejadian || ''
                : form.waktuKejadian,
        );
        formData.append(
            'kronologi',
            selectedMethod.value === 'audio' ? '' : form.kronologi,
        );
        formData.append('metode', selectedMethod.value);
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

        audioRecordings.value.forEach((rec, index) => {
            const file = new File([rec.blob], `rekaman-${index + 1}.webm`, {
                type: rec.blob.type || 'audio/webm',
            });
            formData.append(`audio_recordings[${index}][file]`, file);
            formData.append(
                `audio_recordings[${index}][duration]`,
                rec.duration.toString(),
            );
            formData.append(
                `audio_recordings[${index}][order]`,
                (index + 1).toString(),
            );
        });

        router.post(store().url, formData, {
            onSuccess: () => {
                form.reset();
                currentStep.value = 1;
                stepErrors.value = {};
                selectedMethod.value = 'form';
                audioRecordings.value = [];
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
        class="flex min-h-screen flex-col overflow-x-hidden overflow-y-auto"
        style="font-family: 'Plus Jakarta Sans', sans-serif"
    >
        <!-- Main Content -->
        <main class="mx-auto w-full max-w-4xl flex-1 px-4 py-10">
            <!-- Page Header -->
            <div class="mb-6 text-center">
                <h1 class="mb-2 text-3xl font-bold">Buat Laporan</h1>
                <p class="mx-auto max-w-md text-sm leading-relaxed">
                    <span class="font-bold text-gray-900"
                        >Pilih metode pelaporan yang paling nyaman.</span
                    >
                    <span class="text-gray-500">
                        Identitas Anda dilindungi dan hanya dapat diakses oleh
                        Satgas PPK.</span
                    >
                </p>
            </div>

            <!-- Divider full-width -->
            <div
                class="mb-6 border-b border-gray-200"
                style="width: 100vw; margin-left: calc(50% - 50vw)"
            ></div>

            <!-- Method Selector — hanya tampil di step 1 dan 2 -->
            <div v-if="currentStep < 3" class="mb-6">
                <MethodSelector v-model="selectedMethod" :options="methods" />
            </div>

            <div
                class="my-6 overflow-hidden rounded-2xl border border-gray-200 bg-[#ECE8E2] shadow-sm"
            >
                <!-- STEP INDICATOR -->
                <div class="border-b border-gray-200 bg-white px-10 py-5">
                    <div class="flex items-start">
                        <template v-for="(step, index) in steps" :key="index">
                            <div
                                class="flex flex-col items-center gap-1.5"
                                style="min-width: 110px"
                            >
                                <!-- Circle -->
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold transition-all"
                                    :class="[
                                        currentStep >= index + 1
                                            ? 'bg-[#1A5BA6] text-white'
                                            : 'border-2 border-gray-300 bg-white text-gray-400',
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
                                    class="text-center text-xs font-bold"
                                    :class="
                                        currentStep >= index + 1
                                            ? 'text-gray-900'
                                            : 'text-gray-400'
                                    "
                                >
                                    {{ step.title }}
                                </span>

                                <span
                                    class="max-w-[110px] text-center text-[10px] leading-tight text-gray-400"
                                >
                                    {{ step.desc }}
                                </span>
                            </div>

                            <!-- Connector line -->
                            <div
                                v-if="index < steps.length - 1"
                                class="mt-4 h-0.5 flex-1"
                                :class="
                                    currentStep > index + 1
                                        ? 'bg-[#1A5BA6]'
                                        : 'bg-gray-200'
                                "
                            />
                        </template>
                    </div>
                </div>

                <!-- Form Card -->
                <form @submit.prevent="handleSubmit">
                    <div class="bg-white p-8">
                        <!-- STEP 1 -->
                        <div v-if="currentStep === 1">
                            <h2 class="mb-1 text-xl font-bold text-gray-900">
                                Data Pelapor
                            </h2>
                            <p class="mb-8 text-sm text-gray-500">
                                Identitas Anda hanya diakses oleh Ketua Satgas.
                                Nomor WhatsApp digunakan untuk komunikasi tindak
                                lanjut.
                            </p>

                            <FormCardSection>
                                <FormSectionTitle title="Identitas Anda" />
                                <div
                                    class="grid grid-cols-1 gap-5 md:grid-cols-2"
                                >
                                    <FormField
                                        name="nama"
                                        v-model="form.nama"
                                        label="Nama lengkap pelapor"
                                        :required="isFirstReport"
                                        :disabled="!isFirstReport"
                                        :error="stepErrors.nama"
                                        placeholder="Mis. Annisa Putri"
                                    />
                                    <FormField
                                        name="whatsapp"
                                        v-model="form.whatsapp"
                                        label="Nomor WhatsApp aktif"
                                        required
                                        :error="stepErrors.whatsapp"
                                        placeholder="Mis. 081234567890"
                                        :pattern="phonePattern"
                                        hint="Format: 08xxxxxxxxxx, 10-13 panjang angka"
                                    />
                                    <DropdownField
                                        name="jurusan"
                                        v-model="form.jurusan"
                                        label="Jurusan"
                                        placeholder="Pilih jurusan..."
                                        :options="
                                            jurusanList.map((j) => ({
                                                label: j.name,
                                                value: j.name,
                                            }))
                                        "
                                        :error="stepErrors.jurusan"
                                        :required="isFirstReport"
                                        :disabled="!isFirstReport"
                                    />
                                    <DropdownField
                                        name="prodi"
                                        v-model="form.prodi"
                                        label="Program Studi"
                                        placeholder="Pilih program studi..."
                                        :options="
                                            filteredProdi.map((p) => ({
                                                label: `${p.degreeLevel} ${p.name}`,
                                                value: `${p.degreeLevel} ${p.name}`,
                                            }))
                                        "
                                        :error="stepErrors.prodi"
                                        :required="isFirstReport"
                                        :disabled="!isFirstReport"
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
                                    :required="isFirstReport"
                                    :disabled="!isFirstReport"
                                />
                            </FormCardSection>

                            <FormCardSection>
                                <FormSectionTitle title="Kebutuhan Khusus" />
                                <MultiSelect
                                    v-model="form.disabilitas"
                                    label="Apakah Anda memiliki kebutuhan disabilitas tertentu?"
                                    :options="disabilityOptions"
                                    :error="stepErrors.disabilitas"
                                    helperText="Informasi ini membantu Satgas menyediakan akomodasi yang sesuai pada tahap klarifikasi."
                                />
                            </FormCardSection>
                        </div>

                        <!-- STEP 2 — Formulir Lengkap -->
                        <div
                            v-if="
                                currentStep === 2 && selectedMethod === 'form'
                            "
                        >
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
                                        :error="stepErrors.namaTerlapor"
                                        placeholder="Nama / inisial pihak terlapor"
                                        hint="Anda dapat mengosongkan nama dugaan terlapor jika belum tahu/yakin."
                                    />
                                    <DropdownField
                                        name="statusTerlapor"
                                        v-model="form.statusTerlapor"
                                        label="Terlapor sebagai"
                                        placeholder="Pilih status terlapor..."
                                        :options="statusTerlaporrOptions"
                                        :error="stepErrors.statusTerlapor"
                                    />
                                </div>
                            </FormCardSection>

                            <FormCardSection>
                                <FormSectionTitle
                                    title="Jenis & Tempat Kejadian"
                                />
                                <div
                                    class="mb-5 grid grid-cols-1 gap-5 md:grid-cols-2"
                                >
                                    <DropdownField
                                        name="jenisKekerasan"
                                        v-model="form.jenisKekerasan"
                                        label="Jenis dugaan kekerasan"
                                        placeholder="Pilih jenis dugaan kekerasan..."
                                        :options="jenisKekerasanOptions"
                                        :error="stepErrors.jenisKekerasan"
                                        required
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
                                <FormField
                                    name="tempatKejadian"
                                    v-model="form.tempatKejadian"
                                    label="Tempat kejadian"
                                    required
                                    :error="stepErrors.tempatKejadian"
                                    placeholder="Mis. Gedung Elektro, R. 304"
                                />
                            </FormCardSection>

                            <FormCardSection>
                                <FormSectionTitle title="Kronologi" />
                                <TextareaField
                                    name="kronologi"
                                    v-model="form.kronologi"
                                    label="Kronologi kejadian"
                                    placeholder="Ceritakan apa yang terjadi"
                                    rows="6"
                                    required
                                    hint="Tidak perlu sempurna. Ceritakan sesuai yang Anda ingat dan rasakan. Anda bisa mulai dari apa yang melatarbelakangi kejadian, apa yang terjadi, dan bagaimana dampaknya terhadap Anda. Detail lebih lanjut dapat dilengkapi di tahap klarifikasi bersama Satgas."
                                    :error="stepErrors.kronologi"
                                />
                            </FormCardSection>

                            <FormCardSection>
                                <FormSectionTitle title="Bukti Pendukung" />
                                <FileUploadField
                                    v-model="form.bukti"
                                    label="Unggah bukti digital berupa gambar, dokumen, video, atau audio untuk mendukung laporan yang Anda sampaikan. (opsional)"
                                    hint="Privasi dan keamanan data Anda menjadi prioritas. Seluruh bukti digital disimpan secara terenkripsi dan dikelola secara terbatas sesuai kebutuhan penanganan kasus."
                                />
                            </FormCardSection>
                        </div>

                        <!-- STEP 2 — Formulir + Rekaman Suara -->
                        <div
                            v-if="
                                currentStep === 2 && selectedMethod === 'audio'
                            "
                        >
                            <h2 class="mb-1 text-xl font-bold">
                                Rekam Kronologi Kejadian
                            </h2>
                            <p class="mb-8 text-sm text-gray-500">
                                Sampaikan kejadian dengan suara Anda. Tidak
                                perlu sempurna. Anda dapat merekam ulang kapan
                                saja sebelum mengirim laporan.
                            </p>

                            <FormCardSection>
                                <div data-field="audioRecordings">
                                    <VoiceRecorder
                                        v-model="audioRecordings"
                                        :error="stepErrors.audioRecordings"
                                    />
                                </div>
                            </FormCardSection>

                            <FormCardSection>
                                <FormSectionTitle
                                    title="Jenis Dugaan Kekerasan"
                                />
                                <DropdownField
                                    name="jenisKekerasan"
                                    v-model="form.jenisKekerasan"
                                    label="Jenis Dugaan Kekerasan"
                                    placeholder="Pilih jenis dugaan kekerasan..."
                                    :options="jenisKekerasanOptions"
                                    :error="stepErrors.jenisKekerasan"
                                    required
                                />
                            </FormCardSection>

                            <FormCardSection>
                                <FormSectionTitle title="Bukti Digital" />
                                <FileUploadField
                                    v-model="form.bukti"
                                    label="Unggah bukti digital berupa gambar, video, atau audio untuk mendukung laporan yang Anda sampaikan."
                                    hint="Privasi dan keamanan data Anda menjadi prioritas. Seluruh bukti digital disimpan secara terenkripsi dan dikelola secara terbatas sesuai kebutuhan penanganan kasus."
                                />
                            </FormCardSection>
                        </div>

                        <!-- STEP 3 -->
                        <div v-if="currentStep === 3">
                            <div class="flex flex-col gap-2">
                                <div>
                                    <h1
                                        class="text-2xl font-bold text-gray-900"
                                    >
                                        Konfirmasi & Kirim Laporan
                                    </h1>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Periksa kembali ringkasan laporan Anda
                                        sebelum dikirim ke Satgas PPK.
                                    </p>
                                </div>

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

                                <Agreement
                                    v-model="form.agreed"
                                    title="Saya menyatakan bahwa seluruh informasi yang saya sampaikan dalam laporan ini adalah benar sesuai pengetahuan saya dan disampaikan dengan itikad baik."
                                    description="Saya memahami konsekuensi atas penyampaian informasi yang tidak benar atau sengaja menyesatkan."
                                    :error="stepErrors.agreed"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Footer full-width -->
                    <div
                        class="flex items-center justify-between border-t border-[#ECE8E2] bg-[#FDFCFB] px-8 py-5"
                    >
                        <span class="text-xs text-gray-500">
                            Langkah {{ currentStep }} dari {{ steps.length }}
                        </span>

                        <div class="flex items-center gap-3">
                            <button
                                v-if="currentStep > 1"
                                type="button"
                                @click="prevStep"
                                class="flex items-center gap-2 rounded-lg border border-gray-400 bg-white px-5 py-2.5 text-sm font-medium transition-all hover:bg-gray-50"
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
                                @click="handleNextStep"
                                class="flex items-center gap-2 rounded-lg bg-[#F97316] px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-orange-600"
                            >
                                {{ nextButtonLabel }}
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
                                class="flex items-center gap-2 rounded-lg bg-[#F97316] px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-600"
                            >
                                Kirim Laporan
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
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
</template>
