<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Lock, EyeOff, ShieldCheck, MapPin, Phone, Mail, Clock, ArrowRight } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { create } from '@/routes/reports';

// ─── Hero Slider ─────────────────────────────────────────────────────────────

interface Slide {
    image: string;
    badge: string;
    caption: string;
}

const slides: Slide[] = [
    {
        image: '/images/landing/slide-1.jpg',
        badge: 'Sosialisasi PPK',
        caption: 'Bersama membangun Polban yang aman dan bebas kekerasan',
    },
    {
        image: '/images/landing/slide-2.jpg',
        badge: 'Pendampingan Korban',
        caption: 'Setiap pelapor mendapat pendampingan penuh dari tim profesional',
    },
    {
        image: '/images/landing/slide-3.jpg',
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

onMounted(startAuto);
onUnmounted(stopAuto);
</script>

<template>
    <Head title="Beranda" />

    <!-- Hero Section 1 -->
    <section class="mx-auto max-w-screen-xl px-8 py-16">
        <div class="grid grid-cols-2 items-center gap-12">

            <!-- Left: text content -->
            <div class="flex flex-col gap-6">

                <!-- Heading -->
                <h1 class="font-display text-5xl font-extrabold leading-tight tracking-tight text-foreground">
                    Anda <span class="text-brand">tidak sendirian.</span><br />
                    Laporan Anda kami<br />dengar.
                </h1>

                <!-- Description -->
                <p class="text-base leading-relaxed text-muted-foreground text-justify">
                    Di SIKPK, setiap suara berharga. Sebagai kanal resmi pelaporan
                    dan penanganan kekerasan di lingkungan Politeknik Negeri Bandung,
                    kami hadir untuk mendengarkan, menjaga kerahasiaan identitas Anda,
                    serta memberikan perlindungan dan pendampingan.
                </p>

                <!-- CTAs -->
                <div class="flex items-center gap-3">
                    <Button as-child variant="brand-accent" size="lg" class="font-semibold">
                        <Link :href="create().url">
                            Buat Laporan Sekarang
                            <ArrowRight class="ml-1 size-4" />
                        </Link>
                    </Button>
                    <Button variant="outline" size="lg" class="font-semibold">
                        Lacak Kasus Anda
                    </Button>
                </div>

                <!-- Stats -->
                <div class="flex items-center gap-8 border-t border-border pt-6">
                    <div>
                        <p class="font-display text-2xl font-bold text-[#0F3A6C]">100%</p>
                        <p class="text-xs text-muted-foreground">Identitas dilindungi</p>
                    </div>
                    <div class="h-8 w-px bg-border" />
                    <div>
                        <p class="font-display text-2xl font-bold text-[#0F3A6C]">&lt; 24 Jam</p>
                        <p class="text-xs text-muted-foreground">Respons pertama</p>
                    </div>
                    <div class="h-8 w-px bg-border" />
                    <div>
                        <p class="font-display text-2xl font-bold text-[#0F3A6C]">Anggota Satgas</p>
                        <p class="text-xs text-muted-foreground">Pendamping profesional</p>
                    </div>
                </div>
            </div>

            <!-- Right: image slider -->
            <div class="relative overflow-hidden rounded-2xl shadow-xl"
                 @mouseenter="stopAuto"
                 @mouseleave="startAuto">

                <!-- Slides -->
                <div class="relative aspect-square">
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
                            <div class="absolute bottom-0 left-0 right-0 px-5 pb-10 pt-5">
                                <span class="mb-2 inline-block rounded-full bg-brand-accent px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider text-white">
                                    {{ slide.badge }}
                                </span>
                                <p class="font-display text-base font-bold leading-snug text-white">
                                    {{ slide.caption }}
                                </p>
                            </div>
                        </div>
                    </transition-group>
                </div>

                <!-- Logo overlay — selalu tampil di atas semua slide -->
                <div class="absolute left-0 right-0 top-0 flex items-center gap-3 p-5">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full border border-white/30 bg-white/[0.14] backdrop-blur-sm">
                        <AppLogoIcon class="size-8 object-contain" />
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span class="font-display text-sm font-bold leading-none text-white drop-shadow">
                            Satuan Tugas PPK
                        </span>
                        <span class="font-display text-xs leading-none text-white/80 drop-shadow">
                            Politeknik Negeri Bandung
                        </span>
                    </div>
                </div>

                <!-- Prev / Next arrows -->
                <button
                    class="absolute left-3 top-1/2 -translate-y-1/2 flex h-8 w-8 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm transition hover:bg-white/40"
                    @click="prevSlide"
                    aria-label="Slide sebelumnya"
                >
                    <ChevronLeft class="size-5 text-white" />
                </button>
                <button
                    class="absolute right-3 top-1/2 -translate-y-1/2 flex h-8 w-8 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm transition hover:bg-white/40"
                    @click="nextSlide"
                    aria-label="Slide berikutnya"
                >
                    <ChevronRight class="size-5 text-white" />
                </button>

                <!-- Dot indicators -->
                <div class="absolute bottom-4 right-4 flex gap-1.5">
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
    </section>

    <!-- Access Information Section 2 -->
    <section
        class="py-20"
        style="background: linear-gradient(165deg, #1A5BA6 0%, #0B2A4F 68%);"
    >
        <div class="mx-auto max-w-screen-xl px-8">

            <!-- Header -->
            <div class="mb-12 max-w-lg">
                <h2 class="font-display text-4xl font-extrabold leading-tight text-white">
                    Kerahasiaan adalah<br />
                    <span class="text-brand-accent">Prioritas Utama Kami</span>
                </h2>
                <p class="mt-4 text-sm leading-relaxed text-white/70 text-justify">
                    Setiap informasi yang Anda bagikan dilindungi dengan sistem keamanan berlapis.
                    Tidak ada satu pihak pun yang dapat mengakses identitas Anda tanpa izin.
                </p>
            </div>

            <!-- Feature cards -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

                <!-- Card: Enkripsi -->
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur-sm">
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-[#F5821F]/[0.16]">
                        <Lock class="size-5 text-[#FBA661]" />
                    </div>
                    <h3 class="mb-2 font-display text-base font-bold text-white">Enkripsi End-to-End</h3>
                    <p class="text-sm leading-relaxed text-white/60 text-justify">
                        Seluruh bukti dan dokumen yang Anda kirimkan akan diamankan terlebih dahulu sebelum disimpan ke dalam sistem. 
                        Tidak ada pihak lain yang dapat membaca isi laporan Anda.
                    </p>
                    <div class="mt-4">
                        <span class="inline-block rounded-full bg-[#F5821F]/[0.16] px-3 py-1 text-[11px] font-semibold text-[#FBA661]">
                            Enkripsi
                        </span>
                    </div>
                </div>

                <!-- Card: Anonimitas -->
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur-sm">
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-[#F5821F]/[0.16]">
                        <EyeOff class="size-5 text-[#FBA661]" />
                    </div>
                    <h3 class="mb-2 font-display text-base font-bold text-white">Anonimitas Terjaga</h3>
                    <p class="text-sm leading-relaxed text-white/60 text-justify">
                        Identitas dan informasi yang Anda kirimkan tidak dapat diakses oleh pihak yang tidak berkepentingan.
                        Kerahasiaan Informasi Anda adalah prioritas kami.
                    </p>
                    <div class="mt-4">
                        <span class="inline-block rounded-full bg-[#F5821F]/[0.16] px-3 py-1 text-[11px] font-semibold text-[#FBA661]">
                            Zero Identity Disclosure
                        </span>
                    </div>
                </div>

                <!-- Card: Akses Terbatas -->
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur-sm">
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-[#F5821F]/[0.16]">
                        <ShieldCheck class="size-5 text-[#FBA661]" />
                    </div>
                    <h3 class="mb-2 font-display text-base font-bold text-white">Akses Data Terbatas</h3>
                    <p class="text-sm leading-relaxed text-white/60 text-justify">
                        Hanya anggota Satgas yang ditugaskan yang dapat melihat laporan Anda. Laporan tidak dapat diakses oleh pihak di luar tim penanganan.
                        Setiap progress penanganan akan tercatat dan dapat ditinjau.
                    </p>
                    <div class="mt-4">
                        <span class="inline-block rounded-full bg-[#F5821F]/[0.16] px-3 py-1 text-[11px] font-semibold text-[#FBA661]">
                            Role-Based Access
                        </span>
                    </div>
                </div>

            </div>

            <!-- Legal banner -->
            <div class="mt-6 flex items-start gap-4 rounded-2xl border border-white/10 bg-white/5 px-6 py-5 backdrop-blur-sm">
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
    <section class="py-20" style="background-color: #FDFCFB;">
        <div class="mx-auto max-w-screen-xl px-8">

            <!-- Header -->
            <div class="mb-14 text-center">
                <h2 class="font-display text-4xl font-extrabold leading-tight text-foreground">
                    Sebelum Anda melaporkan,<br />perhatikan hal berikut
                </h2>
                <p class="mx-auto mt-4 max-w-md text-sm leading-relaxed text-muted-foreground">
                    Beberapa informasi ringkas untuk membantu Anda menyiapkan laporan yang akurat dan lengkap.
                </p>
            </div>

            <!-- 3 columns -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">

                <!-- Col 1 — Suara Anda Berarti -->
                <div class="flex flex-col gap-4 rounded-2xl bg-white p-6 shadow-sm">
                    <span class="inline-flex w-fit items-center rounded-full border border-brand/20 bg-brand/10 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-brand">
                        Suara Anda Berarti
                    </span>
                    <h3 class="font-display text-xl font-bold leading-snug text-foreground">
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
                <div class="flex flex-col gap-4 rounded-2xl bg-white p-6 shadow-sm">
                    <span class="inline-flex w-fit items-center rounded-full border border-brand-accent/20 bg-brand-accent/10 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-brand-accent">
                        Cakupan Laporan
                    </span>
                    <h3 class="font-display text-xl font-bold leading-snug text-foreground">
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
                <div class="flex flex-col gap-4 rounded-2xl bg-white p-6 shadow-sm">
                    <span class="inline-flex w-fit items-center rounded-full border border-emerald-600/20 bg-emerald-500/10 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-emerald-700">
                        Peran Satgas PPKPT
                    </span>
                    <h3 class="font-display text-xl font-bold leading-snug text-foreground">
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
    <section class="bg-gray-50 py-20">
        <div class="mx-auto max-w-screen-xl px-8">

            <!-- Header -->
            <div class="mb-10">
                <h2 class="font-display text-4xl font-extrabold text-foreground">Temukan Kami</h2>
            </div>

            <div class="grid grid-cols-3 gap-8">

                <!-- Contact info -->
                <div class="flex flex-col gap-3">

                    <!-- Alamat Kantor -->
                    <div class="flex items-center gap-4 rounded-xl border border-border bg-white px-4 py-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-accent/10">
                            <MapPin class="size-5 text-brand-accent" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-foreground">Alamat Kantor</p>
                            <p class="text-sm text-muted-foreground">Gedung P2T, Lantai 1</p>
                            <p class="text-sm text-muted-foreground">Jl. Gegerkalong Hilir No.147, Bandung</p>
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
                            <p class="text-xs text-muted-foreground">Senin – Jumat · 08.00 – 16.00 WIB</p>
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
                                satgas.ppkpt@polban.ac.id
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
                            <p class="text-xs text-muted-foreground">Pelaporan online: 24 jam</p>
                        </div>
                    </div>

                </div>

                <!-- Map embed — col-span-2 agar 1:2 dengan kolom info -->
                <div class="col-span-2 overflow-hidden rounded-2xl border border-border shadow-sm">
                    <iframe
                        src="https://www.openstreetmap.org/export/embed.html?bbox=107.5673%2C-6.8748%2C107.5713%2C-6.8718&layer=mapnik&marker=-6.8733%2C107.5693"
                        class="h-full min-h-[380px] w-full"
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
