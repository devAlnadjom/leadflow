<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { 
    ChevronLeft, 
    Plus, 
    Trash2, 
    Settings2, 
    Activity, 
    FileText, 
    Layers, 
    Code, 
    ExternalLink, 
    Eye, 
    Copy,
    CheckCircle2,
    Search,
    Clock,
    User,
    Mail,
    Phone,
    Database
} from 'lucide-vue-next';
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

const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
};

const recordSearch = ref('');
const filteredRecords = computed(() => {
    if (!recordSearch.value) return props.records;
    const s = recordSearch.value.toLowerCase();
    return props.records.filter(r => 
        (r.name?.toLowerCase().includes(s)) ||
        (r.email?.toLowerCase().includes(s)) ||
        (r.phone?.toLowerCase().includes(s))
    );
});

const removeWidget = () => {
    if (confirm('Voulez-vous vraiment supprimer ce widget ?')) {
        router.delete(`/widgets/${props.leadForm.id}`);
    }
};

const stats = computed(() => [
    { label: 'Total Prospects', value: props.records.length, icon: User, color: 'text-blue-600', bg: 'bg-blue-50' },
    { label: 'Champs Actifs', value: props.leadForm.fields.length, icon: Layers, color: 'text-indigo-600', bg: 'bg-indigo-50' },
    { label: 'Statut', value: props.leadForm.is_active ? 'Actif' : 'Inactif', icon: Activity, color: props.leadForm.is_active ? 'text-emerald-600' : 'text-slate-400', bg: props.leadForm.is_active ? 'bg-emerald-50' : 'bg-slate-50' },
]);

const formatPayloadValue = (val: any): string => {
    if (typeof val === 'object' && val !== null) return JSON.stringify(val);
    return String(val);
};

const formatDate = (dateStr: string | null) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head :title="leadForm.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="h-full flex flex-col bg-slate-50 min-h-screen">
            <!-- Header section -->
            <div class="bg-white border-b border-slate-200 px-4 md:px-8 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 sticky top-0 z-10 shadow-sm">
                <div class="flex items-center gap-4">
                    <Link href="/widgets" class="p-2 -ml-2 rounded-full hover:bg-slate-100 transition-colors text-slate-500">
                        <ChevronLeft class="w-5 h-5" />
                    </Link>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100 shrink-0">
                        <Layers class="w-6 h-6" />
                    </div>
                    <div>
                        <h1 class="text-xl md:text-2xl font-bold text-slate-900 leading-tight">
                            {{ leadForm.name }}
                        </h1>
                        <div class="flex items-center gap-2 mt-1">
                            <Badge :variant="leadForm.is_active ? 'default' : 'secondary'" class="text-[10px] px-2 py-0 uppercase font-bold tracking-wider">
                                {{ leadForm.is_active ? t('widgets.active') : t('widgets.inactive') }}
                            </Badge>
                            <span class="text-xs text-slate-400">&bull; UID: {{ leadForm.embed_key }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Button variant="outline" class="gap-2 shadow-sm" as-child>
                        <Link :href="`/widgets/${leadForm.id}/edit`">
                            <Settings2 class="w-4 h-4" /> {{ t('widgets.edit') }}
                        </Link>
                    </Button>
                    <Button variant="outline" class="gap-2 text-red-600 hover:text-red-700 hover:bg-red-50 border-red-100" @click="removeWidget">
                        <Trash2 class="w-4 h-4" /> Supprimer
                    </Button>
                    <div class="h-8 w-px bg-slate-200 mx-1 hidden sm:block"></div>
                    <Button as-child class="bg-indigo-600 hover:bg-indigo-700 text-white gap-2 shadow-sm font-medium">
                        <a :href="`/widget-preview?uid=${leadForm.embed_key}`" target="_blank">
                            <Eye class="w-4 h-4" /> Aperçu Direct
                        </a>
                    </Button>
                </div>
            </div>

            <div class="p-4 md:p-8 flex-1 flex flex-col w-full mx-auto max-w-7xl">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8 flex-1">
                    
                    <!-- Left Sidebar -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Stats Cards -->
                        <div class="grid grid-cols-1 gap-4">
                            <div v-for="stat in stats" :key="stat.label" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                                <div :class="['w-10 h-10 rounded-xl flex items-center justify-center', stat.bg, stat.color]">
                                    <component :is="stat.icon" class="w-5 h-5" />
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-slate-500">{{ stat.label }}</p>
                                    <p class="text-lg font-bold text-slate-900">{{ stat.value }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Integration Card -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 overflow-hidden">
                            <h2 class="font-bold text-slate-800 flex items-center gap-2 mb-6">
                                <Code class="w-5 h-5 text-indigo-500" /> Intégration
                            </h2>
                            <p class="text-xs text-slate-500 mb-4">Copiez ce code et collez-le de préférence juste avant la balise <code>&lt;/body&gt;</code> de votre site web pour activer le widget.</p>
                            
                            <div class="bg-slate-900 rounded-xl p-4 relative group">
                                <code class="text-[10px] text-indigo-200 block break-all font-mono leading-relaxed">
                                    {{ leadForm.embed_script }}
                                </code>
                                <button 
                                    @click="copyToClipboard(leadForm.embed_script)"
                                    class="absolute top-2 right-2 p-1.5 bg-white/10 hover:bg-white/20 rounded-md text-white transition-colors opacity-0 group-hover:opacity-100"
                                    title="Copier le script"
                                >
                                    <Copy class="w-3.5 h-3.5" />
                                </button>
                            </div>
                            
                            <div class="mt-6 space-y-3">
                                <h3 class="text-[11px] font-bold uppercase text-slate-400 tracking-wider">Configuration</h3>
                                <div class="flex items-center justify-between text-sm py-2 border-b border-slate-50">
                                    <span class="text-slate-500">Bouton de soumission</span>
                                    <span class="text-slate-900 font-medium">{{ leadForm.submit_button_label }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm py-2">
                                    <span class="text-slate-500">Identifiant Unique (UID)</span>
                                    <span class="text-slate-900 font-mono text-xs">{{ leadForm.embed_key }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Fields Overview -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                            <h2 class="font-bold text-slate-800 flex items-center gap-2 mb-6">
                                <Database class="w-5 h-5 text-emerald-500" /> Structure du formulaire
                            </h2>
                            <div class="space-y-3">
                                <div v-for="field in leadForm.fields" :key="field.label" class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">{{ field.label }}</p>
                                        <p class="text-[10px] text-slate-500 uppercase">{{ field.type }}</p>
                                    </div>
                                    <Badge variant="secondary" class="text-[9px]" v-if="field.required">Obligatoire</Badge>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-2">
                        <Tabs default-value="records" class="w-full">
                            <TabsList class="mb-6 h-11 bg-white border shadow-sm">
                                <TabsTrigger value="records" class="flex items-center gap-2 px-6">
                                    <Users class="w-4 h-4" /> Prospects reçus
                                    <Badge variant="secondary" class="ml-2 bg-slate-100">{{ records.length }}</Badge>
                                </TabsTrigger>
                                <TabsTrigger value="preview" class="flex items-center gap-2 px-6">
                                    <Eye class="w-4 h-4" /> Aperçu Interactif
                                </TabsTrigger>
                            </TabsList>

                            <TabsContent value="records" class="mt-0 space-y-4">
                                <div class="flex flex-col sm:flex-row gap-4 items-center justify-between mb-2">
                                    <div class="relative w-full sm:max-w-xs">
                                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300" />
                                        <Input v-model="recordSearch" placeholder="Filtrer les prospects..." class="pl-9 bg-white border-slate-200 shadow-sm" />
                                    </div>
                                    <p class="text-xs text-slate-400 font-medium">Auto-actualisé en temps réel</p>
                                </div>

                                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm text-left">
                                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-medium">
                                                <tr>
                                                    <th class="px-6 py-4">Nom / Contact</th>
                                                    <th class="px-6 py-4">Informations</th>
                                                    <th class="px-6 py-4">Date de réception</th>
                                                    <th class="px-6 py-4 text-right">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100">
                                                <tr v-if="filteredRecords.length === 0" class="hover:bg-slate-50/50">
                                                    <td colspan="4" class="px-6 py-12 text-center text-slate-400 italic">
                                                        <div class="flex flex-col items-center gap-2">
                                                            <User class="w-8 h-8 opacity-20" />
                                                            {{ t('widgets.no_records') }}
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr v-for="record in filteredRecords" :key="record.id" class="hover:bg-slate-50/50 transition-colors group">
                                                    <td class="px-6 py-4">
                                                        <div class="flex items-center gap-3">
                                                            <div class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-500 text-xs text-indigo-600">
                                                                {{ record.name ? record.name.substring(0, 2).toUpperCase() : '?' }}
                                                            </div>
                                                            <div>
                                                                <div class="font-bold text-slate-900">{{ record.name || 'Anonyme' }}</div>
                                                                <div class="text-[10px] text-slate-400 font-mono">ID: #{{ record.id }}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <div class="space-y-1">
                                                            <div v-if="record.email" class="flex items-center gap-1.5 text-slate-600">
                                                                <Mail class="w-3.5 h-3.5 text-slate-400" /> {{ record.email }}
                                                            </div>
                                                            <div v-if="record.phone" class="flex items-center gap-1.5 text-slate-600">
                                                                <Phone class="w-3.5 h-3.5 text-slate-400" /> {{ record.phone }}
                                                            </div>
                                                            <div v-if="!record.email && !record.phone" class="text-slate-400 italic text-xs">
                                                                {{ Object.keys(record.payload).length }} champs remplis
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 text-slate-500 flex flex-col">
                                                        <span class="font-medium text-slate-800">{{ formatDate(record.created_at) }}</span>
                                                        <span class="text-[10px] italic">Via {{ record.source || 'Widget Web' }}</span>
                                                    </td>
                                                    <td class="px-6 py-4 text-right">
                                                        <Button size="icon" variant="ghost" class="h-8 w-8 text-slate-400 hover:text-indigo-600" title="Détails (JSON)">
                                                            <Code class="w-4 h-4" />
                                                        </Button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </TabsContent>

                            <TabsContent value="preview" class="mt-0">
                                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col min-h-[500px]">
                                    <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="flex gap-1.5">
                                                <div class="w-2.5 h-2.5 rounded-full bg-red-400/20"></div>
                                                <div class="w-2.5 h-2.5 rounded-full bg-amber-400/20"></div>
                                                <div class="w-2.5 h-2.5 rounded-full bg-emerald-400/20"></div>
                                            </div>
                                            <span class="text-[11px] font-medium text-slate-400 ml-2">Simulateur d'affichage</span>
                                        </div>
                                        <Badge variant="outline" class="bg-indigo-50 border-indigo-100 text-indigo-700 font-bold text-[9px]">INTERACTIF</Badge>
                                    </div>
                                    <div class="flex-1 p-6 flex items-center justify-center bg-slate-100/50">
                                        <!-- Form Preview Container -->
                                        <div class="w-full max-w-sm bg-white rounded-2xl shadow-xl border border-slate-200/50 p-6">
                                            <h3 class="font-black text-xl text-slate-900 mb-6 flex items-center gap-2">
                                                {{ leadForm.name }}
                                            </h3>
                                            
                                            <div class="space-y-5">
                                                <div v-for="field in leadForm.fields" :key="field.label" class="space-y-1.5">
                                                    <Label class="text-xs font-bold text-slate-700">
                                                        {{ field.label }}
                                                        <span v-if="field.required" class="text-red-500">*</span>
                                                    </Label>
                                                    
                                                    <Input 
                                                        v-if="['text', 'email', 'tel'].includes(field.type)"
                                                        :type="field.type"
                                                        :placeholder="field.placeholder"
                                                        class="h-10 bg-slate-50/50 border-slate-200"
                                                    />
                                                    
                                                    <textarea 
                                                        v-else-if="field.type === 'textarea'"
                                                        class="min-h-[80px] w-full rounded-lg border border-slate-200 bg-slate-50/50 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                                        :placeholder="field.placeholder"
                                                    ></textarea>
                                                    
                                                    <select v-else-if="field.type === 'select'" class="w-full h-10 rounded-lg border border-slate-200 bg-slate-50/50 px-3 text-sm">
                                                        <option>{{ field.placeholder || 'Sélectionner...' }}</option>
                                                        <option v-for="opt in field.options" :key="opt" :value="opt">{{ opt }}</option>
                                                    </select>
                                                </div>

                                                <Button class="w-full h-11 bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-lg shadow-indigo-600/20">
                                                    {{ leadForm.submit_button_label }}
                                                </Button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-4 bg-white border-t border-slate-100 text-center">
                                        <p class="text-xs text-slate-400">C'est un aperçu visuel. Les soumissions ne seront pas enregistrées ici.</p>
                                    </div>
                                </div>
                            </TabsContent>
                        </Tabs>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

