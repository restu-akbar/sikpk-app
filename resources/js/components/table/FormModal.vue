<script setup lang="ts">
import { reactive, watch, computed } from 'vue';

const props = defineProps<{
    open: boolean;
    title: string;
    description?: string;
    submitLabel?: string;
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
        Object.assign(form, { ...props.data });
    }
}

// Kembalikan opsi yang sudah difilter berdasarkan field lain (cascading select)
function getOptions(field: any) {
    if (!field.filteredBy || !form[field.filteredBy]) {
        return field.options ?? [];
    }
    return (field.options ?? []).filter(
        (opt: any) => opt[field.filteredBy] == form[field.filteredBy],
    );
}

// Reset nilai field dependen saat parent berubah
watch(
    () => form['department_id'],
    () => { form['study_program_id'] = ''; },
);

function submit() {
    emit('submit', { ...form });
}

watch(
    () => props.open,
    (isOpen) => { if (isOpen) initializeForm(); },
    { immediate: true },
);
</script>

<template>
    <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
        @click.self="emit('close')"
    >
        <div class="w-full max-w-2xl rounded-2xl border border-nav-stroke bg-background shadow-2xl">

            <!-- HEADER -->
            <div class="flex items-start justify-between border-b border-nav-stroke px-6 py-2">
                <div>
                    <h2 class="text-lg font-semibold text-foreground">{{ title }}</h2>
                    <p v-if="description" class="mt-0.5 text-sm text-muted-foreground">
                        {{ description }}
                    </p>
                </div>
                <button
                    class="rounded-lg p-2 text-muted-foreground transition hover:bg-muted hover:text-foreground"
                    @click="emit('close')"
                >
                    ✕
                </button>
            </div>

            <!-- BODY -->
            <div class="grid grid-cols-2 gap-x-4 gap-y-5 p-6">
                <div
                    v-for="field in schema"
                    :key="field.key"
                    class="space-y-1.5"
                    :class="field.span === 'half' ? 'col-span-1' : 'col-span-2'"
                >
                    <!-- Label -->
                    <label class="flex items-center gap-1 text-sm font-medium text-foreground">
                        {{ field.label }}
                        <span v-if="field.required" class="text-[#F5821F]">*</span>
                    </label>

                    <!-- SELECT -->
                    <select
                        v-if="field.type === 'select'"
                        v-model="form[field.key]"
                        class="h-11 w-full rounded-xl border border-nav-stroke bg-background px-4 text-sm text-foreground outline-none transition focus:border-[#F5821F] focus:ring-2 focus:ring-[#F5821F]/20"
                    >
                        <option
                            v-for="opt in getOptions(field)"
                            :key="opt.value"
                            :value="opt.value"
                        >
                            {{ opt.label }}
                        </option>
                    </select>

                    <!-- CHECKBOX -->
                    <label
                        v-else-if="field.type === 'checkbox'"
                        class="flex items-center gap-3 text-sm text-foreground"
                    >
                        <input
                            v-model="form[field.key]"
                            type="checkbox"
                            class="h-4 w-4 rounded border-nav-stroke"
                        />
                        <span>{{ field.label }}</span>
                    </label>

                    <!-- TEXT / EMAIL / NUMBER / dll -->
                    <input
                        v-else
                        v-model="form[field.key]"
                        :type="field.type"
                        :placeholder="field.placeholder ?? ''"
                        class="h-11 w-full rounded-xl border border-nav-stroke bg-background px-4 text-sm text-foreground outline-none transition focus:border-[#F5821F] focus:ring-2 focus:ring-[#F5821F]/20"
                    />
                </div>
            </div>

            <!-- FOOTER -->
            <div class="flex items-center justify-between rounded-b-2xl border-t border-nav-stroke bg-[#FBF9F5] px-6 py-4">
                <!-- Batal — kiri -->
                <button
                    class="rounded-xl border border-nav-stroke bg-white px-5 py-2 text-sm font-medium text-foreground transition hover:bg-muted"
                    @click="emit('close')"
                >
                    Batal
                </button>

                <!-- Simpan — kanan -->
                <button
                    class="inline-flex items-center gap-2 rounded-xl bg-[#F5821F] px-5 py-2 text-sm font-medium text-white transition hover:opacity-90"
                    @click="submit"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    {{ submitLabel || 'Simpan' }}
                </button>
            </div>
        </div>
    </div>
</template>
