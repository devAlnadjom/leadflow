<script setup lang="ts">
import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

const page = usePage();

watch(
    () => page.props.flash as Record<string, string | null>,
    (flash) => {
        if (flash?.success) {
            toast.success(flash.success);
        }
        if (flash?.error) {
            toast.error(flash.error);
        }
        if (flash?.message) {
            toast(flash.message);
        }
    },
    { deep: true, immediate: true }
);
</script>

<template>
    <!-- Renderless component -->
</template>
