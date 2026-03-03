<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useI18n } from '@/composables/useI18n';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type FieldDefinition = {
    label: string;
    type:
        | 'text'
        | 'email'
        | 'tel'
        | 'textarea'
        | 'select'
        | 'radio'
        | 'checkbox';
    required: boolean;
    placeholder: string;
    options: string[];
};

type LeadFormPayload = {
    id: number;
    name: string;
    embed_key: string;
    is_active: boolean;
    submit_button_label: string;
    embed_script: string;
    fields: FieldDefinition[];
};

type LeadRecord = {
    id: number;
    name: string | null;
    email: string | null;
    phone: string | null;
    payload: Record<string, unknown>;
    source: string;
    created_at: string | null;
};

type Props = {
    leadForm: LeadFormPayload;
    records: LeadRecord[];
};

const props = defineProps<Props>();
const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('widgets.title'),
        href: '/widgets',
    },
    {
        title: props.leadForm.name,
        href: `/widgets/${props.leadForm.id}`,
    },
]);

const formatPayload = (payload: Record<string, unknown>): string => {
    return JSON.stringify(payload, null, 2);
};
</script>

<template>
    <Head :title="leadForm.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-4 p-4 md:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <Heading
                    :title="leadForm.name"
                    :description="t('widgets.description')"
                />

                <div class="flex items-center gap-2">
                    <Badge
                        :variant="leadForm.is_active ? 'default' : 'secondary'"
                    >
                        {{
                            leadForm.is_active
                                ? t('widgets.active')
                                : t('widgets.inactive')
                        }}
                    </Badge>

                    <Button variant="outline" as-child>
                        <Link :href="`/widgets/${leadForm.id}/edit`">{{
                            t('widgets.edit')
                        }}</Link>
                    </Button>

                    <Button variant="outline" as-child>
                        <a
                            :href="`/widget-preview?uid=${leadForm.embed_key}`"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            {{ t('widgets.integration') }}
                        </a>
                    </Button>
                </div>
            </div>

            <div class="rounded-lg border bg-muted/20 p-3">
                <p class="mb-2 text-xs text-muted-foreground">
                    {{ t('widgets.script') }}
                </p>
                <code class="block overflow-x-auto text-xs">{{
                    leadForm.embed_script
                }}</code>
            </div>

            <Tabs default-value="preview" class="w-full">
                <TabsList>
                    <TabsTrigger value="preview">{{
                        t('widgets.preview_tab')
                    }}</TabsTrigger>
                    <TabsTrigger value="records">{{
                        t('widgets.records_tab')
                    }}</TabsTrigger>
                </TabsList>

                <TabsContent value="preview" class="mt-4">
                    <div class="rounded-xl border p-5">
                        <p class="mb-4 text-sm text-muted-foreground">
                            {{ t('widgets.preview_help') }}
                        </p>

                        <form class="space-y-4" @submit.prevent>
                            <div
                                v-for="(field, index) in leadForm.fields"
                                :key="`${field.label}-${index}`"
                                class="grid gap-2"
                            >
                                <Label>
                                    {{ field.label }}
                                    <span v-if="field.required">*</span>
                                </Label>

                                <Input
                                    v-if="
                                        ['text', 'email', 'tel'].includes(
                                            field.type,
                                        )
                                    "
                                    :type="field.type"
                                    :placeholder="field.placeholder"
                                />

                                <textarea
                                    v-else-if="field.type === 'textarea'"
                                    class="min-h-24 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                                    :placeholder="field.placeholder"
                                />

                                <select
                                    v-else-if="field.type === 'select'"
                                    class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                                >
                                    <option
                                        v-for="(
                                            option, optionIndex
                                        ) in field.options"
                                        :key="`${field.label}-option-${optionIndex}`"
                                        :value="option"
                                    >
                                        {{ option }}
                                    </option>
                                </select>

                                <div
                                    v-else-if="
                                        ['radio', 'checkbox'].includes(
                                            field.type,
                                        )
                                    "
                                    class="space-y-2"
                                >
                                    <label
                                        v-for="(
                                            option, optionIndex
                                        ) in field.options"
                                        :key="`${field.label}-${optionIndex}`"
                                        class="flex items-center gap-2 text-sm"
                                    >
                                        <input
                                            :type="field.type"
                                            :name="field.label"
                                        />
                                        {{ option }}
                                    </label>
                                </div>
                            </div>

                            <Button type="button">{{
                                leadForm.submit_button_label
                            }}</Button>
                        </form>
                    </div>
                </TabsContent>

                <TabsContent value="records" class="mt-4">
                    <div class="overflow-hidden rounded-xl border">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/50 text-left">
                                <tr>
                                    <th class="px-4 py-3">ID</th>
                                    <th class="px-4 py-3">
                                        {{ t('widgets.record_name') }}
                                    </th>
                                    <th class="px-4 py-3">
                                        {{ t('widgets.record_email') }}
                                    </th>
                                    <th class="px-4 py-3">
                                        {{ t('widgets.record_phone') }}
                                    </th>
                                    <th class="px-4 py-3">
                                        {{ t('widgets.record_payload') }}
                                    </th>
                                    <th class="px-4 py-3">
                                        {{ t('widgets.submitted_at') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="records.length === 0">
                                    <td
                                        colspan="6"
                                        class="px-4 py-6 text-muted-foreground"
                                    >
                                        {{ t('widgets.no_records') }}
                                    </td>
                                </tr>

                                <tr
                                    v-for="record in records"
                                    :key="record.id"
                                    class="border-t align-top"
                                >
                                    <td class="px-4 py-3">{{ record.id }}</td>
                                    <td class="px-4 py-3">
                                        {{ record.name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ record.email ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ record.phone ?? '-' }}
                                    </td>
                                    <td class="max-w-md px-4 py-3">
                                        <pre class="overflow-x-auto text-xs">{{
                                            formatPayload(record.payload)
                                        }}</pre>
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ record.created_at ?? '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>
