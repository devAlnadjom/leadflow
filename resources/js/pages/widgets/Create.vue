<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { GripVertical, Plus, Trash2, Settings2, LayoutList, CheckCircle2, CircleDashed } from 'lucide-vue-next';

import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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

import { useI18n } from '@/composables/useI18n';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type FieldInput = {
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

type DefaultField = {
    label: string;
    type: FieldInput['type'];
    required: boolean;
    placeholder: string;
    options: string[];
};

type Props = {
    defaultFields: DefaultField[];
    defaultLayoutMode: 'stack' | 'slider';
};

const props = defineProps<Props>();
const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('widgets.title'),
        href: '/widgets',
    },
    {
        title: t('widgets.create_title'),
        href: '/widgets/create',
    },
]);

const form = useForm({
    name: '',
    is_active: true,
    submit_button_label: 'Envoyer',
    layout_mode: props.defaultLayoutMode,
    fields: props.defaultFields.map((field) => ({
        label: field.label,
        type: field.type,
        required: field.required,
        placeholder: field.placeholder,
        options: field.options ?? [],
    })),
});

const addField = (): void => {
    form.fields.push({
        label: '',
        type: 'text',
        required: false,
        placeholder: '',
        options: [],
    });
};

const removeField = (index: number): void => {
    form.fields.splice(index, 1);
};

const submit = (): void => {
    form.transform((data) => ({
        ...data,
        fields: data.fields.map((field) => ({
            ...field,
            options: field.options
                .map((value) => value.trim())
                .filter((value) => value.length > 0),
        })),
    })).post('/widgets');
};

const fieldUsesOptions = (type: FieldInput['type']): boolean => {
    return ['select', 'radio', 'checkbox'].includes(type);
};
</script>

<template>
    <Head :title="t('widgets.create_title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4 md:p-8">
            <div class="flex items-center justify-between">
                <Heading
                    :title="t('widgets.create_title')"
                    :description="t('widgets.description')"
                />
                <Button type="submit" form="widget-form" :disabled="form.processing" class="gap-2">
                    <CheckCircle2 class="w-4 h-4" />
                    {{ t('widgets.save_create') }}
                </Button>
            </div>

            <form id="widget-form" @submit.prevent="submit" class="grid gap-8 md:grid-cols-[1fr_300px] items-start">
                
                <!-- Left Column: Form Fields Builder -->
                <div class="space-y-6">
                    <Card class="border-slate-200 shadow-sm">
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
                            <div>
                                <CardTitle class="text-lg flex items-center gap-2">
                                    <LayoutList class="w-5 h-5 text-indigo-500" />
                                    {{ t('widgets.fields_title') }}
                                </CardTitle>
                                <CardDescription class="mt-1.5">
                                    Ajoutez et configurez les champs que vos prospects devront remplir.
                                </CardDescription>
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                @click="addField"
                                class="gap-2 shrink-0 border-indigo-200 text-indigo-700 hover:bg-indigo-50 hover:text-indigo-800"
                            >
                                <Plus class="w-4 h-4" />
                                {{ t('widgets.add_field') }}
                            </Button>
                        </CardHeader>
                        
                        <CardContent class="space-y-4">
                            <InputError :message="form.errors.fields" class="mb-4" />

                            <div v-if="form.fields.length === 0" class="rounded-xl border border-dashed border-slate-300 p-8 text-center bg-slate-50">
                                <CircleDashed class="w-8 h-8 mx-auto text-slate-400 mb-3" />
                                <h3 class="text-sm font-medium text-slate-900">Aucun champ</h3>
                                <p class="text-sm text-slate-500 mt-1">Commencez à créer votre widget en ajoutant un champ.</p>
                                <Button type="button" variant="secondary" size="sm" @click="addField" class="mt-4 gap-2">
                                    <Plus class="w-4 h-4" /> Ajouter maintenant
                                </Button>
                            </div>

                            <div
                                v-for="(field, index) in form.fields"
                                :key="index"
                                class="group relative rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:border-indigo-300 hover:shadow-md"
                            >
                                <div class="absolute left-0 top-0 bottom-0 w-8 flex items-center justify-center cursor-move text-slate-300 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <GripVertical class="w-4 h-4" />
                                </div>

                                <div class="pl-4 md:pl-6 space-y-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="grid flex-1 gap-2">
                                            <Label>{{ t('widgets.field_label') }} <span class="text-red-500">*</span></Label>
                                            <Input v-model="field.label" required placeholder="Ex: Quel est votre nom ?" class="font-medium" />
                                        </div>
                                        
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            class="text-slate-400 hover:text-red-600 hover:bg-red-50 -mr-2 mt-6"
                                            @click="removeField(index)"
                                            title="Supprimer ce champ"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="grid gap-2">
                                            <Label>{{ t('widgets.field_type') }}</Label>
                                            <Select v-model="field.type">
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Choisir un type" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectGroup>
                                                        <SelectItem value="text">Texte court</SelectItem>
                                                        <SelectItem value="textarea">Paragraphe (Texte long)</SelectItem>
                                                        <SelectItem value="email">Email</SelectItem>
                                                        <SelectItem value="tel">Téléphone</SelectItem>
                                                        <SelectItem value="select">Menu déroulant (Select)</SelectItem>
                                                        <SelectItem value="radio">Choix unique (Radio)</SelectItem>
                                                        <SelectItem value="checkbox">Choix multiples (Checkbox)</SelectItem>
                                                    </SelectGroup>
                                                </SelectContent>
                                            </Select>
                                        </div>

                                        <div class="grid gap-2">
                                            <Label>{{ t('widgets.field_placeholder') }}</Label>
                                            <Input v-model="field.placeholder" placeholder="Ex: Jean Dupont" />
                                        </div>
                                    </div>

                                    <div v-if="fieldUsesOptions(field.type)" class="grid gap-2 pt-2 border-t border-slate-100">
                                        <Label>{{ t('widgets.field_options') }}</Label>
                                        <div class="bg-slate-50 p-3 rounded-lg border border-slate-200">
                                            <TagsInput v-model="field.options">
                                                <TagsInputItem v-for="option in field.options" :key="option" :value="option">
                                                    <TagsInputItemText />
                                                    <TagsInputItemDelete />
                                                </TagsInputItem>
                                                <TagsInputInput :placeholder="t('widgets.field_options') + ' (Entrer pour valider)'" />
                                            </TagsInput>
                                            <p class="text-[11px] text-slate-500 mt-1.5 ml-1">Appuyez sur Entrée pour séparer chaque option.</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 pt-2">
                                        <Switch :checked="field.required" @update:checked="(val) => field.required = val" :id="'req-' + index" />
                                        <Label :for="'req-' + index" class="cursor-pointer font-normal text-slate-600">
                                            {{ t('widgets.field_required') }} (Rendre ce champ obligatoire)
                                        </Label>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional "Add a field" button at the bottom -->
                            <div v-if="form.fields.length > 0" class="pt-2 flex justify-center">
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    @click="addField"
                                    class="gap-2 border-dashed border-2 text-slate-500 hover:text-slate-900 border-slate-300 w-full hover:bg-slate-50 hover:border-slate-400 py-6"
                                >
                                    <Plus class="w-4 h-4" />
                                    Ajouter un nouveau champ
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column: Settings -->
                <div class="space-y-6">
                    <Card class="border-slate-200 shadow-sm sticky top-6">
                        <CardHeader>
                            <CardTitle class="text-lg flex items-center gap-2">
                                <Settings2 class="w-5 h-5 text-slate-500" />
                                Paramètres généraux
                            </CardTitle>
                        </CardHeader>
                        
                        <CardContent class="space-y-5">
                            <div class="grid gap-2">
                                <Label for="name">{{ t('widgets.form_name') }} <span class="text-red-500">*</span></Label>
                                <Input id="name" v-model="form.name" required placeholder="Ex: Demande de devis" />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="submit_button_label">{{ t('widgets.button_label') }}</Label>
                                <Input id="submit_button_label" v-model="form.submit_button_label" placeholder="Ex: Envoyer ma demande" />
                                <InputError :message="form.errors.submit_button_label" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="layout_mode">{{ t('widgets.layout_mode') }}</Label>
                                <Select v-model="form.layout_mode">
                                    <SelectTrigger id="layout_mode">
                                        <SelectValue placeholder="Choisir un style" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem value="stack">{{ t('widgets.layout_mode_stack') }} (Classique)</SelectItem>
                                            <SelectItem value="slider">{{ t('widgets.layout_mode_slider') }} (Étape par étape)</SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.layout_mode" />
                            </div>

                            <div class="pt-4 border-t border-slate-100">
                                <div class="flex items-center justify-between">
                                    <div class="space-y-0.5">
                                        <Label class="text-base font-medium text-slate-900">Activer le widget</Label>
                                        <p class="text-sm text-slate-500">Rend le formulaire public et utilisable.</p>
                                    </div>
                                    <Switch :checked="form.is_active" @update:checked="(val) => form.is_active = val" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

            </form>
        </div>
    </AppLayout>
</template>
