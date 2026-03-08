<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { 
    Printer, 
    CheckCircle2, 
    Clock, 
    AlertCircle, 
    Ban,
    FileText,
    Calendar,
    Mail,
    Phone,
    MapPin,
    Building2,
    Send
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

interface InvoiceItem {
    description: string;
    quantity: number;
    unit_price: number;
    total: number;
}

interface Props {
    invoice: {
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
    };
    client: {
        name: string;
        email: string | null;
    };
    company: {
        name: string;
        email: string | null;
        phone: string | null;
        address: string | null;
        logo_path: string | null;
        primary_color: string;
    };
    settings: {
        tax1_name: string | null;
        tax1_rate: number;
        tax2_name: string | null;
        tax2_rate: number;
        currency: string;
        legal_mentions?: { key: string; value: string }[];
        terms_and_conditions: string | null;
    };
}

const props = defineProps<Props>();

const print = () => {
    window.print();
};

const statuses = computed(() => ({
    draft: { label: 'Brouillon', icon: Clock, color: 'bg-slate-100 text-slate-600 border-slate-200' },
    sent: { label: 'Envoyée', icon: Send, color: 'bg-blue-100 text-blue-700 border-blue-200' },
    paid: { label: 'Payée', icon: CheckCircle2, color: 'bg-emerald-100 text-emerald-700 border-emerald-200' },
    overdue: { label: 'En retard', icon: AlertCircle, color: 'bg-red-100 text-red-700 border-red-200' },
    cancelled: { label: 'Annulée', icon: Ban, color: 'bg-slate-100 text-slate-400 border-slate-200' },
}));

const formatDate = (date: string) => new Date(date).toLocaleDateString('fr-FR');
const formatCurrency = (val: number) => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(val);

</script>

<template>
    <Head :title="`Facture ${invoice.invoice_number} — ${company.name}`" />

    <div class="min-h-screen bg-slate-50 py-8 px-4 sm:px-6 lg:px-8 font-sans print:py-0 print:px-0 print:bg-white text-slate-900">
        <div class="max-w-4xl mx-auto">
            
            <!-- Public Action Bar -->
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 leading-tight">Facture {{ invoice.invoice_number }}</h1>
                    <p class="text-sm text-slate-500 mt-1">Émise par <span class="font-medium text-slate-700">{{ company.name }}</span></p>
                </div>
                
                <div class="flex items-center gap-2">
                    <span :class="statuses[invoice.status].color" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border">
                        <component :is="statuses[invoice.status].icon" class="w-3.5 h-3.5 shrink-0" />
                        {{ statuses[invoice.status].label }}
                    </span>
                    <Button variant="outline" class="gap-2 bg-white" @click="print">
                        <Printer class="w-4 h-4" /> Imprimer / PDF
                    </Button>
                </div>
            </div>

            <!-- Invoice Document -->
            <div id="print-area" class="bg-white shadow-xl border border-slate-200 rounded-2xl overflow-hidden print:shadow-none print:border-none print:rounded-none">
                <!-- Header / Logo -->
                <div class="p-8 sm:p-12 border-b border-slate-100 flex flex-col sm:flex-row justify-between gap-8" :style="{ borderTop: `8px solid ${company.primary_color}` }">
                    <div>
                        <div v-if="company.logo_path" class="w-24 h-24 mb-6 relative border border-slate-100 rounded-2xl flex items-center justify-center p-2">
                            <img :src="`/storage/${company.logo_path}`" alt="Logo" class="max-w-full max-h-full object-contain" />
                        </div>
                        <div v-else class="w-24 h-24 mb-6 bg-slate-50 rounded-2xl flex items-center justify-center border border-slate-100" :style="{ backgroundColor: company.primary_color + '10' }">
                            <Building2 class="w-10 h-10" :style="{ color: company.primary_color }" />
                        </div>
                        <h2 class="text-2xl font-black text-slate-900 tracking-tight">{{ company.name }}</h2>
                        <ul class="mt-4 space-y-2 text-sm text-slate-500 font-medium">
                            <li v-if="company.email" class="flex items-center gap-2"><Mail class="w-4 h-4 text-slate-400" /> {{ company.email }}</li>
                            <li v-if="company.phone" class="flex items-center gap-2"><Phone class="w-4 h-4 text-slate-400" /> {{ company.phone }}</li>
                        </ul>
                    </div>
                    <div class="text-left sm:text-right">
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-2">Facture Nº</p>
                        <p class="text-4xl font-black text-slate-900 tracking-tighter mb-6">{{ invoice.invoice_number }}</p>
                        <div class="flex flex-col gap-1 inline-block text-left sm:text-right">
                            <div class="bg-slate-50 border border-slate-100 rounded-lg px-4 py-2 flex sm:justify-end gap-3 text-sm font-medium items-center">
                                <span class="text-slate-400">Date d'émission:</span>
                                <span class="text-slate-900">{{ formatDate(invoice.issue_date) }}</span>
                            </div>
                            <div class="bg-indigo-50/50 border border-indigo-100 rounded-lg px-4 py-2 flex sm:justify-end gap-3 text-sm font-medium items-center text-indigo-900" v-if="invoice.due_date">
                                <span class="text-indigo-600/70">Échéance:</span>
                                <span class="font-bold">{{ formatDate(invoice.due_date) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Client Info -->
                <div class="p-8 sm:p-12 relative">
                    <div class="absolute inset-0 bg-slate-50/50 border-b border-slate-100"></div>
                    <div class="relative grid grid-cols-1 sm:grid-cols-2 gap-8">
                        <div>
                            <span class="text-[11px] font-black uppercase tracking-widest text-slate-400 block mb-3">Facturé à</span>
                            <div class="bg-white p-5 rounded-xl border border-slate-200/60 shadow-sm">
                                <p class="text-lg font-black text-slate-900">{{ client.name }}</p>
                                <p v-if="client.email" class="text-sm text-slate-500 mt-1 flex items-center gap-1.5"><Mail class="w-3.5 h-3.5 opacity-70" /> {{ client.email }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="p-8 sm:p-12">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b-2 border-slate-900">
                                <th class="py-3 text-[11px] font-black text-slate-900 uppercase tracking-widest w-1/2">Description</th>
                                <th class="py-3 text-[11px] font-black text-slate-900 uppercase tracking-widest text-center">Qté</th>
                                <th class="py-3 text-[11px] font-black text-slate-900 uppercase tracking-widest text-right">Prix Unitaire</th>
                                <th class="py-3 text-[11px] font-black text-slate-900 uppercase tracking-widest text-right">Total HT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(item, index) in invoice.items" :key="index" class="group">
                                <td class="py-4 text-sm text-slate-800 font-medium whitespace-pre-wrap pr-4">{{ item.description }}</td>
                                <td class="py-4 text-sm text-slate-600 font-semibold text-center">{{ item.quantity }}</td>
                                <td class="py-4 text-sm text-slate-600 text-right">{{ formatCurrency(item.unit_price) }}</td>
                                <td class="py-4 text-sm font-bold text-slate-900 text-right">{{ formatCurrency(item.total) }}</td>
                            </tr>
                        </tbody>
                        
                        <!-- Totals Footer -->
                        <tfoot>
                            <tr>
                                <td colspan="2" class="py-8 pr-8 align-top">
                                    <div v-show="invoice.notes" class="bg-yellow-50/50 border border-yellow-200/50 p-4 rounded-xl">
                                        <span class="text-[10px] font-black uppercase tracking-widest text-yellow-800 block mb-2">Notes</span>
                                        <p class="text-sm text-yellow-900/80 leading-relaxed whitespace-pre-line">{{ invoice.notes }}</p>
                                    </div>
                                    <div v-show="settings.terms_and_conditions" class="mt-4">
                                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 block mb-2">Conditions Générales</span>
                                        <p class="text-xs text-slate-500 leading-relaxed whitespace-pre-line">{{ settings.terms_and_conditions }}</p>
                                    </div>
                                </td>
                                <td colspan="2" class="py-12">
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center text-sm font-medium">
                                            <span class="text-slate-500">Sous-total HT</span>
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
                                            <span class="font-black text-slate-900 uppercase">Total TTC</span>
                                            <span class="text-3xl font-black" :style="{ color: company.primary_color }">{{ formatCurrency(invoice.total) }}</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Footer / Legal -->
                <div class="bg-slate-50 border-t border-slate-200 p-8 text-center text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] space-y-1">
                    <p>Merci pour votre confiance</p>
                    <p>A payer selon les conditions d'échéance</p>
                    <div v-if="settings.legal_mentions && settings.legal_mentions.length > 0" class="pt-4 mt-4 border-t border-slate-200 flex flex-wrap justify-center gap-x-4 gap-y-2 text-[9px] font-medium opacity-80">
                        <span v-for="(mention, index) in settings.legal_mentions" :key="index">
                            <span class="font-bold tracking-widest text-slate-600">{{ mention.key }}</span> : {{ mention.value }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
