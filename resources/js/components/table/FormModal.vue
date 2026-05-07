<script setup lang="ts">
import { reactive, watch } from 'vue';

const props = defineProps<{
    open: boolean;
    title: string;
    schema: any[];
    data?: any;
}>();

const emit = defineEmits<{
    submit: [data: any];
    close: [];
}>();

const form = reactive<Record<string, any>>({});

function initializeForm() {
    Object.keys(form).forEach((key) => {
        delete form[key];
    });

    props.schema.forEach((field) => {
        form[field.key] = field.type === 'checkbox' ? false : '';
    });

    if (props.data) {
        Object.assign(form, {
            ...props.data,
        });
    }
}

function submit() {
    emit('submit', {
        ...form,
    });
}

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            initializeForm();
        }
    },
    { immediate: true },
);
</script>

<template>
    <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
    >
        <div
            class="w-full max-w-2xl rounded-2xl border border-border bg-background shadow-2xl"
        >
            <!-- HEADER -->
            <div
                class="flex items-center justify-between border-b border-border px-6 py-4"
            >
                <h2 class="text-lg font-semibold text-foreground">
                    {{ title }}
                </h2>

                <button
                    class="rounded-lg p-2 text-muted-foreground transition hover:bg-muted hover:text-foreground"
                    @click="emit('close')"
                >
                    ✕
                </button>
            </div>

            <!-- BODY -->
            <div class="space-y-5 p-6">
                <div v-for="field in schema" :key="field.key" class="space-y-2">
                    <label class="block text-sm font-medium text-foreground">
                        {{ field.label }}
                    </label>

                    <!-- INPUT -->
                    <input
                        v-if="field.type !== 'checkbox'"
                        v-model="form[field.key]"
                        :type="field.type"
                        class="h-11 w-full rounded-xl border border-border bg-background px-4 text-sm text-foreground transition outline-none focus:ring-2 focus:ring-primary"
                    />

                    <!-- CHECKBOX -->
                    <label
                        v-else
                        class="flex items-center gap-3 text-sm text-foreground"
                    >
                        <input
                            v-model="form[field.key]"
                            type="checkbox"
                            class="h-4 w-4 rounded border-border"
                        />

                        <span>
                            {{ field.label }}
                        </span>
                    </label>
                </div>
            </div>

            <!-- FOOTER -->
            <div
                class="flex items-center justify-end gap-3 border-t border-border px-6 py-4"
            >
                <button
                    class="rounded-xl border border-border px-4 py-2 text-sm font-medium text-foreground transition hover:bg-muted"
                    @click="emit('close')"
                >
                    Batal
                </button>

                <button
                    class="rounded-xl bg-primary px-5 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90"
                    @click="submit"
                >
                    Simpan
                </button>
            </div>
        </div>
    </div>
</template>
