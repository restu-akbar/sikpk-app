<script setup lang="ts">
import { reactive, watch, ref, nextTick } from 'vue';
import FormField from '@/components/form/FormField.vue';
import DropdownField from '@/components/form/DropdownField.vue';
import DialogFooter from '@/components/DialogFooter.vue';
import { Check } from 'lucide-vue-next';

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

function resolve(value: any) {
    return typeof value === 'function' ? value(form) : value;
}

function resolveLabel(field: any) {
    return resolve(field.label);
}

function resolveType(field: any) {
    return resolve(field.type);
}

function resolvePlaceholder(field: any) {
    return resolve(field.placeholder);
}

function isFieldHidden(field: any) {
    return !!resolve(field.hidden);
}

function isFieldDisabled(field: any) {
    return !!resolve(field.disabled);
}

function getOptions(field: any) {
    const options = resolve(field.options) ?? [];
    if (!field.filteredBy || !form[field.filteredBy]) {
        return options;
    }
    return options.filter(
        (opt: any) => opt[field.filteredBy] == form[field.filteredBy],
    );
}

watch(form, (_, oldForm) => {
    if (isInitializing.value) return;

    props.schema.forEach((field) => {
        const dependsOn = [field.filteredBy, ...(field.resetOn ?? [])].filter(Boolean);
        const dependencyChanged = dependsOn.some(
            (dep) => form[dep] !== oldForm[dep],
        );

        if (dependencyChanged || (isFieldHidden(field) && form[field.key])) {
            form[field.key] = resolveType(field) === 'checkbox' ? false : '';
        }

        if (errors[field.key] && form[field.key]) {
            delete errors[field.key];
        }
    });
});

function validate(): boolean {
    Object.keys(errors).forEach((key) => delete errors[key]);

    props.schema.forEach((field) => {
        if (!field.required || isFieldHidden(field)) return;
        const value = form[field.key];
        if (value === '' || value === null || value === undefined) {
            errors[field.key] = `${resolveLabel(field)} wajib diisi`;
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
                    v-show="!isFieldHidden(field)"
                    :key="field.key"
                    :class="field.span === 'half' ? 'col-span-1' : 'col-span-2'"
                >
                    <!-- SELECT -->
                    <DropdownField
                        v-if="resolveType(field) === 'select'"
                        :label="resolveLabel(field)"
                        :required="field.required"
                        :options="getOptions(field)"
                        :model-value="form[field.key]"
                        :placeholder="resolvePlaceholder(field)"
                        :error="errors[field.key]"
                        :disabled="isFieldDisabled(field)"
                        @update:model-value="form[field.key] = $event"
                    />

                    <!-- CHECKBOX -->
                    <label
                        v-else-if="resolveType(field) === 'checkbox'"
                        class="flex items-center gap-3 text-sm text-foreground"
                    >
                        <input
                            v-model="form[field.key]"
                            type="checkbox"
                            class="h-4 w-4 rounded border-nav-stroke"
                            :disabled="isFieldDisabled(field)"
                        />
                        <span>{{ resolveLabel(field) }}</span>
                    </label>

                    <!-- TEXT / EMAIL / NUMBER / dll -->
                    <FormField
                        v-else
                        :label="resolveLabel(field)"
                        :required="field.required"
                        :model-value="form[field.key]"
                        :type="resolveType(field)"
                        :placeholder="resolvePlaceholder(field) ?? ''"
                        :error="errors[field.key]"
                        :disabled="isFieldDisabled(field)"
                        @update:model-value="form[field.key] = $event"
                    />
                </div>
            </div>

            <!-- FOOTER -->
            <div class="rounded-b-2xl bg-surface">
                <DialogFooter
                    back-label="Batal"
                    :action-label="submitLabel || 'Simpan'"
                    :action-icon="Check"
                    @back="emit('close')"
                    @action="submit"
                />
            </div>
        </div>
    </div>
</template>
