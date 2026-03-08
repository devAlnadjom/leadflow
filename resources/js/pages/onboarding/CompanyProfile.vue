<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Building2, PaintBucket, CircleDollarSign, CheckCircle2, ChevronRight, ChevronLeft, MapPin } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useI18n } from '@/composables/useI18n';
import AppLayout from '@/layouts/AppLayout.vue';
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
    primary_color: '#1D4ED8',
    secondary_color: '#0F172A',
    quote_prefix: props.defaults.quote_prefix,
    invoice_prefix: props.defaults.invoice_prefix,
    tax1_name: 'TPS',
    tax1_rate: '5.000',
    tax2_name: 'TVQ',
    tax2_rate: '9.975',
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
        tax1_rate: data.tax1_rate === '' ? null : Number(data.tax1_rate),
        tax2_rate: data.tax2_rate === '' ? null : Number(data.tax2_rate),
        served_areas: servedAreas.value,
    })).post('/onboarding/company');
};

const currentStep = ref(1);
const totalSteps = 3;

const nextStep = () => {
    if (currentStep.value < totalSteps) currentStep.value++;
};
const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const steps = [
    { id: 1, title: 'Votre Entreprise', desc: 'Informations générales', icon: Building2 },
    { id: 2, title: 'Apparence', desc: 'Couleurs et design', icon: PaintBucket },
    { id: 3, title: 'Facturation', desc: 'Taxes et devises', icon: CircleDollarSign },
];
</script>

<template>
    <Head :title="t('onboarding.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-4xl flex-col gap-8 p-4 md:p-8 lg:p-12 min-h-screen">
            
            <!-- Header -->
            <div class="text-center space-y-2 mb-4">
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">
                    Bienvenue sur LeadFlow
                </h1>
                <p class="text-slate-500 max-w-xl mx-auto">
                    Commençons par configurer votre espace de travail. Cela ne prendra que quelques minutes.
                </p>
            </div>

            <!-- Stepper Progress -->
            <div class="mb-8">
                <div class="flex items-center justify-between relative">
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-slate-100 rounded-full z-0"></div>
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-indigo-600 rounded-full z-0 transition-all duration-500 ease-in-out" :style="{ width: ((currentStep - 1) / (totalSteps - 1)) * 100 + '%' }"></div>
                    
                    <div v-for="step in steps" :key="step.id" class="relative z-10 flex flex-col items-center gap-2">
                        <div 
                            class="w-12 h-12 rounded-full border-4 flex items-center justify-center transition-colors duration-300 shadow-sm"
                            :class="[
                                currentStep >= step.id ? 'bg-indigo-600 border-indigo-100 text-white' : 'bg-white border-slate-100 text-slate-400',
                            ]"
                        >
                            <CheckCircle2 v-if="currentStep > step.id" class="w-5 h-5" />
                            <component v-else :is="step.icon" class="w-5 h-5" />
                        </div>
                        <div class="text-center hidden md:block">
                            <p class="text-sm font-semibold" :class="currentStep >= step.id ? 'text-slate-900' : 'text-slate-400'">{{ step.title }}</p>
                            <p class="text-xs text-slate-500">{{ step.desc }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form
                @submit.prevent="submit"
                class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col min-h-[500px]"
            >
                <div class="p-6 md:p-10 flex-1">
                    
                    <!-- STEP 1: General Info -->
                    <Transition
                        enter-active-class="transition duration-300 ease-out"
                        enter-from-class="opacity-0 translate-x-4"
                        enter-to-class="opacity-100 translate-x-0"
                        leave-active-class="hidden"
                    >
                        <div v-show="currentStep === 1" class="space-y-6">
                            <div class="space-y-1 mb-6">
                                <h2 class="text-xl font-semibold">Parlez-nous de votre entreprise</h2>
                                <p class="text-sm text-slate-500">Ces informations seront utilisées sur vos devis et factures.</p>
                            </div>

                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="grid gap-2 md:col-span-2">
                                    <Label for="name">{{ t('onboarding.company_name') }} <span class="text-red-500">*</span></Label>
                                    <Input id="name" v-model="form.name" required autocomplete="organization" placeholder="Nom officiel" class="h-11" />
                                    <InputError :message="form.errors.name" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="phone">Téléphone principal</Label>
                                    <Input id="phone" v-model="form.phone" autocomplete="tel" placeholder="(514) 123-4567" class="h-11" />
                                    <InputError :message="form.errors.phone" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="email">Courriel de contact <span class="text-red-500">*</span></Label>
                                    <Input id="email" v-model="form.email" type="email" required autocomplete="email" class="h-11" />
                                    <InputError :message="form.errors.email" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="industry">{{ t('onboarding.industry') }} <span class="text-red-500">*</span></Label>
                                    <Select v-model="form.industry" required>
                                        <SelectTrigger id="industry" class="h-11">
                                            <SelectValue :placeholder="t('onboarding.industry_placeholder')" />
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
                                    <InputError :message="form.errors.industry" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="address">{{ t('onboarding.address') }}</Label>
                                    <Input id="address" v-model="form.address" placeholder="123 rue Principale, Ville" class="h-11" />
                                    <InputError :message="form.errors.address" />
                                </div>

                                <div class="grid gap-2 md:col-span-2">
                                    <Label for="served_areas_text">Villes desservies (séparées par des virgules)</Label>
                                    <div class="relative">
                                        <MapPin class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                        <Input
                                            id="served_areas_text"
                                            v-model="form.served_areas_text"
                                            placeholder="Montréal, Laval, Longueuil"
                                            class="h-11 pl-9"
                                        />
                                    </div>
                                    <InputError :message="servedAreasError" />
                                </div>
                            </div>
                        </div>
                    </Transition>

                    <!-- STEP 2: Apparence -->
                    <Transition
                        enter-active-class="transition duration-300 ease-out delay-150"
                        enter-from-class="opacity-0 translate-x-4"
                        enter-to-class="opacity-100 translate-x-0"
                        leave-active-class="hidden"
                    >
                        <div v-show="currentStep === 2" class="space-y-6">
                            <div class="space-y-1 mb-6">
                                <h2 class="text-xl font-semibold">Identité visuelle</h2>
                                <p class="text-sm text-slate-500">Personnalisez les couleurs de vos formulaires et devis.</p>
                            </div>

                            <div class="grid gap-8 md:grid-cols-2">
                                <div class="grid gap-4 bg-slate-50 p-6 rounded-xl border border-slate-100">
                                    <Label for="primary_color" class="font-semibold text-base">Couleur principale</Label>
                                    <p class="text-xs text-slate-500 -mt-2 mb-2">Sera utilisée pour vos boutons d'action et éléments clés.</p>
                                    <div class="flex items-center gap-4">
                                        <Input
                                            id="primary_color"
                                            v-model="form.primary_color"
                                            type="color"
                                            class="w-16 h-16 p-1 cursor-pointer rounded-lg border-2"
                                        />
                                        <div class="text-sm font-mono text-slate-700 bg-white px-3 py-1.5 rounded border shadow-sm">
                                            {{ form.primary_color || '#1D4ED8' }}
                                        </div>
                                    </div>
                                    <InputError :message="form.errors.primary_color" />
                                </div>

                                <div class="grid gap-4 bg-slate-50 p-6 rounded-xl border border-slate-100">
                                    <Label for="secondary_color" class="font-semibold text-base">Couleur secondaire</Label>
                                    <p class="text-xs text-slate-500 -mt-2 mb-2">Utilisée pour les badges, en-têtes ou textes discrets.</p>
                                    <div class="flex items-center gap-4">
                                        <Input
                                            id="secondary_color"
                                            v-model="form.secondary_color"
                                            type="color"
                                            class="w-16 h-16 p-1 cursor-pointer rounded-lg border-2"
                                        />
                                        <div class="text-sm font-mono text-slate-700 bg-white px-3 py-1.5 rounded border shadow-sm">
                                            {{ form.secondary_color || '#0F172A' }}
                                        </div>
                                    </div>
                                    <InputError :message="form.errors.secondary_color" />
                                </div>
                            </div>
                        </div>
                    </Transition>

                    <!-- STEP 3: Billing & Taxes -->
                    <Transition
                        enter-active-class="transition duration-300 ease-out delay-150"
                        enter-from-class="opacity-0 translate-x-4"
                        enter-to-class="opacity-100 translate-x-0"
                        leave-active-class="hidden"
                    >
                        <div v-show="currentStep === 3" class="space-y-6">
                            <div class="space-y-1 mb-6">
                                <h2 class="text-xl font-semibold">Facturation et Taxes</h2>
                                <p class="text-sm text-slate-500">Configurez la tarification de votre région.</p>
                            </div>

                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label for="currency">Devise (3 lettres)</Label>
                                    <Input id="currency" v-model="form.currency" maxlength="3" required placeholder="CAD, EUR, USD..." class="h-11" />
                                    <InputError :message="form.errors.currency" />
                                </div>
                                <div class="hidden md:block"></div> <!-- Spacer -->

                                <div class="grid gap-2">
                                    <Label for="quote_prefix">Préfixe Devis (ex: DEV)</Label>
                                    <Input id="quote_prefix" v-model="form.quote_prefix" required class="h-11" />
                                    <InputError :message="form.errors.quote_prefix" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="invoice_prefix">Préfixe Factures (ex: FAC)</Label>
                                    <Input id="invoice_prefix" v-model="form.invoice_prefix" required class="h-11" />
                                    <InputError :message="form.errors.invoice_prefix" />
                                </div>

                                <div class="grid gap-2 md:col-span-2 bg-slate-50 p-5 rounded-lg border border-slate-100 mt-2">
                                    <Label class="text-base font-semibold mb-3 block">Taxe 1 (ex: TPS)</Label>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="grid gap-2">
                                            <Input id="tax1_name" v-model="form.tax1_name" placeholder="Nom (TPS)" class="h-10 bg-white" />
                                            <InputError :message="form.errors.tax1_name" />
                                        </div>
                                        <div class="grid gap-2">
                                            <div class="relative">
                                                <Input id="tax1_rate" v-model="form.tax1_rate" type="number" min="0" max="100" step="0.001" class="pr-8 h-10 bg-white" placeholder="5.000" />
                                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-slate-500">%</span>
                                            </div>
                                            <InputError :message="form.errors.tax1_rate" />
                                        </div>
                                    </div>
                                </div>

                                <div class="grid gap-2 md:col-span-2 bg-slate-50 p-5 rounded-lg border border-slate-100">
                                    <Label class="text-base font-semibold mb-3 block">Taxe 2 (ex: TVQ)</Label>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="grid gap-2">
                                            <Input id="tax2_name" v-model="form.tax2_name" placeholder="Nom (TVQ)" class="h-10 bg-white" />
                                            <InputError :message="form.errors.tax2_name" />
                                        </div>
                                        <div class="grid gap-2">
                                            <div class="relative">
                                                <Input id="tax2_rate" v-model="form.tax2_rate" type="number" min="0" max="100" step="0.001" class="pr-8 h-10 bg-white" placeholder="9.975" />
                                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-slate-500">%</span>
                                            </div>
                                            <InputError :message="form.errors.tax2_rate" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>

                <!-- Footer Navigation -->
                <div class="bg-slate-50 border-t border-slate-100 p-4 md:p-6 flex items-center justify-between">
                    <Button 
                        type="button" 
                        variant="outline" 
                        @click="prevStep" 
                        :disabled="currentStep === 1"
                        class="gap-2"
                    >
                        <ChevronLeft class="w-4 h-4" /> Précédent
                    </Button>
                    
                    <Button 
                        v-if="currentStep < totalSteps" 
                        type="button" 
                        @click="nextStep"
                        class="gap-2 bg-indigo-600 hover:bg-indigo-700"
                    >
                        Suivant <ChevronRight class="w-4 h-4" />
                    </Button>
                    
                    <Button 
                        v-if="currentStep === totalSteps" 
                        type="submit" 
                        :disabled="form.processing"
                        class="gap-2 bg-green-600 hover:bg-green-700 text-white shadow-sm"
                    >
                        <CheckCircle2 class="w-4 h-4" />
                        Compléter l'Onboarding
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
