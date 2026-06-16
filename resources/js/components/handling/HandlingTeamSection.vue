<script setup lang="ts">
import FormSectionTitle from '../form/FormSectionTitle.vue';
import { getAvatarColor, getInitials } from '@/composables/useInitials';

defineProps<{
    teamNumber: string;
    members: Array<{
        name: string;
    }>;
}>();
</script>

<template>
    <section>
        <FormSectionTitle title="TIM PENANGANAN" />

        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
            <p class="mb-3 text-xs text-gray-400">
                {{ teamNumber }}
            </p>

            <div class="flex flex-wrap gap-3">
                <div
                    v-for="(member, index) in members"
                    :key="index"
                    class="flex items-center gap-2 rounded-full bg-white px-3 py-2"
                >
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                        :class="getAvatarColor(member.name)"
                    >
                        {{ getInitials(member.name) }}
                    </div>

                    <span class="text-sm font-medium text-gray-700">
                        {{ member.name }}
                    </span>
                </div>
            </div>
        </div>
    </section>
</template>
