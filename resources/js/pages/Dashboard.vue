<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from '@/composables/useI18n';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import {
    Users,
    TrendingUp,
    FileText,
    Briefcase,
    ArrowRight,
    Clock,
    AlertCircle,
    CheckCircle2,
    Send,
    Ban
} from 'lucide-vue-next';

interface DashboardProps {
    stats: {
        total_clients: number;
        total_leads: number;
        total_revenue: number;
        pending_revenue: number;
    };
    recentInvoices: Array<{
        id: number;
        invoice_number: string;
        client_name: string;
        status: string;
        total: number;
        issue_date: string | null;
    }>;
    recentQuotes: Array<{
        id: number;
        quote_number: string;
        lead_name: string;
        status: string;
        total: number;
        expire_at: string | null;
    }>;
    currency: string;
}

const props = defineProps<DashboardProps>();

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('dashboard.title'),
        href: dashboard(),
    },
]);

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: props.currency || 'USD',
    }).format(amount);
};

const formatDate = (date: string | null) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('fr-FR');
};

const invoiceStatuses = computed(() => ({
    draft: { label: t('invoices.status_draft') || 'Brouillon', icon: Clock, color: 'bg-slate-100 text-slate-600 border-slate-200' },
    sent: { label: t('invoices.status_sent') || 'Envoyée', icon: Send, color: 'bg-blue-100 text-blue-700 border-blue-200' },
    paid: { label: t('invoices.status_paid') || 'Payée', icon: CheckCircle2, color: 'bg-emerald-100 text-emerald-700 border-emerald-200' },
    overdue: { label: t('invoices.status_overdue') || 'En retard', icon: AlertCircle, color: 'bg-red-100 text-red-700 border-red-200' },
    cancelled: { label: t('invoices.status_cancelled') || 'Annulée', icon: Ban, color: 'bg-slate-100 text-slate-400 border-slate-200' },
}));

const quoteStatuses = computed(() => ({
    draft: { label: t('quotes.status_draft') || 'Brouillon', icon: Clock, color: 'bg-slate-100 text-slate-600 border-slate-200' },
    sent: { label: t('quotes.status_sent') || 'Envoyé', icon: Send, color: 'bg-blue-100 text-blue-700 border-blue-200' },
    accepted: { label: t('quotes.status_accepted') || 'Accepté', icon: CheckCircle2, color: 'bg-emerald-100 text-emerald-700 border-emerald-200' },
    rejected: { label: t('quotes.status_rejected') || 'Refusé', icon: AlertCircle, color: 'bg-red-100 text-red-700 border-red-200' },
    expired: { label: t('quotes.status_expired') || 'Expiré', icon: Ban, color: 'bg-slate-100 text-slate-400 border-slate-200' },
}));
</script>

<template>
    <Head :title="t('dashboard.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-8 w-full max-w-[1600px] mx-auto space-y-8 sm:px-6 lg:px-8">

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Revenue -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm relative overflow-hidden group">
                    <div class="flex justify-between items-start mb-4 relative z-10">
                        <div>
                            <p class="text-[11px] font-bold uppercase text-slate-400 tracking-widest mb-1">{{ t('dashboard.revenue') || 'Chiffre d\'Affaires' }}</p>
                            <h3 class="text-3xl font-black text-slate-900 tracking-tight tabular-nums">{{ formatCurrency(stats.total_revenue) }}</h3>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                            <TrendingUp class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-emerald-50 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500 text-opacity-0"></div>
                </div>

                <!-- Pending Revenue -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm relative overflow-hidden group">
                    <div class="flex justify-between items-start mb-4 relative z-10">
                        <div>
                            <p class="text-[11px] font-bold uppercase text-slate-400 tracking-widest mb-1">{{ t('dashboard.pending') || 'En Attente' }}</p>
                            <h3 class="text-3xl font-black text-slate-900 tracking-tight tabular-nums">{{ formatCurrency(stats.pending_revenue) }}</h3>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100">
                            <Clock class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-amber-50 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500 text-opacity-0"></div>
                </div>

                <!-- Total Clients -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm relative overflow-hidden group">
                    <div class="flex justify-between items-start mb-4 relative z-10">
                        <div>
                            <p class="text-[11px] font-bold uppercase text-slate-400 tracking-widest mb-1">{{ t('dashboard.clients') || 'Clients' }}</p>
                            <h3 class="text-3xl font-black text-slate-900 tracking-tight tabular-nums">{{ stats.total_clients }}</h3>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                            <Briefcase class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-indigo-50 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500 text-opacity-0"></div>
                </div>

                <!-- Total Leads -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm relative overflow-hidden group">
                    <div class="flex justify-between items-start mb-4 relative z-10">
                        <div>
                            <p class="text-[11px] font-bold uppercase text-slate-400 tracking-widest mb-1">{{ t('dashboard.leads') || 'Prospects' }}</p>
                            <h3 class="text-3xl font-black text-slate-900 tracking-tight tabular-nums">{{ stats.total_leads }}</h3>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
                            <Users class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-blue-50 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500 text-opacity-0"></div>
                </div>
            </div>

            <!-- Recent Lists -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pb-12">
                <!-- Recent Invoices -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            <FileText class="w-5 h-5 text-indigo-500" />
                            {{ t('dashboard.recent_invoices') || 'Dernières Factures' }}
                        </h2>
                        <Link href="/invoices" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 flex items-center gap-1 group">
                            {{ t('dashboard.view_all') || 'Voir tout' }}
                            <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                        </Link>
                    </div>
                    
                    <div class="flex-1 p-0">
                        <div v-if="recentInvoices.length === 0" class="p-8 text-center text-slate-500">
                            {{ t('dashboard.no_invoices') || 'Aucune facture récente.' }}
                        </div>
                        <ul v-else class="divide-y divide-slate-100">
                            <li v-for="invoice in recentInvoices" :key="invoice.id">
                                <Link :href="`/invoices/${invoice.id}`" class="flex items-center justify-between p-4 sm:p-6 hover:bg-slate-50 transition-colors group">
                                    <div class="flex items-center gap-4">
                                        <div class="hidden sm:flex w-10 h-10 rounded-full bg-slate-100 items-center justify-center text-slate-500 group-hover:bg-white group-hover:text-indigo-600 transition-colors border border-transparent group-hover:border-indigo-100">
                                            <FileText class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 mb-0.5">{{ invoice.invoice_number }}</p>
                                            <p class="text-sm font-medium text-slate-500">{{ invoice.client_name }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right flex flex-col items-end gap-1.5">
                                        <span class="font-bold text-slate-900 tabular-nums">{{ formatCurrency(invoice.total) }}</span>
                                        <span v-if="invoiceStatuses[invoice.status]" :class="invoiceStatuses[invoice.status].color" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border">
                                            {{ invoiceStatuses[invoice.status].label }}
                                        </span>
                                    </div>
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Recent Quotes -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            <FileText class="w-5 h-5 text-blue-500" />
                            {{ t('dashboard.recent_quotes') || 'Derniers Devis' }}
                        </h2>
                        <Link href="/quotes" class="text-sm font-medium text-blue-600 hover:text-blue-700 flex items-center gap-1 group">
                            {{ t('dashboard.view_all') || 'Voir tout' }}
                            <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                        </Link>
                    </div>
                    
                    <div class="flex-1 p-0">
                        <div v-if="recentQuotes.length === 0" class="p-8 text-center text-slate-500">
                            {{ t('dashboard.no_quotes') || 'Aucun devis récent.' }}
                        </div>
                        <ul v-else class="divide-y divide-slate-100">
                            <li v-for="quote in recentQuotes" :key="quote.id">
                                <Link :href="`/quotes/${quote.id}`" class="flex items-center justify-between p-4 sm:p-6 hover:bg-slate-50 transition-colors group">
                                    <div class="flex items-center gap-4">
                                        <div class="hidden sm:flex w-10 h-10 rounded-full bg-slate-100 items-center justify-center text-slate-500 group-hover:bg-white group-hover:text-blue-600 transition-colors border border-transparent group-hover:border-blue-100">
                                            <FileText class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 mb-0.5">{{ quote.quote_number }}</p>
                                            <p class="text-sm font-medium text-slate-500">{{ quote.lead_name }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right flex flex-col items-end gap-1.5">
                                        <span class="font-bold text-slate-900 tabular-nums">{{ formatCurrency(quote.total) }}</span>
                                        <span v-if="quoteStatuses[quote.status]" :class="quoteStatuses[quote.status].color" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border">
                                            {{ quoteStatuses[quote.status].label }}
                                        </span>
                                    </div>
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
