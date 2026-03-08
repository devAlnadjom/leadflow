<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar" class="print:block print:w-full print:m-0 print:p-0">
        <AppSidebar class="print:hidden" />
        <AppContent variant="sidebar" class="overflow-x-hidden print:!overflow-visible print:!p-0 print:!m-0 print:!w-full print:!block print:!border-none print:!shadow-none print:bg-white bg-transparent">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" class="print:hidden" />
            <slot />
        </AppContent>
    </AppShell>
</template>
