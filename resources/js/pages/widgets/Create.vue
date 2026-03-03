<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    TagsInput,
    TagsInputInput,
    TagsInputItem,
    TagsInputItemDelete,
    TagsInputItemText,
} from '@/components/ui/tags-input';
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
    submit_button_label: 'Send request',
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
        <div class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4 md:p-6">
            <Heading
                :title="t('widgets.create_title')"
                :description="t('widgets.description')"
            />

            <form
                @submit.prevent="submit"
                class="space-y-6 rounded-xl border p-5"
            >
                <div class="grid gap-2">
                    <Label for="name">{{ t('widgets.form_name') }}</Label>
                    <Input id="name" v-model="form.name" required />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="submit_button_label">{{
                        t('widgets.button_label')
                    }}</Label>
                    <Input
                        id="submit_button_label"
                        v-model="form.submit_button_label"
                    />
                    <InputError :message="form.errors.submit_button_label" />
                </div>

                <div class="grid gap-2">
                    <Label for="layout_mode">{{
                        t('widgets.layout_mode')
                    }}</Label>
                    <select
                        id="layout_mode"
                        v-model="form.layout_mode"
                        class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                    >
                        <option value="stack">
                            {{ t('widgets.layout_mode_stack') }}
                        </option>
                        <option value="slider">
                            {{ t('widgets.layout_mode_slider') }}
                        </option>
                    </select>
                    <InputError :message="form.errors.layout_mode" />
                </div>

                <label class="flex items-center gap-2 text-sm">
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        class="h-4 w-4"
                    />
                    {{ t('widgets.active') }}
                </label>

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold">
                            {{ t('widgets.fields_title') }}
                        </h3>
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            @click="addField"
                        >
                            {{ t('widgets.add_field') }}
                        </Button>
                    </div>

                    <InputError :message="form.errors.fields" />

                    <div
                        v-for="(field, index) in form.fields"
                        :key="index"
                        class="grid gap-3 rounded-lg border p-3 md:grid-cols-2"
                    >
                        <div class="grid gap-2">
                            <Label>{{ t('widgets.field_label') }}</Label>
                            <Input v-model="field.label" required />
                        </div>

                        <div class="grid gap-2">
                            <Label>{{ t('widgets.field_type') }}</Label>
                            <select
                                v-model="field.type"
                                class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                            >
                                <option value="text">Text</option>
                                <option value="email">Email</option>
                                <option value="tel">Phone</option>
                                <option value="textarea">Textarea</option>
                                <option value="select">Select</option>
                                <option value="radio">Radio</option>
                                <option value="checkbox">Checkbox</option>
                            </select>
                        </div>

                        <div class="grid gap-2">
                            <Label>{{ t('widgets.field_placeholder') }}</Label>
                            <Input v-model="field.placeholder" />
                        </div>

                        <div class="grid gap-2">
                            <Label>{{ t('widgets.field_options') }}</Label>
                            <TagsInput
                                v-if="fieldUsesOptions(field.type)"
                                v-model="field.options"
                            >
                                <TagsInputItem
                                    v-for="option in field.options"
                                    :key="option"
                                    :value="option"
                                >
                                    <TagsInputItemText />
                                    <TagsInputItemDelete />
                                </TagsInputItem>

                                <TagsInputInput
                                    :placeholder="t('widgets.field_options')"
                                />
                            </TagsInput>
                            <p v-else class="text-xs text-muted-foreground">
                                {{ t('widgets.field_type') }}: {{ field.type }}
                            </p>
                        </div>

                        <label
                            class="flex items-center gap-2 text-sm md:col-span-2"
                        >
                            <input
                                v-model="field.required"
                                type="checkbox"
                                class="h-4 w-4"
                            />
                            {{ t('widgets.field_required') }}
                        </label>

                        <div class="md:col-span-2">
                            <Button
                                type="button"
                                variant="destructive"
                                size="sm"
                                @click="removeField(index)"
                            >
                                {{ t('widgets.delete') }}
                            </Button>
                        </div>
                    </div>
                </div>

                <Button type="submit" :disabled="form.processing">{{
                    t('widgets.save_create')
                }}</Button>
            </form>
        </div>
    </AppLayout>
</template>
