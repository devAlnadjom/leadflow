<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, computed } from 'vue';
import {
    FileText,
    Search,
    MoreHorizontal,
    Calendar,
    Mail,
    XCircle,
    Inbox,
    Clock,
    CheckCircle2,
    Ban,
    AlertCircle,
    Briefcase
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
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { BreadcrumbItem } from '@/types';

interface InvoiceRow {
    id: number;
    public_uid: string;
    invoice_number: string;
    status: string;
    total: number;
    issue_date: string | null;
    due_date: string | null;
    created_at: string;
    client_name: string;
    client_company: string;
    client_id: number | null;
}

interface Props {
    invoices: {
        data: InvoiceRow[];
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
    { title: 'Factures', href: '/invoices' },
]);

const filterState = reactive({
    search: props.filters.search ?? '',
    status: props.filters.status ?? '',
});

const applyFilters = (): void => {
    router.get('/invoices', {
        search: filterState.search || undefined,
        status: filterState.status || undefined,
    }, { preserveState: true, replace: true });
};

const clearFilters = (): void => {
    filterState.search = '';
    filterState.status = '';
    applyFilters();
};

const removeInvoice = (id: number): void => {
    if (confirm('Voulez-vous vraiment supprimer cette facture ?')) {
        router.delete(`/invoices/${id}`);
    }
};

const statuses = [
    { id: 'draft', label: 'Brouillon', icon: Inbox, color: 'bg-slate-100 text-slate-600 border-slate-200' },
    { id: 'sent', label: 'Envoyée', icon: Clock, color: 'bg-blue-100 text-blue-700 border-blue-200' },
    { id: 'paid', label: 'Payée', icon: CheckCircle2, color: 'bg-emerald-100 text-emerald-700 border-emerald-200' },
    { id: 'overdue', label: 'En retard', icon: AlertCircle, color: 'bg-red-100 text-red-700 border-red-200' },
    { id: 'cancelled', label: 'Annulée', icon: Ban, color: 'bg-slate-100 text-slate-400 border-slate-200' },
];

const getInvoicesByStatus = (statusId: string) => {
    return props.invoices.data.filter(i => i.status === statusId);
};

const getStatusTotal = (statusId: string) => {
    const total = props.invoices.data
        .filter(i => i.status === statusId)
        .reduce((sum, i) => sum + Number(i.total || 0), 0);
    return total > 0 ? `${total.toFixed(0)}€` : '';
};

const totalAmount = computed(() => {
    return props.invoices.data.reduce((sum, i) => sum + Number(i.total || 0), 0);
});
</script>

<template>
    <Head title="Factures" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-5 p-4 md:p-6 lg:p-8 bg-slate-50">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 text-slate-900 font-bold">
                <Heading
                    title="Facturation"
                    description="Gérez vos factures, suivez les paiements et relancez les retards."
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
                        <span class="text-2xl font-bold text-slate-900">{{ getInvoicesByStatus(s.id).length }}</span>
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
                        placeholder="Rechercher par numéro de facture, client..."
                        class="pl-9 h-10 w-full"
                    />
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto">
                    <select
                        v-model="filterState.status"
                        class="h-10 px-3 py-2 rounded-md border border-input bg-background text-sm shadow-sm hover:bg-accent hover:text-accent-foreground outline-none text-slate-800"
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
                                <th class="px-4 py-3">Facture</th>
                                <th class="px-4 py-3">Client</th>
                                <th class="px-4 py-3">Statut</th>
                                <th class="px-4 py-3 text-right">Montant</th>
                                <th class="px-4 py-3">Échéance</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="invoices.data.length === 0">
                                <td colspan="6" class="px-4 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-14 h-14 bg-indigo-50 text-indigo-400 flex items-center justify-center rounded-full">
                                            <FileText class="w-7 h-7" />
                                        </div>
                                        <p class="text-slate-500 text-sm">
                                            {{ (filterState.search || filterState.status) ? 'Aucune facture ne correspond à vos filtres.' : 'Aucune facture pour le moment.' }}
                                        </p>
                                        <Button v-if="filterState.search || filterState.status" variant="outline" size="sm" @click="clearFilters" class="gap-1.5">
                                            <XCircle class="w-3.5 h-3.5" /> Effacer les filtres
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="invoice in invoices.data" :key="invoice.id" class="bg-white hover:bg-slate-50/80 transition-colors cursor-pointer" @click="router.visit(`/invoices/${invoice.id}`)">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-500 border border-indigo-100 shrink-0">
                                            <FileText class="w-4 h-4" />
                                        </div>
                                        <span class="font-semibold text-slate-900">{{ invoice.invoice_number }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-900 text-sm">{{ invoice.client_name }}</div>
                                    <div class="flex items-center text-xs text-slate-500 gap-1.5 mt-0.5" v-if="invoice.client_company">
                                        <Briefcase class="w-3 h-3 text-slate-400 flex-shrink-0" /> {{ invoice.client_company }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold border rounded-lg', statuses.find(s => s.id === invoice.status)?.color ?? 'bg-slate-100 text-slate-600 border-slate-200']">
                                        <component :is="statuses.find(s => s.id === invoice.status)?.icon" class="w-3.5 h-3.5" />
                                        {{ statuses.find(s => s.id === invoice.status)?.label ?? invoice.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="font-bold text-slate-900">{{ Number(invoice.total).toFixed(2) }}€</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center text-xs text-slate-500 gap-1 font-mono" v-if="invoice.due_date">
                                        <Calendar class="w-3 h-3" />
                                        {{ invoice.due_date }}
                                    </div>
                                    <span v-else class="text-xs text-slate-400">—</span>
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
                                                <Link :href="`/invoices/${invoice.id}`">Voir la facture</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem v-if="invoice.client_id" asChild>
                                                <Link :href="`/clients/${invoice.client_id}`">Voir le client</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem class="text-red-600 focus:bg-red-50 focus:text-red-700" @click="removeInvoice(invoice.id)">
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
                    {{ invoices.data.length }} factures affichées
                    <span v-if="totalAmount > 0" class="ml-2 font-semibold text-slate-700">
                        · Total: {{ totalAmount.toFixed(2) }}€
                    </span>
                </span>
                <div class="flex flex-wrap items-center gap-1 ml-auto">
                    <Button
                        v-for="(link, index) in invoices.links"
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
