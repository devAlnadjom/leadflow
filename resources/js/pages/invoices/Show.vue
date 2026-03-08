<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { 
    ChevronLeft, 
    Printer, 
    Send, 
    Download, 
    CheckCircle2, 
    Clock, 
    AlertCircle, 
    Ban,
    FileText,
    Briefcase,
    Calendar,
    Mail,
    Phone,
    MapPin
} from 'lucide-vue-next';
import { useI18n } from '@/composables/useI18n';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { BreadcrumbItem } from '@/types';

interface InvoiceItem {
    id: number;
    description: string;
    quantity: number;
    unit_price: number;
    total: number;
}

interface Props {
    invoice: {
        id: number;
        public_uid: string;
        invoice_number: string;
        status: string;
        issue_date: string;
        due_date: string;
        subtotal: number;
        tax1_amount: number;
        tax2_amount: number;
        total: number;
        notes: string | null;
        items: InvoiceItem[];
        client: {
            id: number;
            name: string;
            email: string;
            phone: string | null;
            company_name: string | null;
            address: string | null;
            city: string | null;
            postal_code: string | null;
        };
    };
    settings: {
        tax1_name: string;
        tax2_name: string;
        currency: string;
        legal_mentions?: { key: string; value: string }[];
    };
}

const props = defineProps<Props>();

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: t('invoices.title'), href: '/invoices' },
    { title: props.invoice.invoice_number, href: `/invoices/${props.invoice.id}` },
]);

const statuses = computed(() => ({
    draft: { label: t('invoices.status_draft'), icon: Clock, color: 'bg-slate-100 text-slate-600 border-slate-200' },
    sent: { label: t('invoices.status_sent'), icon: Send, color: 'bg-blue-100 text-blue-700 border-blue-200' },
    paid: { label: t('invoices.status_paid'), icon: CheckCircle2, color: 'bg-emerald-100 text-emerald-700 border-emerald-200' },
    overdue: { label: t('invoices.status_overdue'), icon: AlertCircle, color: 'bg-red-100 text-red-700 border-red-200' },
    cancelled: { label: t('invoices.status_cancelled'), icon: Ban, color: 'bg-slate-100 text-slate-400 border-slate-200' },
}));

const updateStatus = (status: string) => {
    router.patch(`/invoices/${props.invoice.id}/status`, { status }, {
        preserveScroll: true
    });
};

const formatDate = (date: string) => new Date(date).toLocaleDateString('fr-FR');
const formatCurrency = (val: number) => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(val);

</script>

<template>
    <Head :title="t('invoices.invoice') + ' ' + invoice.invoice_number" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Action Bar -->
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
                <div class="flex items-center gap-4">
                    <Link href="/invoices" class="p-2 -ml-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition-colors">
                        <ChevronLeft class="w-6 h-6" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 leading-tight">{{ t('invoices.invoice') }} {{ invoice.invoice_number }}</h1>
                        <p class="text-sm text-slate-500 mt-1">{{ t('invoices.issued_at') }} {{ formatDate(invoice.issue_date) }}</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="outline" class="gap-2">
                                <component :is="statuses[invoice.status].icon" class="w-4 h-4" />
                                {{ statuses[invoice.status].label }}
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuItem v-for="(s, key) in statuses" :key="key" @click="updateStatus(key)">
                                <component :is="s.icon" class="w-4 h-4 mr-2" />
                                {{ s.label }}
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <Button variant="outline" class="gap-2" @click="window.print()">
                        <Printer class="w-4 h-4" /> {{ t('invoices.print') }}
                    </Button>
                    
                    <Button v-if="invoice.status === 'draft'" class="bg-indigo-600 hover:bg-indigo-700 gap-2" @click="updateStatus('sent')">
                        <Send class="w-4 h-4" /> {{ t('invoices.finalize_send') }}
                    </Button>
                </div>
            </div>

            <!-- Invoice Document -->
            <div id="print-area" class="bg-white shadow-xl border border-slate-200 rounded-2xl overflow-hidden print:shadow-none print:border-none">
                <!-- Header / Logo -->
                <div class="p-8 sm:p-12 border-b border-slate-100 flex flex-col sm:flex-row justify-between gap-8">
                    <div>
                        <div class="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center mb-6">
                            <Briefcase class="w-10 h-10 text-white" />
                        </div>
                        <h2 class="text-4xl font-black text-slate-900 tracking-tighter uppercase italic">LeadFlow</h2>
                        <p class="text-slate-400 text-sm font-bold tracking-widest mt-1">CRM & FACTURATION</p>
                    </div>
                    
                    <div class="text-left sm:text-right">
                        <div class="inline-block px-4 py-2 bg-slate-50 rounded-xl border border-slate-100 mb-4">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 block mb-1">{{ t('invoices.payment_status') }}</span>
                            <Badge :variant="invoice.status === 'paid' ? 'default' : 'secondary'" 
                                   :class="invoice.status === 'paid' ? 'bg-emerald-500 hover:bg-emerald-500' : 'bg-amber-100 text-amber-700 hover:bg-amber-100'">
                                {{ statuses[invoice.status].label.toUpperCase() }}
                            </Badge>
                        </div>
                        <div class="space-y-1">
                            <p class="text-slate-900 font-black text-2xl tracking-tight">{{ t('invoices.invoice') }}</p>
                            <p class="text-indigo-600 font-mono font-bold">{{ invoice.invoice_number }}</p>
                        </div>
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 p-8 sm:p-12 bg-white">
                    <div class="space-y-6">
                        <div>
                            <span class="text-[11px] font-black uppercase tracking-widest text-indigo-500 block mb-3">{{ t('invoices.issued_by') }}</span>
                            <div class="space-y-1 text-slate-600">
                                <p class="font-bold text-slate-900 text-lg">{{ t('invoices.your_company') }}</p>
                                <p>123 Avenue du Success</p>
                                <p>75001 Paris, France</p>
                                <p class="text-sm pt-2 flex items-center gap-2"><Mail class="w-3.5 h-3.5" /> hello@leadflow.test</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <span class="text-[11px] font-black uppercase tracking-widest text-indigo-500 block mb-3">{{ t('invoices.bill_to') }}</span>
                            <div class="space-y-1 text-slate-600">
                                <Link :href="`/clients/${invoice.client.id}`" class="font-bold text-slate-900 text-lg hover:text-indigo-600 transition-colors block">
                                    {{ invoice.client.name }}
                                </Link>
                                <p v-if="invoice.client.company_name" class="font-medium italic">{{ invoice.client.company_name }}</p>
                                <p v-if="invoice.client.address">{{ invoice.client.address }}</p>
                                <p>{{ invoice.client.postal_code }} {{ invoice.client.city }}</p>
                                <div class="text-sm pt-2 space-y-1">
                                    <p v-if="invoice.client.email" class="flex items-center gap-2"><Mail class="w-3.5 h-3.5 opacity-60" /> {{ invoice.client.email }}</p>
                                    <p v-if="invoice.client.phone" class="flex items-center gap-2"><Phone class="w-3.5 h-3.5 opacity-60" /> {{ invoice.client.phone }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dates Banner -->
                <div class="mx-8 sm:mx-12 px-8 py-4 bg-slate-50 rounded-2xl border border-slate-100 flex flex-wrap gap-12 justify-between items-center mb-8">
                    <div class="flex items-center gap-4">
                        <div class="p-2 bg-white rounded-lg border border-slate-100">
                            <Calendar class="w-5 h-5 text-slate-400" />
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ t('invoices.issue_date') }}</p>
                            <p class="font-bold text-slate-700">{{ formatDate(invoice.issue_date) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="p-2 bg-white rounded-lg border border-slate-100">
                            <Clock class="w-5 h-5 text-indigo-400" />
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ t('invoices.due_date') }}</p>
                            <p class="font-bold text-slate-700">{{ formatDate(invoice.due_date) }}</p>
                        </div>
                    </div>
                    <div class="hidden sm:block h-8 w-px bg-slate-200"></div>
                    <div class="flex items-center gap-4">
                        <div class="p-2 bg-indigo-50 rounded-lg border border-indigo-100">
                            <FileText class="w-5 h-5 text-indigo-600" />
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ t('invoices.total') }}</p>
                            <p class="font-black text-indigo-600 text-xl">{{ formatCurrency(invoice.total) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="px-8 sm:px-12 pb-12">
                    <table class="w-full text-sm border-collapse">
                        <thead>
                            <tr class="border-b-2 border-slate-900">
                                <th class="py-4 text-left font-black uppercase tracking-widest text-[10px] text-slate-400">{{ t('invoices.description_col') }}</th>
                                <th class="py-4 text-center font-black uppercase tracking-widest text-[10px] text-slate-400">{{ t('invoices.qty') }}</th>
                                <th class="py-4 text-right font-black uppercase tracking-widest text-[10px] text-slate-400">{{ t('invoices.unit_price') }}</th>
                                <th class="py-4 text-right font-black uppercase tracking-widest text-[10px] text-slate-400">{{ t('invoices.total_ht') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in invoice.items" :key="item.id">
                                <td class="py-5 pr-4">
                                    <p class="font-bold text-slate-900">{{ item.description }}</p>
                                </td>
                                <td class="py-5 text-center text-slate-600 font-medium">{{ Number(item.quantity) }}</td>
                                <td class="py-5 text-right text-slate-600 font-medium">{{ Number(item.unit_price).toFixed(2) }}{{ settings.currency || '€' }}</td>
                                <td class="py-5 text-right font-black text-slate-900">{{ (item.quantity * item.unit_price).toFixed(2) }}{{ settings.currency || '€' }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="py-12 pr-12">
                                    <div v-if="invoice.notes" class="p-6 rounded-2xl bg-amber-50/50 border border-amber-100 text-slate-600 text-xs italic space-y-2">
                                        <p class="font-bold uppercase tracking-widest text-[9px] text-amber-600 not-italic">{{ t('invoices.notes_title') }}</p>
                                        {{ invoice.notes }}
                                    </div>
                                </td>
                                <td colspan="2" class="py-12">
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center text-sm font-medium">
                                            <span class="text-slate-500">{{ t('invoices.subtotal_ht') }}</span>
                                            <span class="text-slate-900">{{ formatCurrency(invoice.subtotal) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center text-sm font-medium" v-if="settings.tax1_name && invoice.tax1_amount > 0">
                                            <span class="text-slate-500">{{ settings.tax1_name }}</span>
                                            <span class="text-slate-900">{{ formatCurrency(invoice.tax1_amount) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center text-sm font-medium" v-if="settings.tax2_name && invoice.tax2_amount > 0">
                                            <span class="text-slate-500">{{ settings.tax2_name }}</span>
                                            <span class="text-slate-900">{{ formatCurrency(invoice.tax2_amount) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center pt-4 border-t-2 border-slate-900">
                                            <span class="font-black text-slate-900 uppercase">{{ t('invoices.total_ttc') }}</span>
                                            <span class="text-3xl font-black text-indigo-600">{{ formatCurrency(invoice.total) }}</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Footer / Legal -->
                <div class="bg-slate-900 p-8 text-center text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] space-y-1">
                    <p>{{ t('invoices.thank_you') }}</p>
                    <p>{{ t('invoices.payment_terms') }}</p>
                    <div v-if="settings.legal_mentions && settings.legal_mentions.length > 0" class="pt-4 mt-4 border-t border-slate-800 flex flex-wrap justify-center gap-x-4 gap-y-2 text-[9px] font-medium opacity-80">
                        <span v-for="(mention, index) in settings.legal_mentions" :key="index">
                            <span class="font-bold tracking-widest text-slate-400">{{ mention.key }}</span> : {{ mention.value }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #print-area, #print-area * {
        visibility: visible;
    }
    #print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100vw;
        max-width: 100%;
        margin: 0;
        padding: 0;
    }
    @page {
        margin: 0.5cm;
    }
}
</style>
