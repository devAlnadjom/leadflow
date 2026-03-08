<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, ref, computed } from 'vue';
import {
    FileText,
    Search,
    MoreHorizontal,
    Calendar,
    User,
    Mail,
    ChevronDown,
    XCircle,
    Inbox,
    Clock,
    CheckCircle2,
    ThumbsUp,
    Ban,
    Timer
} from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
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
import type { BreadcrumbItem } from '@/types';

interface QuoteRow {
    id: number;
    public_uid: string;
    quote_number: string;
    status: string;
    total: number;
    expire_at: string | null;
    created_at: string;
    lead_name: string;
    lead_email: string;
    lead_id: number | null;
}

interface Props {
    quotes: {
        data: QuoteRow[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        current_page: number;
        last_page: number;
    };
    filters: {
        search: string;
        status: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Devis', href: '/quotes' },
]);

const filterState = reactive({
    search: props.filters.search ?? '',
    status: props.filters.status ?? '',
});

const applyFilters = (): void => {
    router.get('/quotes', {
        search: filterState.search || undefined,
        status: filterState.status || undefined,
    }, { preserveState: true, replace: true });
};

const clearFilters = (): void => {
    filterState.search = '';
    filterState.status = '';
    applyFilters();
};

const removeQuote = (id: number): void => {
    if (confirm('Voulez-vous vraiment supprimer ce devis ?')) {
        router.delete(`/quotes/${id}`);
    }
};

const statuses = [
    { id: 'draft', label: 'Brouillon', icon: Inbox, color: 'bg-slate-100 text-slate-600 border-slate-200' },
    { id: 'sent', label: 'Envoyé', icon: Clock, color: 'bg-blue-100 text-blue-700 border-blue-200' },
    { id: 'accepted', label: 'Accepté', icon: CheckCircle2, color: 'bg-green-100 text-green-700 border-green-200' },
    { id: 'rejected', label: 'Refusé', icon: Ban, color: 'bg-red-100 text-red-700 border-red-200' },
    { id: 'expired', label: 'Expiré', icon: Timer, color: 'bg-amber-100 text-amber-700 border-amber-200' },
];

const getStatusTotal = (statusId: string) => {
    const total = props.quotes.data
        .filter(q => q.status === statusId)
        .reduce((sum, q) => sum + Number(q.total || 0), 0);
    return total > 0 ? `${total.toFixed(0)}$` : '';
};

const getQuotesByStatus = (statusId: string) => {
    return props.quotes.data.filter(q => q.status === statusId);
};

// Stats
const totalAmount = computed(() => {
    return props.quotes.data.reduce((sum, q) => sum + Number(q.total || 0), 0);
});
</script>

<template>
    <Head title="Devis" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-5 p-4 md:p-6 lg:p-8 bg-slate-50">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <Heading
                    title="Devis & Soumissions"
                    description="Gérez vos propositions commerciales et suivez les montants en un coup d'oeil."
                />
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                <div v-for="s in statuses" :key="s.id" 
                    class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 flex flex-col gap-2 cursor-pointer hover:border-slate-300 transition-colors"
                    :class="{'ring-2 ring-indigo-500 ring-offset-1': filterState.status === s.id}"
                    @click="filterState.status = filterState.status === s.id ? '' : s.id; applyFilters()"
                >
                    <div class="flex items-center gap-2">
                        <span :class="['p-1.5 rounded-md border', s.color]">
                            <component :is="s.icon" class="w-4 h-4" />
                        </span>
                        <span class="text-xs font-medium text-slate-500">{{ s.label }}</span>
                    </div>
                    <div class="flex items-end justify-between">
                        <span class="text-2xl font-bold text-slate-900">{{ getQuotesByStatus(s.id).length }}</span>
                        <span v-if="getStatusTotal(s.id)" class="text-xs font-semibold text-slate-500">{{ getStatusTotal(s.id) }}</span>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <form
                class="flex flex-col md:flex-row gap-3 bg-white p-3 rounded-xl border shadow-sm items-center"
                @submit.prevent="applyFilters"
            >
                <div class="relative flex-1 w-full">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                    <Input
                        v-model="filterState.search"
                        placeholder="Rechercher par numéro de devis, nom ou email du client..."
                        class="pl-9 h-10 w-full"
                    />
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto">
                    <select
                        v-model="filterState.status"
                        class="h-10 px-3 py-2 rounded-md border border-input bg-background text-sm shadow-sm hover:bg-accent hover:text-accent-foreground outline-none"
                    >
                        <option value="">Tous les statuts</option>
                        <option v-for="s in statuses" :key="s.id" :value="s.id">{{ s.label }}</option>
                    </select>

                    <Button type="submit" variant="secondary" class="h-10">
                        Filtrer
                    </Button>

                    <Button type="button" variant="ghost" @click="clearFilters" class="h-10 text-slate-500">
                        Effacer
                    </Button>
                </div>
            </form>

            <!-- Table -->
            <div class="flex-1 overflow-x-auto pb-4">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden min-w-[800px]">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-medium whitespace-nowrap">
                            <tr>
                                <th class="px-4 py-3">Devis</th>
                                <th class="px-4 py-3">Client</th>
                                <th class="px-4 py-3">Statut</th>
                                <th class="px-4 py-3 text-right">Montant</th>
                                <th class="px-4 py-3">Expiration</th>
                                <th class="px-4 py-3">Créé le</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="quotes.data.length === 0">
                                <td colspan="7" class="px-4 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-14 h-14 bg-indigo-50 text-indigo-400 flex items-center justify-center rounded-full">
                                            <FileText class="w-7 h-7" />
                                        </div>
                                        <p class="text-slate-500 text-sm">
                                            {{ (filterState.search || filterState.status) ? 'Aucun devis ne correspond à vos filtres.' : 'Aucun devis pour le moment. Créez-en un depuis la fiche d\'un lead.' }}
                                        </p>
                                        <Button v-if="filterState.search || filterState.status" variant="outline" size="sm" @click="clearFilters" class="gap-1.5">
                                            <XCircle class="w-3.5 h-3.5" /> Effacer les filtres
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="quote in quotes.data" :key="quote.id" class="bg-white hover:bg-slate-50/80 transition-colors cursor-pointer" @click="router.visit(`/quotes/${quote.id}/edit`)">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-500 border border-indigo-100 shrink-0">
                                            <FileText class="w-4 h-4" />
                                        </div>
                                        <span class="font-semibold text-slate-900">{{ quote.quote_number }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-900 text-sm">{{ quote.lead_name }}</div>
                                    <div class="flex items-center text-xs text-slate-500 gap-1.5 mt-0.5" v-if="quote.lead_email !== '-'">
                                        <Mail class="w-3 h-3 text-slate-400 flex-shrink-0" /> {{ quote.lead_email }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold border rounded-lg', statuses.find(s => s.id === quote.status)?.color ?? 'bg-slate-100 text-slate-600 border-slate-200']">
                                        <component :is="statuses.find(s => s.id === quote.status)?.icon" class="w-3.5 h-3.5" />
                                        {{ statuses.find(s => s.id === quote.status)?.label ?? quote.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="font-bold text-slate-900">{{ Number(quote.total).toFixed(2) }}$</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center text-xs text-slate-500 gap-1 font-mono" v-if="quote.expire_at">
                                        <Calendar class="w-3 h-3" />
                                        {{ quote.expire_at }}
                                    </div>
                                    <span v-else class="text-xs text-slate-400">—</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center text-xs text-slate-500 gap-1 font-mono">
                                        <Calendar class="w-3 h-3" />
                                        {{ new Date(quote.created_at).toLocaleDateString() }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right" @click.stop>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger asChild>
                                            <Button variant="ghost" class="h-8 w-8 p-0 text-slate-400 hover:text-slate-700 border border-transparent">
                                                <MoreHorizontal class="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem asChild>
                                                <Link :href="`/quotes/${quote.id}/edit`">Modifier le devis</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem v-if="quote.lead_id" asChild>
                                                <Link :href="`/leads/${quote.lead_id}`">Voir le lead</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem class="text-red-600 focus:bg-red-50 focus:text-red-700" @click="removeQuote(quote.id)">
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

            <!-- Pagination -->
            <div class="flex items-center justify-between mt-auto bg-white p-3 rounded-lg border border-slate-200">
                <span class="text-sm text-muted-foreground hidden sm:inline-block">
                    {{ quotes.data.length }} devis affichés
                    <span v-if="totalAmount > 0" class="ml-2 font-semibold text-slate-700">
                        · Total: {{ totalAmount.toFixed(2) }}$
                    </span>
                </span>
                <div class="flex flex-wrap items-center gap-1 ml-auto">
                    <Button
                        v-for="(link, index) in quotes.links"
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
