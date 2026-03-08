<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { 
    ChevronLeft, 
    Plus, 
    Trash2, 
    Calculator,
    Calendar,
    FileText,
    Settings2,
    Save,
    User
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { BreadcrumbItem } from '@/types';

interface Item {
    description: string;
    quantity: number;
    unit_price: number;
}

interface Props {
    client?: {
        id: number;
        name: string;
        email: string;
    };
    clients?: Array<{
        id: number;
        name: string;
        company_name: string;
    }>;
    settings: {
        invoice_prefix: string;
        tax1_name: string;
        tax1_rate: number;
        tax2_name: string;
        tax2_rate: number;
        currency: string;
        terms_and_conditions: string;
    };
    suggestedNumber: string;
}

const props = defineProps<Props>();

const form = useForm({
    client_id: props.client?.id || '',
    invoice_number: props.suggestedNumber,
    status: 'draft',
    issue_date: new Date().toISOString().split('T')[0],
    due_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
    notes: '',
    tax1_rate: props.settings.tax1_rate || 0,
    tax2_rate: props.settings.tax2_rate || 0,
    items: [
        { description: '', quantity: 1, unit_price: 0 }
    ] as Item[]
});

const addItem = () => {
    form.items.push({ description: '', quantity: 1, unit_price: 0 });
};

const removeItem = (index: number) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const subtotal = computed(() => {
    return form.items.reduce((acc, item) => acc + (item.quantity * item.unit_price), 0);
});

const tax1Amount = computed(() => {
    return subtotal.value * ((form.tax1_rate || 0) / 100);
});

const tax2Amount = computed(() => {
    return subtotal.value * ((form.tax2_rate || 0) / 100);
});

const total = computed(() => {
    return subtotal.value + tax1Amount.value + tax2Amount.value;
});

const submit = () => {
    form.post('/invoices');
};

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Factures', href: '/invoices' },
    { title: 'Nouvelle Facture', href: '#' },
]);
</script>

<template>
    <Head title="Créer une Facture" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header Nav -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Nouvelle Facture</h1>
                    <p class="text-slate-500 mt-1" v-if="client">Émission d'une facture pour <span class="font-semibold text-slate-700">{{ client.name }}</span></p>
                    <p class="text-slate-500 mt-1" v-else>Sélectionnez un client pour générer une facture.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Button variant="outline" as-child>
                        <Link :href="client ? `/clients/${client.id}` : '/invoices'">Annuler</Link>
                    </Button>
                    <Button @click="submit" class="bg-indigo-600 hover:bg-indigo-700 gap-2 shadow-lg shadow-indigo-100" :disabled="form.processing">
                        <Save class="w-4 h-4" />
                        {{ form.processing ? 'Génération...' : 'Générer la Facture' }}
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
                <!-- Client Selection & Settings -> lg:col-span-8 -->
                <div class="lg:col-span-8 space-y-6">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Client -->
                        <div v-if="!client" class="md:col-span-2">
                            <label class="text-[11px] font-bold uppercase text-slate-400 block mb-1.5 ml-1">Destinataire *</label>
                            <select v-model="form.client_id" class="w-full rounded-xl border-slate-200 bg-slate-50 h-11 px-4 text-slate-900 font-medium focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer">
                                <option value="" disabled>Choisir un client...</option>
                                <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }} {{ c.company_name ? `(${c.company_name})` : '' }}</option>
                            </select>
                            <p v-if="form.errors.client_id" class="text-red-500 text-xs mt-1.5 ml-1">{{ form.errors.client_id }}</p>
                        </div>
                        <div v-else class="md:col-span-2 flex items-center gap-4 p-4 bg-indigo-50/50 rounded-xl border border-indigo-100/50">
                             <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-indigo-500 shadow-sm border border-indigo-100">
                                 <User class="w-6 h-6" />
                             </div>
                             <div>
                                 <p class="font-bold text-slate-900 text-lg leading-tight mb-0.5">{{ client.name }}</p>
                                 <p class="text-sm font-medium text-indigo-600/80">{{ client.email || 'Aucun email' }}</p>
                             </div>
                        </div>

                        <div>
                            <label class="text-[11px] font-bold uppercase text-slate-400 block mb-1.5 ml-1">Numéro de facture</label>
                            <Input v-model="form.invoice_number" class="bg-slate-50 border-slate-200 font-mono font-medium text-sm h-11 text-slate-900 focus:bg-white" />
                        </div>
                        <div>
                            <label class="text-[11px] font-bold uppercase text-slate-400 block mb-1.5 ml-1">Statut Initial</label>
                            <select v-model="form.status" class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm h-11 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 font-medium text-slate-900 cursor-pointer">
                                <option value="draft">Brouillon</option>
                                <option value="sent">Envoyée</option>
                                <option value="paid">Payée</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-[11px] font-bold uppercase text-slate-400 block mb-1.5 ml-1">Date d'émission</label>
                            <Input type="date" v-model="form.issue_date" class="bg-slate-50 border-slate-200 font-medium h-11 text-slate-900 focus:bg-white" />
                        </div>
                        <div>
                            <label class="text-[11px] font-bold uppercase text-slate-400 block mb-1.5 ml-1">Échéance</label>
                            <Input type="date" v-model="form.due_date" class="bg-slate-50 border-slate-200 font-medium h-11 text-slate-900 focus:bg-white" />
                        </div>
                    </div>
                </div>

                <!-- Total Amount Card -> lg:col-span-4 -->
                <div class="lg:col-span-4 flex flex-col h-full space-y-6">
                    <div class="bg-gradient-to-br from-indigo-600 to-indigo-700 rounded-2xl p-6 text-white shadow-[0_8px_30px_rgb(79,70,229,0.2)] group relative overflow-hidden flex-1 flex flex-col justify-center border border-indigo-400/20">
                        <div class="relative z-10 text-center">
                            <h3 class="font-bold text-xs mb-2 text-indigo-200 uppercase tracking-[0.2em]">Total Facture (TTC)</h3>
                            <p class="text-4xl md:text-5xl font-black tracking-tight drop-shadow-sm">{{ total.toFixed(2) }}<span class="text-2xl ml-1 font-bold text-indigo-300">{{ settings.currency || '€' }}</span></p>
                        </div>
                        <div class="absolute -right-12 -bottom-12 w-40 h-40 bg-white/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700 ease-out"></div>
                        <div class="absolute -left-8 -top-8 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                    </div>
                </div>
            </div>

            <!-- Items Section (Full width) -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
                <!-- table header -->
                <div class="hidden sm:grid grid-cols-12 gap-4 p-4 border-b border-slate-100 bg-slate-50/80 text-[10px] font-black uppercase tracking-widest text-slate-400">
                    <div class="col-span-6 pl-2 lg:col-span-7">Désignation de la prestation</div>
                    <div class="col-span-1 text-center">Qté</div>
                    <div class="col-span-2 text-right">Prix Unitaire</div>
                    <div class="col-span-2 text-right pr-6 lg:col-span-2">Total HT</div>
                </div>

                <div class="p-2 sm:p-4 bg-slate-50/30 space-y-2">
                    <div v-for="(item, index) in form.items" :key="index" class="relative group grid grid-cols-1 sm:grid-cols-12 gap-3 sm:gap-4 p-4 sm:p-2 rounded-xl bg-white sm:bg-transparent border border-slate-100 sm:border-transparent hover:bg-white hover:border-slate-200 hover:shadow-sm transition-all items-center">
                        <div class="col-span-1 sm:col-span-6 lg:col-span-7">
                            <label class="sm:hidden text-[11px] font-bold uppercase text-slate-400 mb-1.5 block">Description</label>
                            <Input v-model="item.description" type="text" placeholder="Description détaillée de la ligne..." class="h-11 bg-slate-50/50 border-slate-200 shadow-none font-medium placeholder:text-slate-400/70 focus:bg-white" />
                        </div>
                        
                        <div class="col-span-1 sm:col-span-1">
                            <label class="sm:hidden text-[11px] font-bold uppercase text-slate-400 mb-1.5 block">Qté</label>
                            <Input v-model.number="item.quantity" type="number" min="0" step="0.01" class="h-11 text-center bg-slate-50/50 border-slate-200 shadow-none font-semibold focus:bg-white" />
                        </div>

                        <div class="col-span-1 sm:col-span-2 relative">
                            <label class="sm:hidden text-[11px] font-bold uppercase text-slate-400 mb-1.5 block">Prix Unit.</label>
                            <div class="relative">
                                <Input v-model.number="item.unit_price" type="number" min="0" step="0.01" class="h-11 text-right pr-7 bg-slate-50/50 border-slate-200 shadow-none font-semibold focus:bg-white" />
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">{{ settings.currency || '€' }}</span>
                            </div>
                        </div>

                        <div class="col-span-1 sm:col-span-2 lg:col-span-2 flex items-center justify-between sm:justify-end sm:pr-8 pt-2 sm:pt-0">
                            <label class="sm:hidden text-[11px] font-bold uppercase text-slate-400">Total Ligne HT</label>
                            <div class="font-black text-slate-800 text-lg tabular-nums">
                                {{ (item.quantity * item.unit_price).toFixed(2) }}<span class="text-xs ml-0.5 text-slate-400 font-bold">{{ settings.currency || '€' }}</span>
                            </div>
                        </div>

                        <button @click="removeItem(index)" type="button" class="absolute right-2 top-2 sm:top-1/2 sm:-translate-y-1/2 w-8 h-8 flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors border border-transparent hover:border-red-100 opacity-100 sm:opacity-0 group-hover:opacity-100" title="Supprimer la ligne">
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>

                    <div class="p-2 pt-4">
                        <button @click="addItem" type="button" class="w-full h-12 flex items-center justify-center gap-2 border-2 border-dashed border-slate-300 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50/50 hover:border-indigo-300 transition-all rounded-xl group font-semibold text-sm bg-white">
                            <Plus class="w-4 h-4" /> Ajouter une ligne descriptive
                        </button>
                    </div>
                </div>

                <!-- Summary Footer -->
                <div class="p-6 bg-white border-t border-slate-100 flex flex-col sm:flex-row justify-between items-start gap-6">
                    <div class="w-full sm:w-1/2">
                        <label class="text-[11px] font-bold uppercase text-slate-400 block mb-2 px-1">Notes ou remarques complémentaires sur la facture</label>
                        <textarea v-model="form.notes" rows="4" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white text-sm p-4 text-slate-900 transition-colors focus:ring-indigo-500 focus:border-indigo-500" placeholder="Notes visibles par le client sur le document final..."></textarea>
                    </div>
                    
                    <div class="w-full sm:w-80 space-y-3 bg-slate-50/80 p-5 rounded-2xl border border-slate-100">
                        <div class="flex justify-between items-center text-sm font-bold text-slate-900 leading-tight">
                            <span>Sous-total HT</span>
                            <span class="font-bold text-slate-700 tabular-nums">{{ subtotal.toFixed(2) }} {{ settings.currency || '€' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm font-bold text-slate-900 leading-tight" v-if="settings.tax1_name">
                            <div class="flex items-center gap-2">
                                <span class="uppercase text-[11px] text-slate-500">{{ settings.tax1_name }}</span>
                                <Input type="number" step="0.001" v-model.number="form.tax1_rate" class="h-7 w-20 text-right text-xs px-2 py-0 border-slate-200" />
                                <span class="text-xs text-slate-400">%</span>
                            </div>
                            <span class="font-bold text-slate-700 tabular-nums text-right">{{ tax1Amount.toFixed(2) }} {{ settings.currency || '€' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm font-bold text-slate-900 leading-tight" v-if="settings.tax2_name">
                            <div class="flex items-center gap-2">
                                <span class="uppercase text-[11px] text-slate-500">{{ settings.tax2_name }}</span>
                                <Input type="number" step="0.001" v-model.number="form.tax2_rate" class="h-7 w-20 text-right text-xs px-2 py-0 border-slate-200" />
                                <span class="text-xs text-slate-400">%</span>
                            </div>
                            <span class="font-bold text-slate-700 tabular-nums text-right">{{ tax2Amount.toFixed(2) }} {{ settings.currency || '€' }}</span>
                        </div>
                        <div class="pt-4 mt-2 border-t border-slate-200 flex justify-between items-end">
                            <span class="text-xs font-black text-slate-900 uppercase tracking-widest leading-none mb-1">Total TTC</span>
                            <span class="text-3xl font-black text-indigo-600 tabular-nums leading-none">{{ total.toFixed(2) }}<span class="text-base font-bold ml-1 opacity-70">{{ settings.currency || '€' }}</span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
