<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import { MoreHorizontal, GripVertical, CheckCircle2, Clock, Inbox, ThumbsUp, XCircle, Search, Calendar, User, Phone, Mail, LayoutList, LayoutGrid, ChevronDown } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useI18n } from '@/composables/useI18n';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type LeadItem = {
    id: number;
    lead_form_id: number;
    lead_form_name: string;
    name: string | null;
    email: string | null;
    phone: string | null;
    source: string;
    status: string;
    value: number;
    created_at: string | null;
    tags?: Array<{
        id: number;
        name: string;
        color: string | null;
    }>;
};

type LeadFormItem = {
    id: number;
    name: string;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type Props = {
    leads: {
        data: LeadItem[];
        links: PaginationLink[];
    };
    filters: {
        search: string;
        lead_form_id: number | null;
    };
    leadForms: LeadFormItem[];
};

const props = defineProps<Props>();
const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('leads.title'),
        href: '/leads',
    },
]);

const filterState = reactive({
    search: props.filters.search ?? '',
    lead_form_id: props.filters.lead_form_id ? String(props.filters.lead_form_id) : '',
});

const applyFilters = (): void => {
    router.get(
        '/leads',
        {
            search: filterState.search || undefined,
            lead_form_id: filterState.lead_form_id || undefined,
        },
        { preserveState: true, replace: true },
    );
};

const clearFilters = (): void => {
    filterState.search = '';
    filterState.lead_form_id = '';
    applyFilters();
};

const removeLead = (id: number): void => {
    if (confirm('Voulez-vous vraiment supprimer ce lead ?')) {
        router.delete(`/leads/${id}`);
    }
};

// View mode
const viewMode = ref(localStorage.getItem('leadflow_view_mode') || 'kanban');
const setViewMode = (mode: string) => {
    viewMode.value = mode;
    localStorage.setItem('leadflow_view_mode', mode);
};

// Kanban statuses
const statuses = [
    { id: 'new', label: 'Nouveau', icon: Inbox, color: 'bg-blue-100 text-blue-700 border-blue-200' },
    { id: 'contacted', label: 'Contacté', icon: Clock, color: 'bg-yellow-100 text-yellow-700 border-yellow-200' },
    { id: 'qualified', label: 'Qualifié', icon: CheckCircle2, color: 'bg-indigo-100 text-indigo-700 border-indigo-200' },
    { id: 'won', label: 'Gagné', icon: ThumbsUp, color: 'bg-green-100 text-green-700 border-green-200' },
    { id: 'lost', label: 'Perdu', icon: XCircle, color: 'bg-red-100 text-red-700 border-red-200' }
];

const updateLeadStatus = (leadId: number, newStatus: string) => {
    // Optimistic UI update could be done here if we had mutable leads array
    router.patch(`/leads/${leadId}/status`, { status: newStatus }, {
        preserveScroll: true,
        preserveState: true,
    });
};

const getLeadsByStatus = (statusId: string) => {
    return props.leads.data.filter(l => l.status === statusId);
};

// HTML5 Drag and drop
const draggingLeadId = ref<number | null>(null);

const onDragStart = (leadId: number, event: DragEvent) => {
    draggingLeadId.value = leadId;
    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', leadId.toString());
    }
};

const onDragOver = (event: DragEvent) => {
    event.preventDefault(); // Necessary to allow dropping
};

const onDrop = (statusId: string, event: DragEvent) => {
    event.preventDefault();
    if (draggingLeadId.value) {
        // Find existing status
        const lead = props.leads.data.find(l => l.id === draggingLeadId.value);
        if (lead && lead.status !== statusId) {
            updateLeadStatus(draggingLeadId.value, statusId);
        }
        draggingLeadId.value = null;
    }
};

const onDragEnd = () => {
    draggingLeadId.value = null;
};

// Calculate total values (optional enhancement)
const getStatusValue = (statusId: string) => {
    const total = getLeadsByStatus(statusId).reduce((sum, lead) => sum + (Number(lead.value) || 0), 0);
    return total > 0 ? `$${total.toFixed(0)}` : '';
};
</script>

<template>
    <Head :title="t('leads.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-5 p-4 md:p-6 lg:p-8 bg-slate-50">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <Heading
                    title="Pipe de Leads"
                    description="Gérez vos prospects et convertissez-les en clients grâce à ce tableau Kanban intuitif."
                />

                <Button as-child class="gap-2 bg-slate-900 hover:bg-slate-800">
                    <Link href="/leads/create">
                        <User class="w-4 h-4" />
                        Nouveau Lead
                    </Link>
                </Button>
            </div>

            <!-- Filters -->
            <form
                class="flex flex-col md:flex-row gap-3 bg-white p-3 rounded-xl border shadow-sm items-center"
                @submit.prevent="applyFilters"
            >
                <div class="relative flex-1 w-full relative">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                    <Input
                        v-model="filterState.search"
                        placeholder="Rechercher par nom, email ou source..."
                        class="pl-9 h-10 w-full"
                    />
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto">
                    <select
                        v-model="filterState.lead_form_id"
                        class="h-10 px-3 py-2 rounded-md border border-input bg-background text-sm shadow-sm hover:bg-accent hover:text-accent-foreground outline-none"
                    >
                        <option value="">Tous les Widgets</option>
                        <option
                            v-for="leadForm in leadForms"
                            :key="leadForm.id"
                            :value="String(leadForm.id)"
                        >
                            {{ leadForm.name }}
                        </option>
                    </select>

                    <Button type="submit" variant="secondary" class="h-10">
                        {{ t('leads.filter') }}
                    </Button>

                    <Button type="button" variant="ghost" @click="clearFilters" class="h-10 text-slate-500">
                        {{ t('leads.clear') }}
                    </Button>

                    <div class="h-6 w-px bg-slate-200 mx-2 hidden md:block"></div>

                    <div class="flex items-center p-1 bg-slate-100 rounded-lg shrink-0 w-full md:w-auto justify-center">
                        <button type="button" @click="setViewMode('list')" title="Vue Liste"
                                :class="['p-1.5 rounded-md text-sm transition-colors flex-1 md:flex-none flex justify-center', viewMode === 'list' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500 hover:text-slate-700']">
                            <LayoutList class="w-4 h-4" />
                        </button>
                        <button type="button" @click="setViewMode('kanban')" title="Vue Kanban"
                                :class="['p-1.5 rounded-md text-sm transition-colors flex-1 md:flex-none flex justify-center', viewMode === 'kanban' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500 hover:text-slate-700']">
                            <LayoutGrid class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </form>

            <!-- Views Area -->
            <div v-if="viewMode === 'kanban'" class="flex-1 overflow-x-auto pb-4">
                <div class="flex gap-4 h-full min-w-max items-start">
                    
                    <div 
                        v-for="column in statuses" 
                        :key="column.id"
                        class="w-80 flex flex-col bg-slate-100/50 rounded-xl border border-slate-200/50"
                        @dragover="onDragOver"
                        @drop="(e) => onDrop(column.id, e)"
                        :class="{'bg-slate-200/70 border-slate-300 border-dashed': draggingLeadId}"
                    >
                        <!-- Column Header -->
                        <div class="p-3 border-b flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span :class="['p-1.5 rounded-md border', column.color]">
                                    <component :is="column.icon" class="w-4 h-4" />
                                </span>
                                <h3 class="font-semibold text-sm text-slate-800">{{ column.label }}</h3>
                                <span class="bg-white text-slate-500 text-xs px-2 py-0.5 rounded-full border shadow-sm">
                                    {{ getLeadsByStatus(column.id).length }}
                                </span>
                            </div>
                            <span class="text-xs font-medium text-slate-500" v-if="getStatusValue(column.id)">
                                {{ getStatusValue(column.id) }}
                            </span>
                        </div>

                        <!-- Column Body -->
                        <div class="p-2 space-y-2 min-h-[300px]">
                            
                            <div v-if="getLeadsByStatus(column.id).length === 0" class="h-24 flex items-center justify-center text-slate-400 text-sm border-2 border-dashed border-slate-200 rounded-lg m-1">
                                Glisser un lead ici
                            </div>

                            <!-- Lead Card -->
                            <div
                                v-for="lead in getLeadsByStatus(column.id)"
                                :key="lead.id"
                                draggable="true"
                                @dragstart="(e) => onDragStart(lead.id, e)"
                                @dragend="onDragEnd"
                                class="bg-white p-3 rounded-lg border border-slate-200 shadow-sm cursor-grab active:cursor-grabbing hover:border-slate-300 hover:shadow-md transition-all group relative"
                            >
                                <div class="flex justify-between items-start mb-2">
                                    <div class="font-medium text-sm text-slate-900 line-clamp-1">
                                        {{ lead.name || 'Anonyme' }}
                                    </div>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger asChild>
                                            <Button variant="ghost" class="h-6 w-6 p-0 text-slate-400 group-hover:text-slate-700">
                                                <MoreHorizontal class="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                            <DropdownMenuItem asChild>
                                                <Link :href="`/leads/${lead.id}`">Voir les détails</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem asChild>
                                                <Link :href="`/leads/${lead.id}/edit`">Modifier</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuLabel>Déplacer vers...</DropdownMenuLabel>
                                            <DropdownMenuItem 
                                                v-for="s in statuses.filter(st => st.id !== lead.status)" 
                                                :key="s.id"
                                                @click="updateLeadStatus(lead.id, s.id)"
                                            >
                                                {{ s.label }}
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem class="text-red-600 focus:bg-red-50 focus:text-red-700" @click="removeLead(lead.id)">
                                                Supprimer
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>
                                
                                <div class="space-y-1.5 mb-3">
                                    <div class="flex items-center text-xs text-slate-500 gap-1.5" v-if="lead.email">
                                        <Mail class="w-3 h-3 flex-shrink-0" />
                                        <span class="truncate">{{ lead.email }}</span>
                                    </div>
                                    <div class="flex items-center text-xs text-slate-500 gap-1.5" v-if="lead.phone">
                                        <Phone class="w-3 h-3 flex-shrink-0" />
                                        <span class="truncate">{{ lead.phone }}</span>
                                    </div>
                                </div>

                                <div v-if="lead.tags && lead.tags.length > 0" class="flex flex-wrap gap-1 mb-3">
                                    <span 
                                        v-for="tag in lead.tags" 
                                        :key="tag.id" 
                                        class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-medium text-white"
                                        :style="{ backgroundColor: tag.color || '#6366f1' }"
                                    >
                                        {{ tag.name }}
                                    </span>
                                </div>

                                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                                    <div class="flex items-center gap-1" title="Source / Widget">
                                        <Badge variant="secondary" class="font-normal text-[10px] px-1.5 py-0 h-4">
                                            {{ lead.lead_form_name }}
                                        </Badge>
                                    </div>
                                    <div class="flex items-center gap-1" title="Date de création">
                                        <Calendar class="w-3 h-3" />
                                        {{ lead.created_at ? new Date(lead.created_at).toLocaleDateString() : '' }}
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>

                </div>
            </div>

            <!-- List View -->
            <div v-else class="flex-1 overflow-x-auto pb-4">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden min-w-[800px]">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-medium whitespace-nowrap">
                            <tr>
                                <th class="px-4 py-3">Contact</th>
                                <th class="px-4 py-3">Statut</th>
                                <th class="px-4 py-3">Coordonnées</th>
                                <th class="px-4 py-3">Source & Date</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="leads.data.length === 0">
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500 bg-white">
                                    Aucun lead trouvé.
                                </td>
                            </tr>
                            <tr v-for="lead in leads.data" :key="lead.id" class="bg-white hover:bg-slate-50/80 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-900">{{ lead.name || 'Anonyme' }}</div>
                                    <div v-if="lead.tags && lead.tags.length > 0" class="flex flex-wrap gap-1 mt-1">
                                        <span 
                                            v-for="tag in lead.tags" 
                                            :key="tag.id" 
                                            class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-medium text-white"
                                            :style="{ backgroundColor: tag.color || '#6366f1' }"
                                        >
                                            {{ tag.name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger asChild>
                                            <button type="button" :class="['inline-flex items-center justify-between gap-1.5 px-2.5 py-1 text-xs font-semibold border rounded-lg transition-colors', statuses.find(s => s.id === lead.status)?.color]">
                                                <component :is="statuses.find(s => s.id === lead.status)?.icon" class="w-3.5 h-3.5" />
                                                {{ statuses.find(s => s.id === lead.status)?.label }}
                                                <ChevronDown class="w-3 h-3 opacity-50 ml-1" />
                                            </button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="start">
                                            <DropdownMenuLabel>Changer le statut</DropdownMenuLabel>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem 
                                                v-for="s in statuses" 
                                                :key="s.id"
                                                @click="updateLeadStatus(lead.id, s.id)"
                                                :class="{'bg-slate-50 font-medium': s.id === lead.status}"
                                            >
                                                <component :is="s.icon" class="w-4 h-4 mr-2" :class="s.color.split(' ')[1]" />
                                                {{ s.label }}
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                                <td class="px-4 py-3 space-y-1">
                                    <div class="flex items-center text-xs text-slate-600 gap-1.5" v-if="lead.email">
                                        <Mail class="w-3.5 h-3.5 text-slate-400" /> {{ lead.email }}
                                    </div>
                                    <div class="flex items-center text-xs text-slate-600 gap-1.5" v-if="lead.phone">
                                        <Phone class="w-3.5 h-3.5 text-slate-400" /> {{ lead.phone }}
                                    </div>
                                    <span v-if="!lead.email && !lead.phone" class="text-xs text-slate-400">-</span>
                                </td>
                                <td class="px-4 py-3 space-y-1">
                                    <Badge variant="secondary" class="font-normal text-[10px] px-1.5 py-0 h-4.5">{{ lead.lead_form_name }}</Badge>
                                    <div class="flex items-center text-xs text-slate-500 gap-1 mt-1 font-mono">
                                        <Calendar class="w-3 h-3" />
                                        {{ lead.created_at ? new Date(lead.created_at).toLocaleDateString() : '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger asChild>
                                            <Button variant="ghost" class="h-8 w-8 p-0 text-slate-400 hover:text-slate-700 border border-transparent">
                                                <MoreHorizontal class="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem asChild>
                                                <Link :href="`/leads/${lead.id}`">Voir les détails</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem asChild>
                                                <Link :href="`/leads/${lead.id}/edit`">Modifier</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem class="text-red-600 focus:bg-red-50 focus:text-red-700" @click="removeLead(lead.id)">
                                                Supprimer
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Standard Pagination at the bottom if many results -->
            <div class="flex items-center justify-between mt-auto bg-white p-3 rounded-lg border border-slate-200">
                <span class="text-sm text-muted-foreground hidden sm:inline-block">
                    Total: {{ leads.data.length }} leads affichés
                </span>
                <div class="flex flex-wrap items-center gap-1 ml-auto">
                    <Button
                        v-for="(link, index) in leads.links"
                        :key="`link-${index}`"
                        size="sm"
                        variant="outline"
                        :disabled="!link.url"
                        :class="{'bg-slate-100 font-semibold text-slate-900 border-slate-300': link.active}"
                        as-child
                    >
                        <Link v-if="link.url" :href="link.url" preserve-scroll>
                            <span v-html="link.label" />
                        </Link>
                        <span v-else v-html="link.label" />
                    </Button>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
