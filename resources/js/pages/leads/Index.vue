<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useI18n } from '@/composables/useI18n';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type LeadItem = {
    id: number;
    lead_form_id: number;
    lead_form_name: string;
    name: string | null;
    email: string | null;
    phone: string | null;
    source: string;
    created_at: string | null;
};

type LeadFormItem = {
    id: number;
    name: string;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type Props = {
    leads: {
        data: LeadItem[];
        links: PaginationLink[];
    };
    filters: {
        search: string;
        lead_form_id: number | null;
    };
    leadForms: LeadFormItem[];
};

const props = defineProps<Props>();
const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('leads.title'),
        href: '/leads',
    },
]);

const filterState = reactive({
    search: props.filters.search ?? '',
    lead_form_id: props.filters.lead_form_id ? String(props.filters.lead_form_id) : '',
});

const applyFilters = (): void => {
    router.get(
        '/leads',
        {
            search: filterState.search || undefined,
            lead_form_id: filterState.lead_form_id || undefined,
        },
        { preserveState: true, replace: true },
    );
};

const clearFilters = (): void => {
    filterState.search = '';
    filterState.lead_form_id = '';
    applyFilters();
};

const removeLead = (id: number): void => {
    router.delete(`/leads/${id}`);
};
</script>

<template>
    <Head :title="t('leads.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between gap-3">
                <Heading
                    :title="t('leads.title')"
                    :description="t('leads.description')"
                />

                <Button as-child>
                    <Link href="/leads/create">{{ t('leads.new') }}</Link>
                </Button>
            </div>

            <form
                class="grid gap-3 rounded-xl border p-3 md:grid-cols-[1fr_220px_auto_auto]"
                @submit.prevent="applyFilters"
            >
                <Input
                    v-model="filterState.search"
                    :placeholder="t('leads.search')"
                />

                <select
                    v-model="filterState.lead_form_id"
                    class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                >
                    <option value="">{{ t('leads.all_widgets') }}</option>
                    <option
                        v-for="leadForm in leadForms"
                        :key="leadForm.id"
                        :value="String(leadForm.id)"
                    >
                        {{ leadForm.name }}
                    </option>
                </select>

                <Button type="submit" variant="outline">
                    {{ t('leads.filter') }}
                </Button>

                <Button type="button" variant="ghost" @click="clearFilters">
                    {{ t('leads.clear') }}
                </Button>
            </form>

            <div class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm">
                    <thead class="bg-muted/50 text-left">
                        <tr>
                            <th class="px-4 py-3">{{ t('leads.name') }}</th>
                            <th class="px-4 py-3">{{ t('leads.email') }}</th>
                            <th class="px-4 py-3">{{ t('leads.phone') }}</th>
                            <th class="px-4 py-3">{{ t('leads.widget') }}</th>
                            <th class="px-4 py-3">{{ t('leads.source') }}</th>
                            <th class="px-4 py-3">{{ t('leads.created_at') }}</th>
                            <th class="px-4 py-3">{{ t('leads.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="leads.data.length === 0">
                            <td colspan="7" class="px-4 py-6 text-muted-foreground">
                                {{ t('leads.none') }}
                            </td>
                        </tr>

                        <tr v-for="lead in leads.data" :key="lead.id" class="border-t">
                            <td class="px-4 py-3 font-medium">
                                {{ lead.name ?? '-' }}
                            </td>
                            <td class="px-4 py-3">{{ lead.email ?? '-' }}</td>
                            <td class="px-4 py-3">{{ lead.phone ?? '-' }}</td>
                            <td class="px-4 py-3">{{ lead.lead_form_name }}</td>
                            <td class="px-4 py-3">{{ lead.source }}</td>
                            <td class="px-4 py-3">{{ lead.created_at ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <Button size="sm" variant="outline" as-child>
                                        <Link :href="`/leads/${lead.id}`">{{
                                            t('widgets.view')
                                        }}</Link>
                                    </Button>

                                    <Button size="sm" variant="outline" as-child>
                                        <Link :href="`/leads/${lead.id}/edit`">{{
                                            t('widgets.edit')
                                        }}</Link>
                                    </Button>

                                    <Button
                                        size="sm"
                                        variant="destructive"
                                        @click="removeLead(lead.id)"
                                    >
                                        {{ t('widgets.delete') }}
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <Button
                    v-for="(link, index) in leads.links"
                    :key="`link-${index}`"
                    size="sm"
                    variant="outline"
                    :disabled="!link.url"
                    as-child
                >
                    <Link v-if="link.url" :href="link.url" preserve-scroll>
                        <span v-html="link.label" />
                    </Link>
                    <span v-else v-html="link.label" />
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
