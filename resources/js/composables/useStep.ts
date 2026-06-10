import { ref, computed } from 'vue';

export interface StepItem {
    title: string;
    desc: string;
}

export function useStep(initialSteps: StepItem[], totalSteps: number = 3) {
    const currentStep = ref(1);
    const steps = ref<StepItem[]>(initialSteps);

    const isFirstStep = computed(() => currentStep.value === 1);
    const isLastStep = computed(() => currentStep.value === totalSteps);

    const nextStep = async (validateFn?: unknown) => {
        if (typeof validateFn === 'function') {
            const valid = await validateFn();

            if (!valid) return;
        }

        if (currentStep.value < totalSteps) {
            currentStep.value++;
        }
    };

    const prevStep = () => {
        if (currentStep.value > 1) {
            currentStep.value--;
        }
    };

    const setStep = (step: number) => {
        if (step >= 1 && step <= totalSteps) {
            currentStep.value = step;
        }
    };

    return {
        currentStep,
        steps,
        isFirstStep,
        isLastStep,
        nextStep,
        prevStep,
        setStep,
    };
}
