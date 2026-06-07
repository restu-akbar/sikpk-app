<script setup lang="ts">
import { ref, reactive, onUnmounted, watch } from 'vue';
import { Mic, Square, Play, Pause, Trash2, MessageCircle } from 'lucide-vue-next';

export interface AudioRecording {
    blob: Blob;
    url: string;
    duration: number;
    label: string;
}

const props = defineProps<{
    modelValue: AudioRecording[];
    error?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: AudioRecording[]];
}>();

type RecorderStatus = 'idle' | 'recording' | 'saved';

const status = ref<RecorderStatus>('idle');
const elapsed = ref(0);
const lastSavedIndex = ref<number | null>(null);

let mediaRecorder: MediaRecorder | null = null;
let chunks: Blob[] = [];
let timerInterval: ReturnType<typeof setInterval> | null = null;
let animationFrameId: number | null = null;
let analyser: AnalyserNode | null = null;
let audioCtx: AudioContext | null = null;

const canvasRef = ref<HTMLCanvasElement | null>(null);
const playingIndex = ref<number | null>(null);
const audioElements = ref<Map<number, HTMLAudioElement>>(new Map());
const playbackProgress = reactive<Record<number, number>>({});
let playbackRafId: number | null = null;

const tickPlayback = () => {
    if (playingIndex.value === null) return;
    const audio = audioElements.value.get(playingIndex.value);
    if (audio && audio.duration) {
        playbackProgress[playingIndex.value] = (audio.currentTime / audio.duration) * 100;
    }
    playbackRafId = requestAnimationFrame(tickPlayback);
};

const stopPlaybackTick = () => {
    if (playbackRafId !== null) {
        cancelAnimationFrame(playbackRafId);
        playbackRafId = null;
    }
};

const formatTime = (seconds: number) => {
    const m = Math.floor(seconds / 60).toString().padStart(2, '0');
    const s = (seconds % 60).toString().padStart(2, '0');
    return `${m}:${s}`;
};

const totalDuration = () =>
    props.modelValue.reduce((sum, r) => sum + r.duration, 0);

const drawWaveform = (dataArray: Uint8Array, bufferLength: number) => {
    const canvas = canvasRef.value;
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    const width = canvas.width;
    const height = canvas.height;
    ctx.clearRect(0, 0, width, height);

    const barWidth = 3;
    const gap = 2;
    const totalBars = Math.floor(width / (barWidth + gap));
    const step = Math.floor(bufferLength / totalBars);

    ctx.fillStyle = 'rgba(255,255,255,0.7)';

    for (let i = 0; i < totalBars; i++) {
        const value = dataArray[i * step] / 128.0;
        const barHeight = Math.max(4, value * (height / 2));
        const x = i * (barWidth + gap);
        const y = (height - barHeight) / 2;
        ctx.beginPath();
        ctx.rect(x, y, barWidth, barHeight);
        ctx.fill();
    }
};

const drawIdleWaveform = () => {
    const canvas = canvasRef.value;
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    const width = canvas.width;
    const height = canvas.height;
    ctx.clearRect(0, 0, width, height);

    const barWidth = 3;
    const gap = 2;
    const totalBars = Math.floor(width / (barWidth + gap));

    ctx.fillStyle = 'rgba(255,255,255,0.2)';
    for (let i = 0; i < totalBars; i++) {
        const barHeight = 4;
        const x = i * (barWidth + gap);
        const y = (height - barHeight) / 2;
        ctx.beginPath();
        ctx.rect(x, y, barWidth, barHeight);
        ctx.fill();
    }
};

const animateWaveform = () => {
    if (!analyser) return;
    const bufferLength = analyser.frequencyBinCount;
    const dataArray = new Uint8Array(bufferLength);

    const draw = () => {
        animationFrameId = requestAnimationFrame(draw);
        analyser!.getByteTimeDomainData(dataArray);
        drawWaveform(dataArray, bufferLength);
    };
    draw();
};

const startRecording = async () => {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });

        audioCtx = new AudioContext();
        analyser = audioCtx.createAnalyser();
        analyser.fftSize = 256;
        const source = audioCtx.createMediaStreamSource(stream);
        source.connect(analyser);

        const preferredTypes = [
            'audio/webm;codecs=opus',
            'audio/webm',
            'audio/mp4',
            'audio/ogg;codecs=opus',
        ];
        const mimeType = preferredTypes.find((t) => MediaRecorder.isTypeSupported(t)) ?? '';

        chunks = [];
        mediaRecorder = new MediaRecorder(stream, mimeType ? { mimeType } : {});
        mediaRecorder.ondataavailable = (e) => {
            if (e.data.size > 0) chunks.push(e.data);
        };
        mediaRecorder.onstop = () => {
            const blob = new Blob(chunks, { type: mimeType || 'audio/webm' });
            const url = URL.createObjectURL(blob);
            const index = props.modelValue.length + 1;
            const recording: AudioRecording = {
                blob,
                url,
                duration: elapsed.value,
                label: `Pesan #${index}`,
            };
            emit('update:modelValue', [...props.modelValue, recording]);
            lastSavedIndex.value = index;
            status.value = 'saved';

            stream.getTracks().forEach((t) => t.stop());
            if (audioCtx) { audioCtx.close(); audioCtx = null; }
            analyser = null;

            if (animationFrameId) { cancelAnimationFrame(animationFrameId); animationFrameId = null; }
            drawIdleWaveform();

            setTimeout(() => {
                if (status.value === 'saved') status.value = 'idle';
            }, 3000);
        };

        mediaRecorder.start();
        status.value = 'recording';
        elapsed.value = 0;
        timerInterval = setInterval(() => { elapsed.value++; }, 1000);
        animateWaveform();
    } catch {
        alert('Tidak dapat mengakses mikrofon. Pastikan izin mikrofon sudah diberikan.');
    }
};

const stopRecording = () => {
    if (timerInterval) { clearInterval(timerInterval); timerInterval = null; }
    mediaRecorder?.stop();
};

const deleteRecording = (index: number) => {
    const audio = audioElements.value.get(index);
    if (audio) { audio.pause(); audioElements.value.delete(index); }
    if (playingIndex.value === index) { stopPlaybackTick(); playingIndex.value = null; }
    delete playbackProgress[index];

    const updated = props.modelValue.filter((_, i) => i !== index).map((r, i) => ({
        ...r,
        label: `Pesan #${i + 1}`,
    }));
    emit('update:modelValue', updated);
};

const togglePlay = (index: number) => {
    if (playingIndex.value === index) {
        audioElements.value.get(index)?.pause();
        stopPlaybackTick();
        playingIndex.value = null;
        return;
    }

    if (playingIndex.value !== null) {
        audioElements.value.get(playingIndex.value)?.pause();
        stopPlaybackTick();
    }

    let audio = audioElements.value.get(index);
    if (!audio) {
        audio = new Audio(props.modelValue[index].url);
        audio.onended = () => {
            stopPlaybackTick();
            playbackProgress[index] = 0;
            playingIndex.value = null;
        };
        audioElements.value.set(index, audio);
    }
    audio.currentTime = 0;
    audio.play();
    playingIndex.value = index;
    playbackRafId = requestAnimationFrame(tickPlayback);
};

watch(canvasRef, (canvas) => {
    if (canvas) drawIdleWaveform();
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
    if (animationFrameId) cancelAnimationFrame(animationFrameId);
    stopPlaybackTick();
    if (audioCtx) audioCtx.close();
    props.modelValue.forEach((r) => URL.revokeObjectURL(r.url));
});
</script>

<template>
    <div>
        <!-- 1. Tip banner — BG #FFF4EA, stroke #F5821F, ikon mic aksen #F5821F -->
        <div
            class="mb-4 flex items-start gap-3 rounded-xl border px-4 py-3"
            style="background-color: #FFF4EA; border-color: #F5821F"
        >
            <div
                class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full"
                style="background-color: #F5821F1A"
            >
                <Mic class="h-4 w-4" style="color: #F5821F" />
            </div>
            <div class="text-xs">
                <p class="font-bold" style="color: #181613">Bisa kirim lebih dari satu pesan suara</p>
                <p class="mt-0.5 leading-relaxed" style="color: #403B34">
                    Anda dapat mengirimkan lebih dari satu pesan suara. Tekan
                    <span class="font-semibold" style="color: #F5821F">Mulai Rekam</span> untuk merekam,
                    lalu <span class="font-semibold" style="color: #F5821F">Berhenti &amp; Simpan</span>.
                    Rekaman akan ditambahkan ke daftar di bawah.
                    Anda dapat menambahkan rekaman baru kapan saja sebelum mengirim laporan.
                </p>
            </div>
        </div>

        <!-- 2. Guide — BG #FDFCFB, stroke #ECE8E2, ikon & list #1A5BA6, teks #403B34 -->
        <div
            class="mb-4 rounded-xl border px-4 py-3"
            style="background-color: #FDFCFB; border-color: #ECE8E2"
        >
            <p class="mb-2 flex items-center gap-1.5 text-xs font-bold" style="color: #1A5BA6">
                <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: #1A5BA6">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z" />
                </svg>
                Panduan: Sertakan informasi berikut dalam rekaman
            </p>
            <div class="grid grid-cols-1 gap-x-6 gap-y-1 text-xs sm:grid-cols-2" style="color: #403B34">
                <ul class="space-y-1 pl-4" style="list-style: disc; list-style-color: #1A5BA6">
                    <li><span class="font-bold">Waktu dan tempat</span> : kapan dan di mana kejadian terjadi</li>
                    <li><span class="font-bold">Kronologi kejadian</span> : ceritakan secara urut sesuai ingatan Anda</li>
                </ul>
                <ul class="space-y-1 pl-4" style="list-style: disc; list-style-color: #1A5BA6">
                    <li><span class="font-bold">Pihak yang terlibat</span> : pihak yang melakukan, dan apakah ada yang menyaksikan</li>
                    <li><span class="font-bold">Dampak yang dirasakan</span> : baik secara fisik, emosional, maupun akademik</li>
                </ul>
            </div>
        </div>

        <!-- 3. Recorder Box — gradient sama seperti landing section 2 -->
        <div
            class="relative overflow-hidden rounded-2xl px-6 py-5"
            style="background: linear-gradient(165deg, #1A5BA6 0%, #0B2A4F 68%)"
        >
            <!-- Status badge — dibungkus pill dengan bg white/10 dan stroke white/20 -->
            <div class="mb-3 flex items-center justify-between">
                <div
                    class="flex items-center gap-2 rounded-full border px-3 py-1"
                    style="background-color: rgba(255,255,255,0.10); border-color: rgba(255,255,255,0.20)"
                >
                    <span
                        class="h-1.5 w-1.5 rounded-full"
                        :class="status === 'recording' ? 'animate-pulse' : ''"
                        :style="status === 'recording' ? 'background-color: #F5821F' : 'background-color: rgba(255,255,255,0.5)'"
                    />
                    <span
                        class="text-xs font-semibold uppercase tracking-widest"
                        :style="status === 'recording' ? 'color: #F5821F' : 'color: white'"
                    >
                        {{ status === 'recording' ? 'Sedang Merekam' : 'Siap Merekam' }}
                    </span>
                </div>
                <span class="font-mono text-sm font-semibold" style="color: rgba(255,255,255,0.8)">
                    {{ formatTime(status === 'recording' ? elapsed : 0) }}
                </span>
            </div>

            <!-- Waveform canvas -->
            <canvas
                ref="canvasRef"
                width="600"
                height="56"
                class="mb-5 w-full"
                style="height: 56px; will-change: contents"
            />

            <!-- Button & hint -->
            <div class="flex flex-col items-center gap-3">
                <!-- 4. Tombol Mulai Rekam — lingkaran, ikon mic saja -->
                <button
                    v-if="status !== 'recording'"
                    type="button"
                    @click="startRecording"
                    class="flex h-12 w-12 items-center justify-center rounded-full bg-white shadow transition-all hover:bg-blue-50"
                    title="Mulai Rekam"
                >
                    <Mic class="h-5 w-5" style="color: #1A5BA6" />
                </button>
                <button
                    v-else
                    type="button"
                    @click="stopRecording"
                    class="flex items-center gap-2 rounded-full px-7 py-2.5 text-sm font-semibold text-white shadow transition-all"
                    style="background-color: #F5821F"
                >
                    <Square class="h-4 w-4 fill-white" />
                    Berhenti &amp; Simpan
                </button>

                <p class="max-w-sm text-center text-xs leading-relaxed" style="color: rgba(255,255,255,0.6)">
                    <template v-if="status === 'recording'">
                        Bicara dengan tenang dan tidak perlu terburu-buru. Tekan
                        <span class="font-semibold" style="color: rgba(255,255,255,0.9)">Berhenti &amp; Simpan</span> jika sudah selesai.
                    </template>
                    <template v-else-if="status === 'saved'">
                        <span class="font-semibold" style="color: #86efac">Pesan #{{ lastSavedIndex }} tersimpan</span>
                        — Anda dapat menambahkan rekaman baru atau lanjut ke konfirmasi.
                    </template>
                    <template v-else>
                        Tekan
                        <span class="font-semibold" style="color: rgba(255,255,255,0.9)">Mulai Rekam</span>
                        untuk mulai merekam kronologi Anda.
                    </template>
                </p>
            </div>
        </div>

        <!-- 5 & 6. Saved recordings list -->
        <div v-if="modelValue.length > 0" class="mt-4">
            <!-- Header — ikon chat #1A5BA6, judul #181613 bold, badge bg #EDF3FB teks #0F3A6C -->
            <div class="mb-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <MessageCircle class="h-4 w-4" style="color: #1A5BA6" />
                    <span class="text-sm font-bold" style="color: #181613">Pesan Suara Anda</span>
                    <span
                        class="rounded-full px-2 py-0.5 text-xs font-bold"
                        style="background-color: #EDF3FB; color: #0F3A6C"
                    >
                        {{ modelValue.length }} pesan
                    </span>
                </div>
                <span class="text-xs" style="color: #847B6E">Total: {{ formatTime(totalDuration()) }}</span>
            </div>

            <!-- List items — spacing gap-2, no bg on container -->
            <div class="flex flex-col gap-2">
                <div
                    v-for="(rec, i) in modelValue"
                    :key="i"
                    class="flex items-center gap-3 rounded-xl border px-3 py-2.5"
                    style="border-color: #ECE8E2; background-color: #ffffff"
                >
                    <!-- Nomor — bg #FDFCFB, teks #847B6E bold -->
                    <span
                        class="flex h-8 w-10 shrink-0 items-center justify-center rounded-lg text-xs font-bold"
                        style="background-color: #FDFCFB; color: #847B6E; border: 1px solid #ECE8E2"
                    >
                        #{{ String(i + 1).padStart(2, '0') }}
                    </span>

                    <!-- Play button — ikon warna #0F3A6C -->
                    <button
                        type="button"
                        @click="togglePlay(i)"
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full transition hover:opacity-80"
                        style="background-color: #EDF3FB"
                    >
                        <Pause v-if="playingIndex === i" class="h-3.5 w-3.5" style="color: #0F3A6C" />
                        <Play v-else class="h-3.5 w-3.5 translate-x-0.5" style="color: #0F3A6C" />
                    </button>

                    <!-- Progress bar -->
                    <div class="min-w-0 flex-1">
                        <div class="h-1.5 w-full rounded-full overflow-hidden" style="background-color: #C7D8EF">
                            <div
                                class="h-full rounded-full"
                                style="background-color: #1A5BA6; will-change: transform; transform-origin: left"
                                :style="{ transform: `scaleX(${(playbackProgress[i] ?? 0) / 100})` }"
                            />
                        </div>
                    </div>

                    <!-- Durasi — teks #403B34 bold -->
                    <span class="shrink-0 text-xs font-bold" style="color: #403B34">
                        {{ formatTime(rec.duration) }}
                    </span>

                    <!-- Delete — ikon #847B6E, stroke #ECE8E2 -->
                    <button
                        type="button"
                        @click="deleteRecording(i)"
                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border transition hover:bg-red-50"
                        style="border-color: #ECE8E2; color: #847B6E"
                        @mouseenter="($event.currentTarget as HTMLElement).style.color = '#ef4444'"
                        @mouseleave="($event.currentTarget as HTMLElement).style.color = '#847B6E'"
                    >
                        <Trash2 class="h-3.5 w-3.5" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Error -->
        <p v-if="error" class="mt-2 text-xs text-red-500">{{ error }}</p>
    </div>
</template>
