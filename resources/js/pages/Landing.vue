<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Lock, EyeOff, ShieldCheck, MapPin, Phone, Mail, Clock, ArrowRight, CheckCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { create, track } from '@/routes/reports';
import { login as loginGoogle } from '@/routes/google';
import type { Auth } from '@/types';

const page = usePage<{ auth: Auth; flash: { toast?: any; reportSubmitted?: boolean } }>();
const user = computed(() => page.props.auth?.user ?? null);
const buatLaporanUrl = computed(() => user.value ? create().url : loginGoogle());

// ─── Report Submitted Dialog ──────────────────────────────────────────────────

const showSuccessDialog = ref(page.props.flash?.reportSubmitted === true);

// ─── Hero Slider ─────────────────────────────────────────────────────────────

interface Slide {
    image: string;
    badge: string;
    caption: string;
}

const slides: Slide[] = [
    {
        image: '/images/landing/slide-1.webp',
        badge: 'Sosialisasi PPK',
        caption: 'Bersama membangun Polban yang aman dan bebas kekerasan',
    },
    {
        image: '/images/landing/slide-2.webp',
        badge: 'Pendampingan Korban',
        caption: 'Setiap pelapor mendapat pendampingan penuh dari tim profesional',
    },
    {
        image: '/images/landing/slide-3.webp',
        badge: 'Kerja Nyata Satgas',
        caption: 'Satgas PPK Polban berkomitmen menangani setiap kasus dengan tuntas',
    },
];

const currentSlide = ref(0);
let autoTimer: ReturnType<typeof setInterval> | null = null;

function goToSlide(index: number) {
    currentSlide.value = (index + slides.length) % slides.length;
}

function prevSlide() { goToSlide(currentSlide.value - 1); }
function nextSlide() { goToSlide(currentSlide.value + 1); }

function startAuto() {
    autoTimer = setInterval(nextSlide, 5000);
}
function stopAuto() {
    if (autoTimer) clearInterval(autoTimer);
}

onMounted(() => {
    startAuto();
    document.documentElement.style.scrollSnapType = 'y proximity';
    document.documentElement.style.scrollPaddingTop = '90px';
});

onUnmounted(() => {
    stopAuto();
    document.documentElement.style.scrollSnapType = '';
    document.documentElement.style.scrollPaddingTop = '';
});
</script>

<template>
    <Head title="Beranda" />

    <!-- ─── Report Submitted Success Dialog ─────────────────────────────────── -->
    <Dialog :open="showSuccessDialog" @update:open="showSuccessDialog = $event">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <div class="mb-3 flex justify-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100">
                        <CheckCircle class="size-8 text-emerald-600" />
                    </div>
                </div>
                <DialogTitle class="text-center text-lg font-bold">
                    Laporan berhasil dikirim
                </DialogTitle>
                <DialogDescription class="text-center text-sm text-muted-foreground">
                    Terima kasih telah melapor. Tim Satgas PPK Polban akan segera menindaklanjuti laporan Anda.
                    Anda dapat memantau perkembangan kasus melalui halaman pelacakan laporan.
                </DialogDescription>
            </DialogHeader>

            <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:justify-center">
                <Button as-child variant="brand-accent" class="w-full font-semibold sm:w-auto">
                    <Link :href="track().url">
                        Lacak Laporan Saya
                        <ArrowRight class="ml-1 size-4" />
                    </Link>
                </Button>
                <Button
                    variant="outline"
                    class="w-full font-semibold sm:w-auto"
                    @click="showSuccessDialog = false"
                >
                    Tutup
                </Button>
            </div>
        </DialogContent>
    </Dialog>

    <!-- Info banner + Hero Section 1 -->
    <section style="scroll-snap-align: start;">
        <div class="border-b border-border bg-[#FDFCFB] px-4 py-8 sm:px-6 sm:py-10 md:px-8">
            <div class="mx-auto max-w-screen-xl text-center">
                <h2 v-reveal class="font-display text-lg font-extrabold tracking-tight text-foreground sm:text-xl">Informasi Penting</h2>
                <p v-reveal="'100'" class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-muted-foreground">
                    Portal pelaporan ini hanya diperuntukkan bagi civitas akademika yang memiliki alamat email POLBAN.
                    Bagi pihak lain yang ingin menyampaikan laporan, silakan menghubungi hotline Satgas melalui
                    <a
                        href="https://wa.me/6281324050594"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="font-semibold text-brand underline"
                    >WhatsApp</a>.
                </p>
            </div>
        </div>

        <div class="mx-auto max-w-screen-xl px-4 py-10 sm:px-6 md:px-8 md:py-16">
        <div class="grid grid-cols-1 items-center gap-8 lg:grid-cols-2 lg:gap-12">

            <!-- Left: text content -->
            <div class="flex flex-col gap-5 md:gap-6">

                <!-- Heading -->
                <h1 v-reveal class="font-display text-3xl font-extrabold leading-tight tracking-tight text-foreground sm:text-4xl lg:text-5xl">
                    Anda <span class="text-brand">tidak sendirian.</span><br />
                    Laporan Anda kami<br />dengar.
                </h1>

                <!-- Description -->
                <p v-reveal="'100'" class="text-sm leading-relaxed text-muted-foreground text-justify sm:text-base">
                    Di SIKPK, setiap suara berharga. Sebagai kanal resmi pelaporan
                    dan penanganan kekerasan di lingkungan Politeknik Negeri Bandung,
                    kami hadir untuk mendengarkan, menjaga kerahasiaan identitas Anda,
                    serta memberikan perlindungan dan pendampingan.
                </p>

                <!-- CTAs -->
                <div v-reveal="'200'" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <Button as-child variant="brand-accent" size="lg" class="w-full font-semibold sm:w-auto">
                        <Link :href="buatLaporanUrl">
                            Buat Laporan Sekarang
                            <ArrowRight class="ml-1 size-4" />
                        </Link>
                    </Button>
                    <Button variant="outline" size="lg" class="w-full font-semibold sm:w-auto">
                        Lacak Kasus Anda
                    </Button>
                </div>

                <!-- Stats -->
                <div v-reveal="'300'" class="flex flex-wrap items-center gap-4 border-t border-border pt-6 sm:gap-8">
                    <div>
                        <p class="font-display text-xl font-bold text-[#0F3A6C] sm:text-2xl">100%</p>
                        <p class="text-xs text-muted-foreground">Identitas dilindungi</p>
                    </div>
                    <div class="h-8 w-px bg-border" />
                    <div>
                        <p class="font-display text-xl font-bold text-[#0F3A6C] sm:text-2xl">&lt; 24 Jam</p>
                        <p class="text-xs text-muted-foreground">Respons pertama</p>
                    </div>
                    <div class="hidden h-8 w-px bg-border sm:block" />
                    <div class="hidden sm:block">
                        <p class="font-display text-xl font-bold text-[#0F3A6C] sm:text-2xl">Anggota Satgas</p>
                        <p class="text-xs text-muted-foreground">Pendamping profesional</p>
                    </div>
                </div>
            </div>

            <!-- Right: image slider -->
            <div v-reveal="'200'" class="relative overflow-hidden rounded-2xl shadow-xl"
                 @mouseenter="stopAuto"
                 @mouseleave="startAuto">

                <!-- Slides -->
                <div class="relative aspect-[4/3]">
                    <transition-group name="slide-fade">
                        <div
                            v-for="(slide, i) in slides"
                            v-show="i === currentSlide"
                            :key="i"
                            class="absolute inset-0"
                        >
                            <!-- Image -->
                            <img
                                :src="slide.image"
                                :alt="slide.caption"
                                class="h-full w-full object-cover"
                            />

                            <!-- Overlay gradient top & bottom -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-black/40" />

                            <!-- Caption -->
                            <div class="absolute bottom-0 left-0 right-0 px-4 pb-8 pt-5 sm:px-5 sm:pb-10">
                                <span class="mb-2 inline-block rounded-full bg-brand-accent px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider text-white">
                                    {{ slide.badge }}
                                </span>
                                <p class="font-display text-sm font-bold leading-snug text-white sm:text-base">
                                    {{ slide.caption }}
                                </p>
                            </div>
                        </div>
                    </transition-group>
                </div>

                <!-- Logo overlay -->
                <div class="absolute left-0 right-0 top-0 flex items-center gap-3 p-4 sm:p-5">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full border border-white/30 bg-white/[0.14] backdrop-blur-sm sm:h-10 sm:w-10">
                        <AppLogoIcon class="size-7 object-contain sm:size-8" />
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span class="font-display text-xs font-bold leading-none text-white drop-shadow sm:text-sm">
                            Satuan Tugas PPK
                        </span>
                        <span class="font-display text-[10px] leading-none text-white/80 drop-shadow sm:text-xs">
                            Politeknik Negeri Bandung
                        </span>
                    </div>
                </div>

                <!-- Prev / Next arrows -->
                <button
                    class="absolute left-2 top-1/2 -translate-y-1/2 flex h-8 w-8 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm transition hover:bg-white/40 sm:left-3"
                    @click="prevSlide"
                    aria-label="Slide sebelumnya"
                >
                    <ChevronLeft class="size-5 text-white" />
                </button>
                <button
                    class="absolute right-2 top-1/2 -translate-y-1/2 flex h-8 w-8 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm transition hover:bg-white/40 sm:right-3"
                    @click="nextSlide"
                    aria-label="Slide berikutnya"
                >
                    <ChevronRight class="size-5 text-white" />
                </button>

                <!-- Dot indicators -->
                <div class="absolute bottom-3 right-3 flex gap-1.5 sm:bottom-4 sm:right-4">
                    <button
                        v-for="(_, i) in slides"
                        :key="i"
                        :class="[
                            'h-1.5 rounded-full transition-all',
                            i === currentSlide ? 'w-5 bg-white' : 'w-1.5 bg-white/50',
                        ]"
                        @click="goToSlide(i)"
                        :aria-label="`Slide ${i + 1}`"
                    />
                </div>
            </div>

        </div>
        </div>
    </section>

    <!-- Access Information Section 2 -->
    <section
        id="informasi"
        class="py-10 md:py-16"
        style="background: linear-gradient(165deg, #1A5BA6 0%, #0B2A4F 68%); scroll-snap-align: start;"
    >
        <div class="mx-auto max-w-screen-xl px-4 sm:px-6 md:px-8">

            <!-- Header -->
            <div v-reveal class="mb-6 max-w-lg">
                <h2 class="font-display text-2xl font-extrabold leading-tight text-white sm:text-3xl lg:text-4xl">
                    Kerahasiaan adalah<br />
                    <span class="text-brand-accent">Prioritas Utama Kami</span>
                </h2>
                <p class="mt-4 text-sm leading-relaxed text-white/70 text-justify">
                    Setiap informasi yang Anda bagikan dilindungi dengan sistem keamanan berlapis.
                    Tidak ada satu pihak pun yang dapat mengakses identitas Anda tanpa izin.
                </p>
            </div>

            <!-- Feature cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">

                <!-- Card: Enkripsi -->
                <div v-reveal="'100'" class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm sm:p-6">
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-[#F5821F]/[0.16]">
                        <Lock class="size-5 text-[#FBA661]" />
                    </div>
                    <h3 class="mb-2 font-display text-base font-bold text-white">Enkripsi End-to-End</h3>
                    <p class="text-sm leading-relaxed text-white/60 text-justify">
                        Seluruh bukti dan dokumen yang Anda kirimkan akan diamankan terlebih dahulu sebelum disimpan ke dalam sistem.
                        Tidak ada pihak lain yang dapat membaca isi laporan Anda.
                    </p>
                </div>

                <!-- Card: Anonimitas -->
                <div v-reveal="'200'" class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm sm:p-6">
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-[#F5821F]/[0.16]">
                        <EyeOff class="size-5 text-[#FBA661]" />
                    </div>
                    <h3 class="mb-2 font-display text-base font-bold text-white">Anonimitas Terjaga</h3>
                    <p class="text-sm leading-relaxed text-white/60 text-justify">
                        Identitas dan informasi yang Anda kirimkan tidak dapat diakses oleh pihak yang tidak berkepentingan.
                        Kerahasiaan Informasi Anda adalah prioritas kami.
                    </p>
                </div>

                <!-- Card: Akses Terbatas -->
                <div v-reveal="'300'" class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm sm:col-span-2 sm:p-6 md:col-span-1">
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-[#F5821F]/[0.16]">
                        <ShieldCheck class="size-5 text-[#FBA661]" />
                    </div>
                    <h3 class="mb-2 font-display text-base font-bold text-white">Akses Data Terbatas</h3>
                    <p class="text-sm leading-relaxed text-white/60 text-justify">
                        Hanya anggota Satgas yang ditugaskan yang dapat melihat laporan Anda. Laporan tidak dapat diakses oleh pihak di luar tim penanganan.
                        Setiap progress penanganan akan tercatat dan dapat ditinjau.
                    </p>
                </div>

            </div>

            <!-- Legal banner -->
            <div v-reveal="'400'" class="mt-6 flex items-start gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-4 backdrop-blur-sm sm:gap-4 sm:px-6 sm:py-5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/10">
                    <ShieldCheck class="size-5 text-white" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-white">
                        Laporan Anda aman bersama kami. Dijamin oleh Peraturan Mendikbudristek No. 55 Tahun 2024
                    </p>
                    <p class="mt-1 text-xs text-white/55">
                        Satgas PPKPT Politeknik Negeri Bandung beroperasi sesuai regulasi nasional tentang
                        Pencegahan dan Penanganan Kekerasan di Perguruan Tinggi.
                    </p>
                </div>
            </div>

        </div>
    </section>

    <!-- General Information Section 3 -->
    <section class="py-10 md:py-16" style="background-color: #FDFCFB; scroll-snap-align: start;">
        <div class="mx-auto max-w-screen-xl px-4 sm:px-6 md:px-8">

            <!-- Header -->
            <div v-reveal class="mb-8 text-center">
                <h2 class="font-display text-2xl font-extrabold leading-tight text-foreground sm:text-3xl lg:text-4xl">
                    Sebelum Anda melaporkan,<br />perhatikan hal berikut
                </h2>
                <p class="mx-auto mt-4 max-w-md text-sm leading-relaxed text-muted-foreground">
                    Beberapa informasi ringkas untuk membantu Anda menyiapkan laporan yang akurat dan lengkap.
                </p>
            </div>

            <!-- 3 columns -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">

                <!-- Col 1 — Suara Anda Berarti -->
                <div v-reveal="'100'" class="flex flex-col gap-4 rounded-2xl bg-white p-5 shadow-sm sm:p-6">
                    <span class="inline-flex w-fit items-center rounded-full border border-brand/20 bg-brand/10 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-brand">
                        Suara Anda Berarti
                    </span>
                    <h3 class="font-display text-lg font-bold leading-snug text-foreground sm:text-xl">
                        Melapor adalah langkah pertama menuju perubahan
                    </h3>
                    <p class="text-sm leading-relaxed text-muted-foreground text-justify">
                        Setiap laporan menjadi dasar Polban untuk mencegah kekerasan terulang dan
                        menciptakan lingkungan kampus yang aman.
                    </p>
                    <ul class="flex flex-col gap-2.5">
                        <li v-for="item in [
                            'Anda dapat melapor sebagai korban maupun saksi',
                            'Anda yang memegang kendali tindak lanjut penanganan',
                            'Anda mendapatkan status progres laporan penanganan',
                            'Anda berhak atas pendampingan psikologis dan hukum',
                        ]" :key="item" class="flex items-start gap-2 text-sm text-muted-foreground">
                            <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-brand" />
                            {{ item }}
                        </li>
                    </ul>
                </div>

                <!-- Col 2 — Cakupan Laporan -->
                <div v-reveal="'200'" class="flex flex-col gap-4 rounded-2xl bg-white p-5 shadow-sm sm:p-6">
                    <span class="inline-flex w-fit items-center rounded-full border border-brand-accent/20 bg-brand-accent/10 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-brand-accent">
                        Cakupan Laporan
                    </span>
                    <h3 class="font-display text-lg font-bold leading-snug text-foreground sm:text-xl">
                        Jenis dugaan kekerasan yang dapat dilaporkan
                    </h3>
                    <p class="text-sm leading-relaxed text-muted-foreground text-justify">
                        SIKPK menerima laporan atas berbagai bentuk kekerasan di lingkungan Polban. Anda tetap dapat membuat laporan meski belum yakin atau tidak tahu jenis kekerasan yang dialami.
                    </p>
                    <ul class="flex flex-col gap-2.5">
                        <li v-for="item in [
                            'Kekerasan Fisik',
                            'Kekerasan Psikis',
                            'Kekerasan Seksual',
                            'Perundungan (Bullying)',
                            'Diskriminasi dan Intoleransi',
                            'Kebijakan yang Merugikan/Kekerasan',
                        ]" :key="item" class="flex items-start gap-2 text-sm text-muted-foreground">
                            <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-accent" />
                            {{ item }}
                        </li>
                    </ul>
                </div>

                <!-- Col 3 — Peran Satgas PPKPT -->
                <div v-reveal="'300'" class="flex flex-col gap-4 rounded-2xl bg-white p-5 shadow-sm md:col-span-2 md:p-6 lg:col-span-1">
                    <span class="inline-flex w-fit items-center rounded-full border border-emerald-600/20 bg-emerald-500/10 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-emerald-700">
                        Peran Satgas PPKPT
                    </span>
                    <h3 class="font-display text-lg font-bold leading-snug text-foreground sm:text-xl">
                        Mandat berdasarkan Permendikbudristek No. 55/2024
                    </h3>
                    <p class="text-sm leading-relaxed text-muted-foreground text-justify">
                        Satuan Tugas PPKPT Polban menjalankan amanat regulasi nasional dengan empat fungsi utama.
                    </p>
                    <ul class="flex flex-col gap-2.5">
                        <li v-for="item in [
                            'Pencegahan kekerasan melalui edukasi dan sosialisasi',
                            'Penerimaan dan penanganan laporan secara aman dan rahasia',
                            'Pendampingan korban (psikologis, medis, hukum, akademik)',
                            'Rekomendasi sanksi dan pemulihan kepada pimpinan Polban',
                        ]" :key="item" class="flex items-start gap-2 text-sm text-muted-foreground">
                            <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-500" />
                            {{ item }}
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <!-- Satgas Location Section 4 -->
    <section id="lokasi-satgas" class="bg-gray-50 py-10 md:py-16" style="scroll-snap-align: start;">
        <div class="mx-auto max-w-screen-xl px-4 sm:px-6 md:px-8">

            <!-- Header -->
            <div v-reveal class="mb-8 md:mb-10">
                <h2 class="font-display text-2xl font-extrabold text-foreground sm:text-3xl lg:text-4xl">Temukan Kami</h2>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 lg:gap-8">

                <!-- Contact info -->
                <div v-reveal="'100'" class="flex flex-col gap-3">

                    <!-- Alamat Kantor -->
                    <div class="flex items-center gap-4 rounded-xl border border-border bg-white px-4 py-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-accent/10">
                            <MapPin class="size-5 text-brand-accent" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-foreground">Alamat Kantor</p>
                            <p class="text-sm text-muted-foreground">Gedung P2T, Lantai 1</p>
                            <p class="text-sm text-muted-foreground">Jl. Gegerkalong Hilir, Bandung</p>
                        </div>
                    </div>

                    <!-- Hotline -->
                    <div class="flex items-center gap-4 rounded-xl border border-border bg-white px-4 py-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand/10">
                            <Phone class="size-5 text-brand" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-foreground">Hotline Pengaduan</p>
                            <a href="tel:+622220137890" class="text-sm font-medium text-brand hover:underline">+62 22 2013 789</a>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="flex items-center gap-4 rounded-xl border border-border bg-white px-4 py-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-500/10">
                            <Mail class="size-5 text-violet-500" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-foreground">Email Satgas</p>
                            <a href="mailto:satgas.ppkpt@polban.ac.id" class="text-sm font-medium text-brand hover:underline">
                                ppks@polban.ac.id
                            </a>
                        </div>
                    </div>

                    <!-- Jam Operasional -->
                    <div class="flex items-center gap-4 rounded-xl border border-border bg-white px-4 py-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10">
                            <Clock class="size-5 text-emerald-500" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-foreground">Jam Operasional</p>
                            <p class="text-sm text-muted-foreground">Senin – Jumat · 08.00 – 16.00 WIB</p>
                            <p class="text-xs text-muted-foreground">Disarankan menghubungi terlebih dahulu untuk memastikan ketersediaan</p>
                        </div>
                    </div>

                </div>

                <!-- Map embed -->
                <div v-reveal="'200'" class="overflow-hidden rounded-2xl border border-border shadow-sm lg:col-span-2">
                    <iframe
                        src="https://www.openstreetmap.org/export/embed.html?bbox=107.5709%2C-6.8734%2C107.5769%2C-6.8694&layer=mapnik&marker=-6.871406%2C107.573901"
                        class="h-full min-h-[300px] w-full sm:min-h-[380px]"
                        frameborder="0"
                        allowfullscreen
                        loading="lazy"
                        title="Lokasi Satgas PPKPT Polban"
                    />
                </div>

            </div>
        </div>
    </section>

</template>

<style scoped>
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: opacity 0.5s ease;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
    opacity: 0;
}
</style>