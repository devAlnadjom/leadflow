<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import { MoreHorizontal, Search, FileText, Briefcase, Plus, Users, Building, Mail, Phone, ExternalLink, LayoutList, LayoutGrid } from 'lucide-vue-next';
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

type ClientItem = {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    company_name: string | null;
    leads_count: number;
    quotes_count: number;
    created_at: string | null;
    tags?: Array<{
        id: number;
        name: string;
        color: string | null;
    }>;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type Props = {
    clients: {
        data: ClientItem[];
        links: PaginationLink[];
    };
    filters: {
        search: string;
    };
};

const props = defineProps<Props>();
const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: 'Clients',
        href: '/clients',
    },
]);

const filterState = reactive({
    search: props.filters.search ?? '',
});

const applyFilters = (): void => {
    router.get(
        '/clients',
        {
            search: filterState.search || undefined,
        },
        { preserveState: true, replace: true },
    );
};

const clearFilters = (): void => {
    filterState.search = '';
    applyFilters();
};

const removeClient = (id: number): void => {
    if (confirm('Voulez-vous vraiment supprimer ce client ? Toutes ses quotes et leads resteront, mais le lien sera coupé.')) {
        router.delete(`/clients/${id}`);
    }
};

const viewMode = ref(localStorage.getItem('clientflow_view_mode') || 'card');
const setViewMode = (mode: string) => {
    viewMode.value = mode;
    localStorage.setItem('clientflow_view_mode', mode);
};
</script>

<template>
    <Head title="Clients | clientux" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-5 p-4 md:p-6 lg:p-8 bg-slate-50 min-h-screen">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <Heading 
                    title="Clients" 
                    description="Gérez votre portefeuille client et consultez leur historique commercial." 
                />

                <div class="flex items-center gap-3">
                    <Button as-child class="gap-2 bg-emerald-600 hover:bg-emerald-700 shadow-sm" title="Ajouter un client (bientôt)">
                        <Link href="#">
                            <Plus class="w-4 h-4" />
                            Nouveau Client
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Filters -->
            <form
                class="flex flex-col md:flex-row gap-3 bg-white p-3 rounded-xl border shadow-sm items-center"
                @submit.prevent="applyFilters"
            >
                <div class="relative flex-1 w-full">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <Input
                        v-model="filterState.search"
                        type="text"
                        placeholder="Rechercher un client..."
                        class="pl-9 h-10 w-full"
                    />
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto">
                    <Button type="submit" variant="secondary" class="h-10">
                        Filtrer
                    </Button>

                    <Button v-if="filterState.search" type="button" variant="ghost" @click="clearFilters" class="h-10 text-slate-500">
                        Réinitialiser
                    </Button>

                    <div class="h-6 w-px bg-slate-200 mx-1 hidden md:block"></div>

                    <div class="flex items-center p-1 bg-slate-100 rounded-lg shrink-0 w-full md:w-auto justify-center">
                        <button type="button" @click="setViewMode('list')" title="Vue Liste"
                                :class="['p-1.5 rounded-md text-sm transition-colors flex-1 md:flex-none flex justify-center', viewMode === 'list' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500 hover:text-slate-700']">
                            <LayoutList class="w-4 h-4" />
                        </button>
                        <button type="button" @click="setViewMode('card')" title="Vue Carte"
                                :class="['p-1.5 rounded-md text-sm transition-colors flex-1 md:flex-none flex justify-center', viewMode === 'card' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500 hover:text-slate-700']">
                            <LayoutGrid class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </form>

                <div v-if="clients.data.length === 0" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-12 text-center flex flex-col items-center justify-center min-h-[400px]">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 border border-slate-100">
                        <Briefcase class="w-8 h-8 text-slate-300" />
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 mb-2">Aucun client trouvé</h3>
                    <p class="text-slate-500 max-w-sm mx-auto mb-6">Vous n'avez pas encore de client ou aucun ne correspond à votre recherche. Convertissez des prospects depuis leur fiche détail.</p>
                    <Button variant="outline" @click="clearFilters" v-if="filterState.search" class="mt-4">Effacer la recherche</Button>
                </div>

                <!-- Card View -->
                <div v-if="viewMode === 'card' && clients.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="client in clients.data" 
                        :key="client.id"
                        class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 hover:shadow-md transition-shadow group relative flex flex-col"
                    >
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1 min-w-0 pr-4">
                                <Link :href="`/clients/${client.id}`" class="hover:text-indigo-600 transition-colors">
                                    <h3 class="font-bold text-slate-900 text-lg truncate hover:text-indigo-600">
                                        {{ client.name }}
                                    </h3>
                                </Link>
                                <p v-if="client.company_name" class="text-xs font-semibold text-indigo-600 flex items-center gap-1 mt-1 truncate">
                                    <Building class="w-3 h-3" /> {{ client.company_name }}
                                </p>

                                <div v-if="client.tags && client.tags.length > 0" class="flex flex-wrap gap-1 mt-3">
                                    <span 
                                        v-for="tag in client.tags" 
                                        :key="tag.id" 
                                        class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-medium text-white"
                                        :style="{ backgroundColor: tag.color || '#6366f1' }"
                                    >
                                        {{ tag.name }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2 mb-6 text-sm text-slate-500 flex-1">
                            <div v-if="client.email" class="flex items-center gap-2 truncate">
                                <Mail class="w-4 h-4 shrink-0 text-slate-400" />
                                <a :href="'mailto:' + client.email" class="hover:text-indigo-600 truncate">{{ client.email }}</a>
                            </div>
                            <div v-if="client.phone" class="flex items-center gap-2 truncate">
                                <Phone class="w-4 h-4 shrink-0 text-slate-400" />
                                <a :href="'tel:' + client.phone" class="hover:text-indigo-600 truncate">{{ client.phone }}</a>
                            </div>
                            <div v-if="!client.email && !client.phone" class="flex items-center gap-2 italic opacity-60">
                                Coordonnées non renseignées
                            </div>
                        </div>

                        <div class="mt-auto border-t border-slate-100 pt-4 flex items-center justify-between text-xs font-medium text-slate-500">
                            <div class="flex gap-4">
                                <div class="flex items-center gap-1" :title="`${client.leads_count} lead(s) lié(s)`">
                                    <Users class="w-4 h-4 text-slate-400" /> {{ client.leads_count }}
                                </div>
                                <div class="flex items-center gap-1" :title="`${client.quotes_count} devis lié(s)`">
                                    <FileText class="w-4 h-4 text-slate-400" /> {{ client.quotes_count }}
                                </div>
                            </div>
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity text-indigo-600 flex items-center gap-1">
                                <Link :href="`/clients/${client.id}`" class="flex items-center gap-1 hover:underline">
                                    <span class="font-medium">Ouvrir le dossier</span>
                                    <ExternalLink class="w-3 h-3" />
                                </Link>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- List View -->
                <div v-else-if="viewMode === 'list' && clients.data.length > 0" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden min-w-[800px] overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-medium whitespace-nowrap">
                            <tr>
                                <th class="px-4 py-3">Nom</th>
                                <th class="px-4 py-3">Entreprise</th>
                                <th class="px-4 py-3">Coordonnées</th>
                                <th class="px-4 py-3 text-center">Historique</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="client in clients.data" :key="client.id" class="bg-white hover:bg-slate-50/80 transition-colors">
                                <td class="px-4 py-3">
                                    <Link :href="`/clients/${client.id}`" class="font-medium text-slate-900 hover:text-indigo-600 transition-colors">
                                        {{ client.name }}
                                    </Link>
                                    <div class="text-xs text-slate-400 mt-0.5" v-if="client.created_at">Depuis {{ new Date(client.created_at).toLocaleDateString() }}</div>
                                    
                                    <div v-if="client.tags && client.tags.length > 0" class="flex flex-wrap gap-1 mt-2">
                                        <span 
                                            v-for="tag in client.tags" 
                                            :key="tag.id" 
                                            class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-medium text-white"
                                            :style="{ backgroundColor: tag.color || '#6366f1' }"
                                        >
                                            {{ tag.name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ client.company_name || '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-col gap-1 text-slate-500 text-xs">
                                        <div v-if="client.email" class="flex items-center gap-1.5 truncate">
                                            <Mail class="w-3 h-3 flex-shrink-0" />
                                            <span>{{ client.email }}</span>
                                        </div>
                                        <div v-if="client.phone" class="flex items-center gap-1.5 truncate">
                                            <Phone class="w-3 h-3 flex-shrink-0" />
                                            <span>{{ client.phone }}</span>
                                        </div>
                                        <div v-if="!client.email && !client.phone" class="italic opacity-60">Non renseignées</div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="inline-flex gap-3 text-xs text-slate-500">
                                        <div class="flex items-center gap-1" title="Leads associés">
                                            <Users class="w-3.5 h-3.5 text-slate-400" /> {{ client.leads_count }}
                                        </div>
                                        <div class="flex items-center gap-1" title="Devis associés">
                                            <FileText class="w-3.5 h-3.5 text-slate-400" /> {{ client.quotes_count }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Link :href="`/clients/${client.id}`" class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-800 transition-colors bg-indigo-50 px-2 py-1.5 rounded-md hover:bg-indigo-100">
                                        Ouvrir
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="clients.links?.length > 3" class="mt-8 flex justify-center pb-8">
                    <div class="flex gap-1 bg-white p-1 rounded-xl shadow-sm border border-slate-200">
                        <Link
                            v-for="(link, num) in clients.links"
                            :key="num"
                            :href="link.url || '#'"
                            class="px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                            :class="[
                                link.active ? 'bg-indigo-50 text-indigo-600 font-bold' : 'text-slate-600 hover:bg-slate-50',
                                !link.url ? 'opacity-50 cursor-not-allowed hidden md:block' : ''
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
        </div>
    </AppLayout>
</template>
