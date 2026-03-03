<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useI18n } from '@/composables/useI18n';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit, update } from '@/routes/company-settings';
import type { BreadcrumbItem } from '@/types';

type CompanyPayload = {
    name: string;
    phone: string | null;
    email: string | null;
    industry: string | null;
    address: string | null;
    primary_color: string | null;
    secondary_color: string | null;
};

type CompanySettingsPayload = {
    quote_prefix: string;
    invoice_prefix: string;
    default_tax_rate: number | null;
    currency: string;
    terms_and_conditions: string | null;
};

type Props = {
    company: CompanyPayload;
    servedAreas: string[];
    settings: CompanySettingsPayload;
};

const props = defineProps<Props>();
const { t } = useI18n();

const breadcrumbItems = computed<BreadcrumbItem[]>(() => [
    {
        title: t('company_settings.title'),
        href: edit(),
    },
]);

const servedAreasValue = computed(() => props.servedAreas.join(', '));
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="t('company_settings.title')" />

        <h1 class="sr-only">Company settings</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    :title="t('company_settings.title')"
                    :description="t('company_settings.description')"
                />

                <Form
                    v-bind="update.form()"
                    :transform="
                        (data) => ({
                            ...data,
                            served_areas: String(data.served_areas_text ?? '')
                                .split(',')
                                .map((value) => value.trim())
                                .filter((value) => value.length > 0),
                            default_tax_rate:
                                data.default_tax_rate === '' ||
                                data.default_tax_rate === null
                                    ? null
                                    : Number(data.default_tax_rate),
                        })
                    "
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2">
                        <Label for="name">{{
                            t('company_settings.company_name')
                        }}</Label>
                        <Input
                            id="name"
                            name="name"
                            :default-value="company.name"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="phone">{{
                            t('company_settings.phone')
                        }}</Label>
                        <Input
                            id="phone"
                            name="phone"
                            :default-value="company.phone ?? ''"
                        />
                        <InputError :message="errors.phone" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">{{
                            t('company_settings.company_email')
                        }}</Label>
                        <Input
                            id="email"
                            name="email"
                            type="email"
                            :default-value="company.email ?? ''"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="industry">{{
                            t('company_settings.industry')
                        }}</Label>
                        <Input
                            id="industry"
                            name="industry"
                            :default-value="company.industry ?? ''"
                        />
                        <InputError :message="errors.industry" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="address">{{
                            t('company_settings.address')
                        }}</Label>
                        <Input
                            id="address"
                            name="address"
                            :default-value="company.address ?? ''"
                        />
                        <InputError :message="errors.address" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="served_areas_text">{{
                            t('company_settings.served_areas')
                        }}</Label>
                        <Input
                            id="served_areas_text"
                            name="served_areas_text"
                            :default-value="servedAreasValue"
                        />
                        <InputError :message="errors.served_areas" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="primary_color">{{
                            t('company_settings.primary_color')
                        }}</Label>
                        <Input
                            id="primary_color"
                            name="primary_color"
                            :default-value="company.primary_color ?? ''"
                            placeholder="#1D4ED8"
                        />
                        <InputError :message="errors.primary_color" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="secondary_color">{{
                            t('company_settings.secondary_color')
                        }}</Label>
                        <Input
                            id="secondary_color"
                            name="secondary_color"
                            :default-value="company.secondary_color ?? ''"
                            placeholder="#0F172A"
                        />
                        <InputError :message="errors.secondary_color" />
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="quote_prefix">{{
                                t('company_settings.quote_prefix')
                            }}</Label>
                            <Input
                                id="quote_prefix"
                                name="quote_prefix"
                                :default-value="settings.quote_prefix"
                                required
                            />
                            <InputError :message="errors.quote_prefix" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="invoice_prefix">{{
                                t('company_settings.invoice_prefix')
                            }}</Label>
                            <Input
                                id="invoice_prefix"
                                name="invoice_prefix"
                                :default-value="settings.invoice_prefix"
                                required
                            />
                            <InputError :message="errors.invoice_prefix" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="default_tax_rate">{{
                                t('company_settings.tax_rate')
                            }}</Label>
                            <Input
                                id="default_tax_rate"
                                name="default_tax_rate"
                                type="number"
                                min="0"
                                max="100"
                                step="0.01"
                                :default-value="settings.default_tax_rate ?? ''"
                            />
                            <InputError :message="errors.default_tax_rate" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="currency">{{
                                t('company_settings.currency')
                            }}</Label>
                            <Input
                                id="currency"
                                name="currency"
                                maxlength="3"
                                :default-value="settings.currency"
                                required
                            />
                            <InputError :message="errors.currency" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="terms_and_conditions">{{
                            t('company_settings.terms')
                        }}</Label>
                        <textarea
                            id="terms_and_conditions"
                            name="terms_and_conditions"
                            rows="4"
                            class="min-h-24 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            :value="settings.terms_and_conditions ?? ''"
                        />
                        <InputError :message="errors.terms_and_conditions" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            :disabled="processing"
                            data-test="update-company-settings-button"
                        >
                            {{ t('common.save') }}
                        </Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="recentlySuccessful"
                                class="text-sm text-neutral-600"
                            >
                                {{ t('common.saved') }}
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
