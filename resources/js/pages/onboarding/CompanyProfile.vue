<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useI18n } from '@/composables/useI18n';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type OnboardingDefaults = {
    email: string;
    currency: string;
    quote_prefix: string;
    invoice_prefix: string;
};

type Props = {
    defaults: OnboardingDefaults;
};

const props = defineProps<Props>();
const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('onboarding.title'),
        href: '/onboarding/company',
    },
]);

const form = useForm({
    name: '',
    phone: '',
    email: props.defaults.email,
    industry: '',
    address: '',
    served_areas_text: '',
    primary_color: '',
    secondary_color: '',
    quote_prefix: props.defaults.quote_prefix,
    invoice_prefix: props.defaults.invoice_prefix,
    default_tax_rate: '',
    currency: props.defaults.currency,
    terms_and_conditions: '',
});

const servedAreas = computed(() =>
    form.served_areas_text
        .split(',')
        .map((value) => value.trim())
        .filter((value) => value.length > 0),
);

const servedAreasError = computed(
    () => (form.errors as Record<string, string>).served_areas,
);

const submit = (): void => {
    form.transform((data) => ({
        ...data,
        default_tax_rate:
            data.default_tax_rate === '' ? null : Number(data.default_tax_rate),
        served_areas: servedAreas.value,
    })).post('/onboarding/company');
};
</script>

<template>
    <Head :title="t('onboarding.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    {{ t('onboarding.title') }}
                </h1>
                <p class="text-sm text-muted-foreground">
                    {{ t('onboarding.description') }}
                </p>
            </div>

            <form
                @submit.prevent="submit"
                class="grid gap-5 rounded-xl border p-5 md:grid-cols-2"
            >
                <div class="grid gap-2 md:col-span-2">
                    <Label for="name">{{ t('onboarding.company_name') }}</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        required
                        autocomplete="organization"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="phone">{{ t('onboarding.phone') }}</Label>
                    <Input id="phone" v-model="form.phone" autocomplete="tel" />
                    <InputError :message="form.errors.phone" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">{{
                        t('onboarding.company_email')
                    }}</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="industry">{{ t('onboarding.industry') }}</Label>
                    <Input
                        id="industry"
                        v-model="form.industry"
                        :placeholder="t('onboarding.industry_placeholder')"
                    />
                    <InputError :message="form.errors.industry" />
                </div>

                <div class="grid gap-2">
                    <Label for="address">{{ t('onboarding.address') }}</Label>
                    <Input id="address" v-model="form.address" />
                    <InputError :message="form.errors.address" />
                </div>

                <div class="grid gap-2 md:col-span-2">
                    <Label for="served_areas_text">{{
                        t('onboarding.served_areas')
                    }}</Label>
                    <Input
                        id="served_areas_text"
                        v-model="form.served_areas_text"
                        placeholder="Montreal, Laval, Longueuil"
                    />
                    <InputError :message="servedAreasError" />
                </div>

                <div class="grid gap-2">
                    <Label for="primary_color">{{
                        t('onboarding.primary_color')
                    }}</Label>
                    <Input
                        id="primary_color"
                        v-model="form.primary_color"
                        placeholder="#1D4ED8"
                    />
                    <InputError :message="form.errors.primary_color" />
                </div>

                <div class="grid gap-2">
                    <Label for="secondary_color">{{
                        t('onboarding.secondary_color')
                    }}</Label>
                    <Input
                        id="secondary_color"
                        v-model="form.secondary_color"
                        placeholder="#0F172A"
                    />
                    <InputError :message="form.errors.secondary_color" />
                </div>

                <div class="grid gap-2">
                    <Label for="quote_prefix">{{
                        t('onboarding.quote_prefix')
                    }}</Label>
                    <Input
                        id="quote_prefix"
                        v-model="form.quote_prefix"
                        required
                    />
                    <InputError :message="form.errors.quote_prefix" />
                </div>

                <div class="grid gap-2">
                    <Label for="invoice_prefix">{{
                        t('onboarding.invoice_prefix')
                    }}</Label>
                    <Input
                        id="invoice_prefix"
                        v-model="form.invoice_prefix"
                        required
                    />
                    <InputError :message="form.errors.invoice_prefix" />
                </div>

                <div class="grid gap-2">
                    <Label for="default_tax_rate">{{
                        t('onboarding.tax_rate')
                    }}</Label>
                    <Input
                        id="default_tax_rate"
                        v-model="form.default_tax_rate"
                        type="number"
                        min="0"
                        max="100"
                        step="0.01"
                    />
                    <InputError :message="form.errors.default_tax_rate" />
                </div>

                <div class="grid gap-2">
                    <Label for="currency">{{ t('onboarding.currency') }}</Label>
                    <Input
                        id="currency"
                        v-model="form.currency"
                        maxlength="3"
                        required
                    />
                    <InputError :message="form.errors.currency" />
                </div>

                <div class="grid gap-2 md:col-span-2">
                    <Label for="terms_and_conditions">{{
                        t('onboarding.terms')
                    }}</Label>
                    <Input
                        id="terms_and_conditions"
                        v-model="form.terms_and_conditions"
                    />
                    <InputError :message="form.errors.terms_and_conditions" />
                </div>

                <div class="md:col-span-2">
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full md:w-auto"
                    >
                        {{ t('onboarding.submit') }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
