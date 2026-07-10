<script setup lang="ts">
import { statusBadgeClasses } from '@/lib/orders';

defineProps<{
    status: string;
    label: string;
}>();
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
        :class="statusBadgeClasses[status] ?? statusBadgeClasses.draft"
    >
        {{ label }}
    </span>
</template>
