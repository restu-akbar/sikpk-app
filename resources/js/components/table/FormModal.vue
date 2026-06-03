<script setup lang="ts">
import { reactive, watch, ref, nextTick } from 'vue';
import FormField from '@/components/form/FormField.vue';
import DropdownField from '@/components/form/DropdownField.vue';

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
const errors = reactive<Record<string, string>>({});
const isInitializing = ref(false);

function initializeForm() {
    isInitializing.value = true;

    Object.keys(form).forEach((key) => delete form[key]);
    Object.keys(errors).forEach((key) => delete errors[key]);

    props.schema.forEach((field) => {
        form[field.key] = field.type === 'checkbox' ? false : '';
    });

    if (props.data) {
        Object.assign(form, { ...props.data });
    }

    nextTick(() => { isInitializing.value = false; });
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

// Reset field dependen saat parent berubah — dinamis berdasarkan schema filteredBy
watch(form, (_, oldForm) => {
    if (isInitializing.value) return;

    props.schema.forEach((field) => {
        if (field.filteredBy && form[field.filteredBy] !== oldForm[field.filteredBy]) {
            form[field.key] = '';
        }
        // Hapus error saat field diisi
        if (errors[field.key] && form[field.key]) {
            delete errors[field.key];
        }
    });
});

function validate(): boolean {
    Object.keys(errors).forEach((key) => delete errors[key]);

    props.schema.forEach((field) => {
        if (!field.required) return;
        const value = form[field.key];
        if (value === '' || value === null || value === undefined) {
            errors[field.key] = `${field.label} wajib diisi`;
        }
    });

    return Object.keys(errors).length === 0;
}

function submit() {
    if (!validate()) return;
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
        <div class="w-full max-w-2xl rounded-2xl border border-border bg-background shadow-2xl">

            <!-- HEADER -->
            <div class="flex items-start justify-between border-b border-border px-6 py-2">
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
                    :class="field.span === 'half' ? 'col-span-1' : 'col-span-2'"
                >
                    <!-- SELECT -->
                    <DropdownField
                        v-if="field.type === 'select'"
                        :label="field.label"
                        :required="field.required"
                        :options="getOptions(field)"
                        :model-value="form[field.key]"
                        :placeholder="field.placeholder"
                        :error="errors[field.key]"
                        @update:model-value="form[field.key] = $event"
                    />

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
                    <FormField
                        v-else
                        :label="field.label"
                        :required="field.required"
                        :model-value="form[field.key]"
                        :type="field.type"
                        :placeholder="field.placeholder ?? ''"
                        :error="errors[field.key]"
                        @update:model-value="form[field.key] = $event"
                    />
                </div>
            </div>

            <!-- FOOTER -->
            <div class="flex items-center justify-between rounded-b-2xl border-t border-border bg-surface px-6 py-4">
                <!-- Batal — kiri -->
                <button
                    class="rounded-xl border border-border bg-background px-5 py-2 text-sm font-medium text-foreground transition hover:bg-muted"
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
