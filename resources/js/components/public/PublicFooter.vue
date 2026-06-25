<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';
</script>

<template>
    <footer class="bg-brand-dark text-white">

        <!-- Main footer content -->
        <div class="mx-auto max-w-screen-xl px-4 py-10 sm:px-6 md:px-8 md:py-14">
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4 lg:gap-12">

                <!-- Brand column -->
                <div class="flex flex-col gap-4">
                    <Link :href="home()" class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-lg">
                            <AppLogoIcon class="size-9" />
                        </div>
                        <div class="flex flex-col leading-none">
                            <span class="text-sm font-bold tracking-tight text-white">SIKPK</span>
                            <span class="text-[10px] font-medium tracking-widest text-white/60">POLBAN</span>
                        </div>
                    </Link>
                    <p class="text-sm leading-relaxed text-white/65">
                        Sistem Informasi dan Penanganan Kekerasan di Lingkungan Politeknik Negeri Bandung.
                        Aman, rahasia, profesional — kanal resmi pelaporan untuk seluruh civitas akademika.
                    </p>
                </div>

                <!-- Pelaporan links -->
                <div class="flex flex-col gap-4">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-white/50">Pelaporan</h3>
                    <ul class="flex flex-col gap-3">
                        <li>
                            <a href="#" class="text-sm text-white/80 transition-colors hover:text-white">Buat Laporan</a>
                        </li>
                        <li>
                            <a href="#" class="text-sm text-white/80 transition-colors hover:text-white">Lacak Kasus</a>
                        </li>
                        <li>
                            <a href="#" class="text-sm text-white/80 transition-colors hover:text-white">Informasi Penting</a>
                        </li>
                        <li>
                            <a href="#" class="text-sm text-white/80 transition-colors hover:text-white">Lokasi Satgas</a>
                        </li>
                    </ul>
                </div>

                <!-- Sosial media -->
<div class="flex flex-col gap-4">
    <h3 class="text-xs font-bold uppercase tracking-widest text-white/50">Sosial Media</h3>
    <ul class="flex flex-col gap-4">

        <!-- Instagram -->
        <li class="flex items-center gap-3">
            <a href="https://www.instagram.com/ppkspolban/" target="_blank" rel="noopener noreferrer"
               class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-[#f9ce34] via-[#ee2a7b] to-[#6228d7]">
                <svg class="size-5" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
            </a>
            <div>
                <p class="text-sm font-semibold text-white">Instagram</p>
                <p class="text-xs text-white/55">@ppkspolban</p>
            </div>
        </li>

        <!-- Youtube -->
        <li class="flex items-center gap-3">
            <a href="https://www.youtube.com/@ppkspolban" target="_blank" rel="noopener noreferrer"
               class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#FF0000]">
                <svg class="size-5" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                </svg>
            </a>
            <div>
                <p class="text-sm font-semibold text-white">Youtube</p>
                <p class="text-xs text-white/55">ppks polban</p>
            </div>
        </li>

        <!-- WhatsApp -->
        <li class="flex items-center gap-3">
            <a href="https://wa.me/6285171047293" target="_blank" rel="noopener noreferrer"
               class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#25D366]">
                <svg class="size-5" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
            </a>
            <div>
                <p class="text-sm font-semibold text-white">WhatsApp</p>
                <p class="text-xs text-white/55">Hotline Satgas</p>
            </div>
        </li>

    </ul>
</div>

                <!-- Kontak darurat -->
                <div class="flex flex-col gap-4">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-white/50">Kontak Darurat</h3>
                    <ul class="flex flex-col gap-5">
                        <li>
                            <p class="text-sm font-semibold text-white">Satpam Polban</p>
                            <p class="mt-0.5 text-sm text-white/65">+62 859-2436-2219</p>
                        </li>
                        <li>
                            <p class="text-sm font-semibold text-white">Hotline 24 Jam</p>
                            <p class="mt-0.5 text-sm text-white/65">+62 812-3456-7890</p>
                        </li>
                        <li>
                            <p class="text-sm font-semibold text-white">Email Satgas</p>
                            <p class="mt-0.5 text-sm text-white/65">ppks@polban.ac.id</p>
                        </li>
                    </ul>
                </div>

            </div>
        </div>

        <!-- Bottom bar -->
        <div class="border-t border-white/10">
            <div class="mx-auto flex max-w-screen-xl items-center justify-between px-4 py-4 sm:px-6 md:px-8">
                <p class="text-xs text-white/40">
                    &copy; {{ new Date().getFullYear() }} Politeknik Negeri Bandung &ndash; Satgas PPK
                </p>
                <p class="text-xs text-white/40">v1.0</p>
            </div>
        </div>

    </footer>
</template>
