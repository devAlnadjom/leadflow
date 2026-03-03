<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useI18n } from '@/composables/useI18n';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type LeadFormItem = {
    id: number;
    name: string;
};

type Props = {
    leadForms: LeadFormItem[];
};

const props = defineProps<Props>();
const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('leads.title'),
        href: '/leads',
    },
    {
        title: t('leads.create_title'),
        href: '/leads/create',
    },
]);

const form = useForm({
    lead_form_id: props.leadForms[0]?.id ? String(props.leadForms[0].id) : '',
    name: '',
    email: '',
    phone: '',
    source: 'manual',
    payload_json: '{}',
});

const payloadError = computed<string | undefined>(() => {
    return (
        form.errors.payload_json ||
        (form.errors as Record<string, string | undefined>).payload
    );
});

const submit = (): void => {
    let payload: Record<string, unknown> = {};

    try {
        payload = JSON.parse(form.payload_json || '{}');
    } catch {
        form.setError('payload_json', 'Invalid JSON.');
        return;
    }

    form.clearErrors('payload_json');
    form.transform((data) => ({
        lead_form_id: Number(data.lead_form_id),
        name: data.name || null,
        email: data.email || null,
        phone: data.phone || null,
        source: data.source || 'manual',
        payload,
    })).post('/leads');
};
</script>

<template>
    <Head :title="t('leads.create_title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4 md:p-6">
            <Heading
                :title="t('leads.create_title')"
                :description="t('leads.description')"
            />

            <form @submit.prevent="submit" class="space-y-6 rounded-xl border p-5">
                <div class="grid gap-2">
                    <Label for="lead_form_id">{{ t('leads.widget') }}</Label>
                    <select
                        id="lead_form_id"
                        v-model="form.lead_form_id"
                        required
                        class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                    >
                        <option
                            v-for="leadForm in leadForms"
                            :key="leadForm.id"
                            :value="String(leadForm.id)"
                        >
                            {{ leadForm.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.lead_form_id" />
                </div>

                <div class="grid gap-2">
                    <Label for="name">{{ t('leads.name') }}</Label>
                    <Input id="name" v-model="form.name" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2 md:grid-cols-2 md:gap-4">
                    <div class="grid gap-2">
                        <Label for="email">{{ t('leads.email') }}</Label>
                        <Input id="email" v-model="form.email" type="email" />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="phone">{{ t('leads.phone') }}</Label>
                        <Input id="phone" v-model="form.phone" />
                        <InputError :message="form.errors.phone" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="source">{{ t('leads.source') }}</Label>
                    <Input id="source" v-model="form.source" />
                    <InputError :message="form.errors.source" />
                </div>

                <div class="grid gap-2">
                    <Label for="payload">{{ t('leads.payload') }}</Label>
                    <Textarea
                        id="payload"
                        v-model="form.payload_json"
                        class="min-h-36 font-mono text-xs"
                    />
                    <InputError :message="payloadError" />
                </div>

                <Button type="submit" :disabled="form.processing">
                    {{ t('leads.save_create') }}
                </Button>
            </form>
        </div>
    </AppLayout>
</template>
