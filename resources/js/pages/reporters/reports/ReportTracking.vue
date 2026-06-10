<script setup lang="ts">
import { ref } from 'vue';
import StepIndicator from '@/components/StepIndicator.vue';
import { useStep } from '@/composables/useStep';

const { currentStep, steps, nextStep } = useStep(
    [
        {
            title: 'Pernyataan Kerahasiaan',
            desc: 'Persetujuan menjaga informasi',
        },
        { title: 'Pilih Laporan', desc: 'Memilih laporan yang akan ditinjau' },
        { title: 'Lihat Progress', desc: 'Alur Penanganan Oleh Satgas' },
    ],
    3,
);

const agreed = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Step Indicator -->
        <StepIndicator :steps="steps" :current-step="currentStep" />

        <!-- Hero Banner -->
        <div class="bg-[#1A5BA6] px-6 py-8 md:px-10">
            <div class="mx-auto flex max-w-3xl items-start gap-4">
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-white/30"
                >
                    <svg
                        class="h-6 w-6 text-white"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"
                        />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white">
                        Pernyataan Kerahasiaan
                    </h1>
                    <p class="mt-1.5 text-sm leading-relaxed text-white/80">
                        Sebelum melihat progres kasus, Anda perlu memahami dan
                        menyetujui pernyataan kerahasiaan berikut. Pernyataan
                        ini melindungi privasi Anda dan pihak-pihak yang
                        terlibat.
                    </p>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="mx-auto max-w-3xl px-6 py-6 md:px-10">
            <!-- Declaration Card -->
            <div class="rounded-xl border border-gray-200 bg-white p-6">
                <p class="mb-4 font-bold text-gray-800">
                    Saya menyatakan bahwa:
                </p>
                <ol class="flex flex-col gap-4">
                    <li
                        class="flex gap-3 text-sm leading-relaxed text-gray-600"
                    >
                        <span
                            class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-50 text-[11px] font-bold text-[#1A5BA6]"
                            >1</span
                        >
                        <span>
                            <strong class="font-semibold text-gray-800">
                                Informasi yang saya akses pada halaman ini
                                bersifat rahasia.
                            </strong>
                            Saya tidak akan mengambil tangkapan layar, menyalin,
                            atau menyebarluaskan informasi progres kasus kepada
                            pihak yang tidak berkepentingan.
                        </span>
                    </li>
                    <li
                        class="flex gap-3 text-sm leading-relaxed text-gray-600"
                    >
                        <span
                            class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-50 text-[11px] font-bold text-[#1A5BA6]"
                            >2</span
                        >
                        <span>
                            <strong class="font-semibold text-gray-800">
                                Saya adalah pelapor yang sah
                            </strong>
                            atas kasus dengan nomor laporan tersebut. Saya
                            bertanggung jawab penuh atas penggunaan akses ini.
                        </span>
                    </li>
                    <li
                        class="flex gap-3 text-sm leading-relaxed text-gray-600"
                    >
                        <span
                            class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-50 text-[11px] font-bold text-[#1A5BA6]"
                            >3</span
                        >
                        <span>
                            <strong class="font-semibold text-gray-800">
                                Pelanggaran pernyataan ini
                            </strong>
                            dapat menyebabkan dikenakan sanksi sesuai peraturan
                            Polban yang berlaku dan/atau tindak lanjut
                            berdasarkan ketentuan peraturan perundang-undangan,
                            termasuk UU ITE.
                        </span>
                    </li>
                </ol>
            </div>

            <!-- Checkbox -->
            <label
                class="mt-3 flex cursor-pointer items-start gap-3 rounded-xl border bg-white p-4 transition-colors duration-200"
                :class="agreed ? 'border-[#1A5BA6]' : 'border-gray-200'"
            >
                <input
                    v-model="agreed"
                    type="checkbox"
                    class="mt-0.5 h-4 w-4 shrink-0 cursor-pointer accent-[#1A5BA6]"
                />
                <span class="text-sm leading-relaxed text-gray-600">
                    Saya telah membaca, memahami, dan menyetujui Pernyataan
                    Kerahasiaan SIKPK di atas. Saya bersedia menjaga seluruh
                    informasi progres kasus yang akan ditampilkan.
                </span>
            </label>

            <!-- Action Button -->
            <div class="mt-6 flex justify-end">
                <button
                    :disabled="!agreed"
                    class="flex items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold text-white transition-all duration-200"
                    :class="
                        agreed
                            ? 'bg-[#1A5BA6] hover:bg-[#154d8e] active:scale-[0.98]'
                            : 'cursor-not-allowed bg-[#1A5BA6]/40'
                    "
                    @click="nextStep(() => agreed.value)"
                >
                    Setuju &amp; Lihat Progres
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"
                        />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>
