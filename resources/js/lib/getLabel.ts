export function getLabel<T extends string>(
    options: { value: T; label: string }[],
    value?: T | null,
) {
    return options.find((item) => item.value === value)?.label ?? '-';
}
