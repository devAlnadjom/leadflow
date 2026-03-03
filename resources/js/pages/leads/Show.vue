<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { useI18n } from '@/composables/useI18n';
import type { BreadcrumbItem } from '@/types';

type LeadPayload = {
    id: number;
    lead_form_id: number;
    lead_form_name: string;
    name: string | null;
    email: string | null;
    phone: string | null;
    source: string;
    payload: Record<string, unknown>;
    created_at: string | null;
    updated_at: string | null;
};

type Props = {
    lead: LeadPayload;
};

const props = defineProps<Props>();
const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('leads.title'),
        href: '/leads',
    },
    {
        title: `#${props.lead.id}`,
        href: `/leads/${props.lead.id}`,
    },
]);

const payloadFormatted = computed<string>(() =>
    JSON.stringify(props.lead.payload ?? {}, null, 2),
);

const removeLead = (): void => {
    router.delete(`/leads/${props.lead.id}`);
};
</script>

<template>
    <Head :title="`${t('leads.show_title')} #${lead.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between gap-3">
                <Heading
                    :title="`${t('leads.show_title')} #${lead.id}`"
                    :description="t('leads.description')"
                />

                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="`/leads/${lead.id}/edit`">{{
                            t('widgets.edit')
                        }}</Link>
                    </Button>

                    <Button variant="destructive" @click="removeLead">
                        {{ t('widgets.delete') }}
                    </Button>
                </div>
            </div>

            <div class="grid gap-4 rounded-xl border p-5 md:grid-cols-2">
                <div>
                    <p class="text-xs text-muted-foreground">{{ t('leads.name') }}</p>
                    <p class="font-medium">{{ lead.name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">{{ t('leads.widget') }}</p>
                    <p class="font-medium">{{ lead.lead_form_name }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">{{ t('leads.email') }}</p>
                    <p class="font-medium">{{ lead.email ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">{{ t('leads.phone') }}</p>
                    <p class="font-medium">{{ lead.phone ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">{{ t('leads.source') }}</p>
                    <p class="font-medium">{{ lead.source }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">{{ t('leads.created_at') }}</p>
                    <p class="font-medium">{{ lead.created_at ?? '-' }}</p>
                </div>
            </div>

            <div class="rounded-xl border p-5">
                <p class="mb-3 text-sm font-semibold">{{ t('leads.payload') }}</p>
                <pre class="overflow-x-auto rounded-md bg-muted p-3 text-xs">{{
                    payloadFormatted
                }}</pre>
            </div>

            <div>
                <Button variant="outline" as-child>
                    <Link href="/leads">{{ t('leads.back') }}</Link>
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
