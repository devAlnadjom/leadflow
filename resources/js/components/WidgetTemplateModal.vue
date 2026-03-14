<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import {
    BookTemplate, Search, Star, Trash2, X, ChevronRight, Loader2
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';

// ── Types ──────────────────────────────────────────────────────────────────
type FieldInput = {
    label: string;
    type: 'text' | 'email' | 'tel' | 'textarea' | 'select' | 'radio' | 'checkbox';
    required: boolean;
    placeholder: string;
    options: string[];
};

type WidgetTemplate = {
    id: number;
    name: string;
    category: string;
    icon: string;
    description: string | null;
    layout_mode: 'stack' | 'slider';
    submit_button_label: string;
    fields: FieldInput[];
    is_system: boolean;
    company_id: number | null;
};

// ── Props / Emits ──────────────────────────────────────────────────────────
const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'load', template: WidgetTemplate): void;
}>();

// ── State ──────────────────────────────────────────────────────────────────
const templates = ref<WidgetTemplate[]>([]);
const loading   = ref(false);
const search    = ref('');
const activeCategory = ref<string>('all');
const deletingId = ref<number | null>(null);

const categoryLabels: Record<string, string> = {
    all:         '🔍 Tous',
    general:     '📋 Général',
    construction:'🏗️ Construction',
    esthetic:    '💆 Esthétique',
    real_estate: '🏠 Immobilier',
    auto:        '🚗 Automobile',
    service:     '⚙️ Services',
    custom:      '⭐ Mes templates',
};

// ── Computed ───────────────────────────────────────────────────────────────
const categories = computed(() => {
    const existing = new Set(templates.value.map(t => t.is_system ? t.category : 'custom'));
    return ['all', ...Object.keys(categoryLabels).filter(k => k !== 'all' && (existing.has(k)))];
});

const filtered = computed(() => {
    let list = templates.value;

    if (activeCategory.value !== 'all') {
        if (activeCategory.value === 'custom') {
            list = list.filter(t => !t.is_system);
        } else {
            list = list.filter(t => t.is_system && t.category === activeCategory.value);
        }
    }

    if (search.value.trim()) {
        const q = search.value.toLowerCase();
        list = list.filter(t =>
            t.name.toLowerCase().includes(q) ||
            (t.description ?? '').toLowerCase().includes(q)
        );
    }

    return list;
});

// ── Methods ────────────────────────────────────────────────────────────────
async function fetchTemplates() {
    loading.value = true;
    try {
        const { data } = await axios.get<WidgetTemplate[]>('/widget-templates');
        templates.value = data;
    } finally {
        loading.value = false;
    }
}

function loadTemplate(template: WidgetTemplate) {
    emit('load', template);
    emit('update:open', false);
}

async function deleteTemplate(template: WidgetTemplate) {
    if (template.is_system) return;
    if (!confirm(`Supprimer le template « ${template.name} » ?`)) return;

    deletingId.value = template.id;
    try {
        await axios.delete(`/widget-templates/${template.id}`);
        templates.value = templates.value.filter(t => t.id !== template.id);
    } finally {
        deletingId.value = null;
    }
}

onMounted(fetchTemplates);
</script>

<template>
    <Dialog :open="props.open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-5xl sm:max-w-5xl w-full max-h-[85vh] flex flex-col p-0 gap-0 overflow-hidden">
            <!-- Header -->
            <DialogHeader class="px-6 pt-6 pb-4 border-b border-slate-100 shrink-0">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center">
                            <BookTemplate class="w-5 h-5 text-indigo-600" />
                        </div>
                        <div>
                            <DialogTitle class="text-lg font-semibold text-slate-900">
                                Modèles de widgets
                            </DialogTitle>
                            <DialogDescription class="text-sm text-slate-500">
                                Sélectionnez un modèle pour pré-remplir votre widget. Vous pourrez le modifier librement.
                            </DialogDescription>
                        </div>
                    </div>
                    <Button
                        variant="ghost" size="icon"
                        class="text-slate-400 hover:text-slate-700"
                        @click="emit('update:open', false)"
                    >
                        <X class="w-4 h-4" />
                    </Button>
                </div>

                <!-- Search -->
                <div class="relative mt-4">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                    <Input
                        v-model="search"
                        placeholder="Rechercher un modèle…"
                        class="pl-9 bg-slate-50 border-slate-200"
                    />
                </div>
            </DialogHeader>

            <!-- Body -->
            <div class="flex flex-1 overflow-hidden min-h-0">
                <!-- Categories sidebar -->
                <nav class="w-44 shrink-0 border-r border-slate-100 p-3 space-y-1 overflow-y-auto">
                    <button
                        v-for="cat in categories"
                        :key="cat"
                        @click="activeCategory = cat"
                        :class="[
                            'w-full text-left px-3 py-2 rounded-lg text-sm transition-colors',
                            activeCategory === cat
                                ? 'bg-indigo-50 text-indigo-700 font-medium'
                                : 'text-slate-600 hover:bg-slate-100'
                        ]"
                    >
                        {{ categoryLabels[cat] ?? cat }}
                    </button>
                </nav>

                <!-- Templates grid -->
                <div class="flex-1 overflow-y-auto p-4">
                    <!-- Loading -->
                    <div v-if="loading" class="flex items-center justify-center h-40">
                        <Loader2 class="w-6 h-6 animate-spin text-indigo-500" />
                    </div>

                    <!-- Empty -->
                    <div v-else-if="filtered.length === 0" class="flex flex-col items-center justify-center h-40 text-slate-400">
                        <BookTemplate class="w-10 h-10 mb-3 opacity-30" />
                        <p class="text-sm">Aucun modèle trouvé</p>
                    </div>

                    <!-- Grid -->
                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div
                            v-for="template in filtered"
                            :key="template.id"
                            class="group relative border border-slate-200 rounded-xl p-4 bg-white hover:border-indigo-300 hover:shadow-md transition-all cursor-pointer"
                            @click="loadTemplate(template)"
                        >
                            <!-- Header -->
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="text-xl shrink-0">{{ template.icon }}</span>
                                    <span class="font-semibold text-sm text-slate-900 truncate">{{ template.name }}</span>
                                </div>
                                <div class="flex items-center gap-1 shrink-0">
                                    <!-- System badge -->
                                    <Badge v-if="template.is_system" variant="secondary" class="text-[10px] px-1.5 py-0 h-5">
                                        Système
                                    </Badge>
                                    <Badge v-else variant="outline" class="text-[10px] px-1.5 py-0 h-5 text-indigo-600 border-indigo-200 bg-indigo-50">
                                        <Star class="w-2.5 h-2.5 mr-0.5" /> Mon template
                                    </Badge>

                                    <!-- Delete button (custom only) -->
                                    <button
                                        v-if="!template.is_system"
                                        @click.stop="deleteTemplate(template)"
                                        :disabled="deletingId === template.id"
                                        class="w-6 h-6 rounded-md flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 transition-colors opacity-0 group-hover:opacity-100"
                                    >
                                        <Loader2 v-if="deletingId === template.id" class="w-3 h-3 animate-spin" />
                                        <Trash2 v-else class="w-3 h-3" />
                                    </button>
                                </div>
                            </div>

                            <!-- Description -->
                            <p class="text-xs text-slate-500 mb-3 line-clamp-2">{{ template.description }}</p>

                            <!-- Meta -->
                            <div class="flex items-center justify-between text-xs text-slate-400">
                                <span>{{ template.fields.length }} champ{{ template.fields.length > 1 ? 's' : '' }}</span>
                                <div class="flex items-center gap-1">
                                    <span class="capitalize">{{ template.layout_mode === 'slider' ? '📑 Étapes' : '📋 Classique' }}</span>
                                </div>
                            </div>

                            <!-- Hover arrow -->
                            <div class="absolute bottom-4 right-4 text-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                <ChevronRight class="w-4 h-4" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
