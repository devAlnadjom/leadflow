<script setup lang="ts">
import { ref, reactive } from 'vue';
import axios from 'axios';
import { Star, Loader2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

type FieldInput = {
    label: string;
    type: string;
    required: boolean;
    placeholder: string;
    options: string[];
};

const props = defineProps<{
    open: boolean;
    fields: FieldInput[];
    layoutMode: string;
    submitButtonLabel: string;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'saved'): void;
}>();

const saving = ref(false);
const error  = ref('');

const form = reactive({
    name: '',
    icon: '⭐',
    category: 'custom',
    description: '',
});

const ICONS = ['⭐', '📋', '🏗️', '💆', '🏠', '🚗', '⚙️', '🛠️', '💼', '📞', '🌿', '🎯'];

async function save() {
    if (!form.name.trim()) {
        error.value = 'Le nom du template est obligatoire.';
        return;
    }

    saving.value = true;
    error.value  = '';

    try {
        await axios.post('/widget-templates', {
            name:                form.name.trim(),
            icon:                form.icon,
            category:            form.category,
            description:         form.description.trim() || null,
            layout_mode:         props.layoutMode,
            submit_button_label: props.submitButtonLabel,
            fields:              props.fields,
        });

        form.name        = '';
        form.icon        = '⭐';
        form.category    = 'custom';
        form.description = '';

        emit('saved');
        emit('update:open', false);
    } catch {
        error.value = 'Une erreur est survenue. Veuillez réessayer.';
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <Dialog :open="props.open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center">
                        <Star class="w-4 h-4 text-amber-500" />
                    </div>
                    <DialogTitle class="text-base font-semibold">Sauvegarder comme template</DialogTitle>
                </div>
                <DialogDescription class="text-sm text-slate-500">
                    Ce template sera disponible lors de la création de vos prochains widgets.
                    Il contiendra <strong>{{ props.fields.length }} champ{{ props.fields.length > 1 ? 's' : '' }}</strong>.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-2">
                <!-- Icon picker -->
                <div class="grid gap-2">
                    <Label>Icône</Label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="ico in ICONS"
                            :key="ico"
                            type="button"
                            @click="form.icon = ico"
                            :class="[
                                'w-9 h-9 rounded-lg border text-lg flex items-center justify-center transition-all',
                                form.icon === ico
                                    ? 'border-indigo-400 bg-indigo-50 shadow-sm scale-110'
                                    : 'border-slate-200 hover:border-slate-400 hover:bg-slate-50'
                            ]"
                        >{{ ico }}</button>
                    </div>
                </div>

                <!-- Name -->
                <div class="grid gap-2">
                    <Label>Nom du template <span class="text-red-500">*</span></Label>
                    <Input
                        v-model="form.name"
                        placeholder="Ex: Mon formulaire renovation"
                        @keyup.enter="save"
                    />
                    <p v-if="error" class="text-xs text-red-600">{{ error }}</p>
                </div>

                <!-- Category -->
                <div class="grid gap-2">
                    <Label>Catégorie</Label>
                    <Select v-model="form.category">
                        <SelectTrigger>
                            <SelectValue placeholder="Choisir une catégorie" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem value="custom">⭐ Mon secteur (personnalisé)</SelectItem>
                                <SelectItem value="general">📋 Général</SelectItem>
                                <SelectItem value="construction">🏗️ Construction</SelectItem>
                                <SelectItem value="esthetic">💆 Esthétique</SelectItem>
                                <SelectItem value="real_estate">🏠 Immobilier</SelectItem>
                                <SelectItem value="auto">🚗 Automobile</SelectItem>
                                <SelectItem value="service">⚙️ Services</SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Description -->
                <div class="grid gap-2">
                    <Label>Description <span class="text-slate-400 font-normal">(optionnel)</span></Label>
                    <Input v-model="form.description" placeholder="Courte description du template…" />
                </div>
            </div>

            <DialogFooter class="gap-2">
                <Button variant="outline" @click="emit('update:open', false)" :disabled="saving">
                    Annuler
                </Button>
                <Button @click="save" :disabled="saving" class="gap-2">
                    <Loader2 v-if="saving" class="w-4 h-4 animate-spin" />
                    <Star v-else class="w-4 h-4" />
                    Sauvegarder le template
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
