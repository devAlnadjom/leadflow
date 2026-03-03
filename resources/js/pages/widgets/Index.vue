<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { useI18n } from '@/composables/useI18n';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type LeadFormListItem = {
    id: number;
    name: string;
    embed_key: string;
    is_active: boolean;
    submit_button_label: string;
    fields_count: number;
    embed_script: string;
};

type Props = {
    leadForms: LeadFormListItem[];
};

const props = defineProps<Props>();
const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('widgets.title'),
        href: '/widgets',
    },
]);

const removeWidget = (id: number): void => {
    router.delete(`/widgets/${id}`);
};
</script>

<template>
    <Head :title="t('widgets.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between gap-3">
                <Heading
                    :title="t('widgets.title')"
                    :description="t('widgets.description')"
                />

                <Button as-child>
                    <Link href="/widgets/create">{{ t('widgets.new') }}</Link>
                </Button>
            </div>

            <div class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm">
                    <thead class="bg-muted/50 text-left">
                        <tr>
                            <th class="px-4 py-3">{{ t('widgets.name') }}</th>
                            <th class="px-4 py-3">{{ t('widgets.status') }}</th>
                            <th class="px-4 py-3">{{ t('widgets.fields') }}</th>
                            <th class="px-4 py-3">{{ t('widgets.script') }}</th>
                            <th class="px-4 py-3">
                                {{ t('widgets.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="props.leadForms.length === 0">
                            <td
                                colspan="5"
                                class="px-4 py-6 text-muted-foreground"
                            >
                                {{ t('widgets.none') }}
                            </td>
                        </tr>

                        <tr
                            v-for="leadForm in props.leadForms"
                            :key="leadForm.id"
                            class="border-t"
                        >
                            <td class="px-4 py-3 font-medium">
                                {{ leadForm.name }}
                            </td>
                            <td class="px-4 py-3">
                                {{
                                    leadForm.is_active
                                        ? t('widgets.active')
                                        : t('widgets.inactive')
                                }}
                            </td>
                            <td class="px-4 py-3">
                                {{ leadForm.fields_count }}
                            </td>
                            <td class="max-w-md px-4 py-3">
                                <code class="text-xs break-all">{{
                                    leadForm.embed_script
                                }}</code>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        as-child
                                    >
                                        <Link
                                            :href="`/widgets/${leadForm.id}`"
                                            >{{ t('widgets.view') }}</Link
                                        >
                                    </Button>

                                    <Button
                                        size="sm"
                                        variant="outline"
                                        as-child
                                    >
                                        <a
                                            :href="`/widget-preview?uid=${leadForm.embed_key}`"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            {{ t('widgets.integration') }}
                                        </a>
                                    </Button>

                                    <Button
                                        size="sm"
                                        variant="outline"
                                        as-child
                                    >
                                        <Link
                                            :href="`/widgets/${leadForm.id}/edit`"
                                            >{{ t('widgets.edit') }}</Link
                                        >
                                    </Button>

                                    <Button
                                        size="sm"
                                        variant="destructive"
                                        @click="removeWidget(leadForm.id)"
                                    >
                                        {{ t('widgets.delete') }}
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
