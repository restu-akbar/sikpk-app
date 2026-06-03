import { computed, type Ref } from 'vue';

type InputVariant =
    | 'input'
    | 'select'
    | 'textarea'
    | 'checkbox'
    | 'radio'
    | 'button';

const baseClasses: Record<InputVariant, string> = {
    input: 'w-full rounded-lg border px-3.5 py-2.5 text-sm transition-all outline-none bg-background text-foreground',
    select: 'w-full rounded-lg border px-3.5 py-2.5 text-sm transition-all appearance-none outline-none bg-background text-foreground',
    textarea:
        'w-full rounded-lg border px-3.5 py-2.5 text-sm transition-all resize-y min-h-[100px] outline-none bg-background text-foreground',
    checkbox: 'rounded border w-4 h-4 cursor-pointer',
    radio: 'rounded-full border w-4 h-4 cursor-pointer',
    button: 'rounded-lg border px-4 py-2 text-sm font-medium transition-all',
};

const errorClasses: Record<InputVariant, string> = {
    input: 'border-red-500 ring-1 ring-red-500',
    select: 'border-red-500 ring-1 ring-red-500',
    textarea: 'border-red-500 ring-1 ring-red-500',
    checkbox: 'border-red-500 accent-red-500',
    radio: 'border-red-500 accent-red-500',
    button: 'border-red-500 ring-1 ring-red-500',
};

const normalClasses: Record<InputVariant, string> = {
    input: 'border-gray-300 focus:ring-2 focus:ring-[#F5821F]/20 focus:border-[#F5821F]',
    select: 'border-gray-300 focus:ring-2 focus:ring-[#F5821F]/20 focus:border-[#F5821F]',
    textarea:
        'border-gray-300 focus:ring-2 focus:ring-[#F5821F]/20 focus:border-[#F5821F]',
    checkbox: 'border-gray-300 accent-[#F5821F]',
    radio: 'border-gray-300 accent-[#F5821F]',
    button: 'border-gray-300',
};

export function useFieldErrorClass(
    error: Ref<string | null | undefined>,
    variant: InputVariant = 'input',
) {
    return computed(() => [
        baseClasses[variant],
        error.value ? errorClasses[variant] : normalClasses[variant],
    ]);
}
