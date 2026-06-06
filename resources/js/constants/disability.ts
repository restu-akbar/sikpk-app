import { Disabilitas } from '@/types';

export const disabilityOptions: {
    value: Disabilitas;
    label: string;
}[] = [
    { value: 'tidak_ada', label: 'Tidak ada' },
    { value: 'pendengaran', label: 'Disabilitas pendengaran / wicara' },
    { value: 'fisik', label: 'Disabilitas fisik / mobilitas' },
    { value: 'lainnya', label: 'Lainnya' },
];
