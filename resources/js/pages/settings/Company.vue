<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
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
import {
    TagsInput,
    TagsInputInput,
    TagsInputItem,
    TagsInputItemDelete,
    TagsInputItemText,
} from '@/components/ui/tags-input';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

type CompanyPayload = {
    name: string;
    description: string | null;
    phone: string | null;
    email: string | null;
    website: string | null;
    industry: string | null;
    address: string | null;
    primary_color: string | null;
    secondary_color: string | null;
};

type CompanySettingsPayload = {
    quote_prefix: string;
    invoice_prefix: string;
    tax1_name: string | null;
    tax1_rate: number | null;
    tax2_name: string | null;
    tax2_rate: number | null;
    currency: string;
    terms_and_conditions: string | null;
};

type Props = {
    company: CompanyPayload;
    logo_url?: string | null;
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

const servedAreasRef = ref(props.servedAreas);
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
                            tax1_rate:
                                data.tax1_rate === '' ||
                                data.tax1_rate === null
                                    ? null
                                    : Number(data.tax1_rate),
                            tax2_rate:
                                data.tax2_rate === '' ||
                                data.tax2_rate === null
                                    ? null
                                    : Number(data.tax2_rate),
                        })
                    "
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2">
                        <Label for="logo">Logo de l'entreprise</Label>
                        <div class="flex items-center gap-4">
                            <div v-if="logo_url" class="h-16 w-16 overflow-hidden rounded-md border border-input mt-2">
                                <img :src="logo_url" alt="Logo" class="h-full w-full object-cover" />
                            </div>
                            <div v-else class="h-16 w-16 overflow-hidden rounded-md border border-input mt-2 bg-muted flex items-center justify-center text-muted-foreground text-xs text-center">
                                Aucun logo
                            </div>
                            <Input
                                id="logo"
                                name="logo"
                                type="file"
                                accept="image/*"
                                class="flex-1"
                            />
                        </div>
                        <InputError :message="errors.logo" />
                    </div>

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
                        <Label for="description">Description de l'entreprise</Label>
                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                            class="min-h-24 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            :value="company.description ?? ''"
                            placeholder="Décrivez brièvement les activités de votre entreprise"
                        />
                        <InputError :message="errors.description" />
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
                        <Label for="website">Site web</Label>
                        <Input
                            id="website"
                            name="website"
                            type="url"
                            placeholder="https://www.mon-entreprise.com"
                            :default-value="company.website ?? ''"
                        />
                        <InputError :message="errors.website" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="industry">{{
                            t('company_settings.industry')
                        }}</Label>
                        <Select
                            name="industry"
                            :default-value="company.industry ?? ''"
                        >
                            <SelectTrigger id="industry">
                                <SelectValue placeholder="Choisir un secteur" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem value="Construction">Construction</SelectItem>
                                    <SelectItem value="Rénovation">Rénovation</SelectItem>
                                    <SelectItem value="Plomberie">Plomberie</SelectItem>
                                    <SelectItem value="Électricité">Électricité</SelectItem>
                                    <SelectItem value="Paysagement">Aménagement paysager</SelectItem>
                                    <SelectItem value="Entretien & Nettoyage">Entretien & Nettoyage</SelectItem>
                                    <SelectItem value="Esthétique">Esthétique / Beauté</SelectItem>
                                    <SelectItem value="Services professionnels">Services professionnels</SelectItem>
                                    <SelectItem value="Autre">Autre</SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
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
                        <TagsInput v-model="servedAreasRef">
                            <TagsInputItem v-for="item in servedAreasRef" :key="item" :value="item">
                                <TagsInputItemText />
                                <TagsInputItemDelete />
                            </TagsInputItem>
                            <TagsInputInput
                                placeholder="Ajouter une ville... (Entrer pour valider)"
                            />
                        </TagsInput>
                        <input type="hidden" name="served_areas_text" :value="servedAreasRef.join(',')" />
                        <InputError :message="errors.served_areas" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="primary_color">{{
                            t('company_settings.primary_color')
                        }}</Label>
                        <div class="flex items-center gap-3">
                            <Input
                                id="primary_color"
                                name="primary_color"
                                type="color"
                                :default-value="company.primary_color ?? '#1D4ED8'"
                                class="w-14 h-10 p-1 cursor-pointer"
                            />
                            <div class="text-sm font-mono text-slate-500">
                                Sélectionner la couleur principale
                            </div>
                        </div>
                        <InputError :message="errors.primary_color" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="secondary_color">{{
                            t('company_settings.secondary_color')
                        }}</Label>
                        <div class="flex items-center gap-3">
                            <Input
                                id="secondary_color"
                                name="secondary_color"
                                type="color"
                                :default-value="company.secondary_color ?? '#0F172A'"
                                class="w-14 h-10 p-1 cursor-pointer"
                            />
                            <div class="text-sm font-mono text-slate-500">
                                Sélectionner la couleur secondaire
                            </div>
                        </div>
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
                    <div class="grid gap-2 md:col-span-2">
                        <Label>Taxe 1 (ex: TPS)</Label>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Input
                                    id="tax1_name"
                                    name="tax1_name"
                                    :default-value="settings.tax1_name"
                                    placeholder="Nom (TPS)"
                                />
                                <InputError :message="errors.tax1_name" />
                            </div>
                            <div class="grid gap-2">
                                <div class="relative">
                                    <Input
                                        id="tax1_rate"
                                        name="tax1_rate"
                                        type="number"
                                        min="0"
                                        max="100"
                                        step="0.001"
                                        class="pr-8"
                                        placeholder="5.000"
                                        :default-value="settings.tax1_rate ?? ''"
                                    />
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-slate-500">%</span>
                                </div>
                                <InputError :message="errors.tax1_rate" />
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-2 md:col-span-2">
                        <Label>Taxe 2 (ex: TVQ)</Label>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Input
                                    id="tax2_name"
                                    name="tax2_name"
                                    :default-value="settings.tax2_name"
                                    placeholder="Nom (TVQ)"
                                />
                                <InputError :message="errors.tax2_name" />
                            </div>
                            <div class="grid gap-2">
                                <div class="relative">
                                    <Input
                                        id="tax2_rate"
                                        name="tax2_rate"
                                        type="number"
                                        min="0"
                                        max="100"
                                        step="0.001"
                                        class="pr-8"
                                        placeholder="9.975"
                                        :default-value="settings.tax2_rate ?? ''"
                                    />
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-slate-500">%</span>
                                </div>
                                <InputError :message="errors.tax2_rate" />
                            </div>
                        </div>
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
