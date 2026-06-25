<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { LockKeyhole } from 'lucide-vue-next';
import DialogFooter from '@/components/DialogFooter.vue';

const props = defineProps<{
    open: boolean;
    loading?: boolean;
    error?: string;
}>();

const emit = defineEmits<{
    submit: [password: string];
    cancel: [];
}>();

const password = ref('');

watch(
    () => props.open,
    (value) => {
        if (value) {
            password.value = '';
        }
    },
);

function handleSubmit() {
    if (!password.value.trim()) return;

    emit('submit', password.value);
}

const actionLabel = computed(() =>
    props.loading ? 'Membuka kunci...' : 'Buka Kunci',
);
</script>

<template>
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="open"
                class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
            >
                <div
                    class="w-full max-w-md overflow-hidden rounded-xl border border-border bg-background shadow-2xl"
                >
                    <!-- Header -->
                    <div class="border-b border-border px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-orange-100"
                            >
                                <LockKeyhole class="h-5 w-5 text-orange-600" />
                            </div>

                            <div>
                                <h2 class="text-lg font-semibold">
                                    Kata Sandi
                                </h2>

                                <p class="mt-1 text-sm text-muted-foreground">
                                    Masukkan kata sandi untuk memuat kunci enkripsi.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="space-y-4 px-6 py-5">
                        <input
                            type="text"
                            autocomplete="username"
                            tabindex="-1"
                            class="pointer-events-none absolute h-0 w-0 opacity-0"
                        />

                        <input
                            v-model="password"
                            type="password"
                            autocomplete="current-password"
                            placeholder="Masukkan kata sandi"
                            class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm"
                            autofocus
                            @keyup.enter="handleSubmit"
                        />

                        <p v-if="error" class="text-sm text-red-500">
                            {{ error }}
                        </p>
                    </div>

                    <!-- Footer -->
                    <DialogFooter
                        back-label="Batal"
                        :back-disabled="loading"
                        :action-label="actionLabel"
                        :action-disabled="loading || !password"
                        action-variant="primary"
                        @back="emit('cancel')"
                        @action="handleSubmit"
                    />
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
