<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
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
    Send,
    ExternalLink,
    AlertTriangle,
    Mail
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    AlertDialog,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import type { BreadcrumbItem } from '@/types';

interface Item {
    description: string;
    quantity: number;
    unit_price: number;
}

interface QuotePayload {
    id: number;
    public_uid: string;
    quote_number: string;
    status: string;
    subtotal: number;
    tax1_amount: number;
    tax2_amount: number;
    total: number;
    expire_at: string | null;
    notes: string | null;
    terms: string | null;
    items: Array<{
        id: number;
        description: string;
        quantity: number;
        unit_price: number;
    }>;
}

interface Props {
    quote: QuotePayload;
    lead: {
        id: number;
        name: string;
        email: string;
    };
    settings: {
        quote_prefix: string;
        tax1_name: string;
        tax1_rate: number;
        tax2_name: string;
        tax2_rate: number;
        currency: string;
        terms_and_conditions: string;
    };
}

const props = defineProps<Props>();

const form = useForm({
    quote_number: props.quote.quote_number,
    status: props.quote.status,
    expire_at: props.quote.expire_at ?? '',
    notes: props.quote.notes ?? '',
    terms: props.quote.terms ?? '',
    tax1_rate: props.settings.tax1_rate || 0,
    tax2_rate: props.settings.tax2_rate || 0,
    items: props.quote.items.map(i => ({
        description: i.description,
        quantity: i.quantity,
        unit_price: i.unit_price,
    })) as Item[]
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

const tax1Amount = computed(() => subtotal.value * (form.tax1_rate / 100));
const tax2Amount = computed(() => subtotal.value * (form.tax2_rate / 100));
const total = computed(() => subtotal.value + tax1Amount.value + tax2Amount.value);

const submit = () => {
    form.put(`/quotes/${props.quote.id}`);
};

const confirmAction = ref<'deleteQuote' | 'sendEmail' | null>(null);

const confirmDeleteQuote = () => confirmAction.value = 'deleteQuote';
const confirmSendEmail = () => confirmAction.value = 'sendEmail';

const executeConfirm = () => {
    if (confirmAction.value === 'deleteQuote') {
        router.delete(`/quotes/${props.quote.id}`, {
            onFinish: () => confirmAction.value = null
        });
    } else if (confirmAction.value === 'sendEmail') {
        sendingEmail.value = true;
        router.post(`/quotes/${props.quote.id}/send`, {}, {
            preserveScroll: true,
            onFinish: () => {
                sendingEmail.value = false;
                confirmAction.value = null;
            },
        });
    }
};

const statusLabels: Record<string, { label: string; class: string }> = {
    draft: { label: 'Brouillon', class: 'bg-slate-100 text-slate-600' },
    sent: { label: 'Envoyé', class: 'bg-blue-100 text-blue-700' },
    accepted: { label: 'Accepté', class: 'bg-emerald-100 text-emerald-700' },
    rejected: { label: 'Refusé', class: 'bg-red-100 text-red-700' },
    expired: { label: 'Expiré', class: 'bg-amber-100 text-amber-700' },
};

const publicUrl = computed(() => `${globalThis.location?.origin ?? ''}/devis/${props.quote.public_uid}`);

const copyLink = () => {
    navigator.clipboard.writeText(publicUrl.value);
};

const sendingEmail = ref(false);

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Leads', href: '/leads' },
    { title: props.lead.name || `Lead #${props.lead.id}`, href: `/leads/${props.lead.id}` },
    { title: `Devis ${props.quote.quote_number}`, href: '#' },
]);
</script>

<template>
    <Head :title="`Modifier ${quote.quote_number}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header Nav -->
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">{{ quote.quote_number }}</h1>
                        <Badge :class="statusLabels[quote.status]?.class ?? 'bg-slate-100'" class="text-xs uppercase font-bold px-2.5 py-0.5">
                            {{ statusLabels[quote.status]?.label ?? quote.status }}
                        </Badge>
                    </div>
                    <p class="text-slate-500 mt-1">Devis pour <span class="font-semibold text-slate-700">{{ lead.name }}</span> <span v-if="lead.email" class="text-slate-400 text-sm border bg-slate-50 px-2 py-0.5 rounded">{{ lead.email }}</span></p>
                </div>
                <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                    <Button variant="outline" class="gap-2 text-red-500 border-red-200 hover:bg-red-50 hover:text-red-600" @click="confirmDeleteQuote">
                        <Trash2 class="w-4 h-4" /> Supprimer
                    </Button>
                    <Button variant="outline" as-child>
                        <Link :href="`/leads/${lead.id}`">Annuler</Link>
                    </Button>
                    <Button @click="submit" class="bg-indigo-600 hover:bg-indigo-700 gap-2 shadow-lg shadow-indigo-100" :disabled="form.processing">
                        <Save class="w-4 h-4" />
                        {{ form.processing ? 'Enregistrement...' : 'Sauvegarder' }}
                    </Button>
                    <Button 
                        @click="confirmSendEmail" 
                        class="bg-emerald-600 hover:bg-emerald-700 text-white gap-2 shadow-lg shadow-emerald-100 min-w-40" 
                        :disabled="sendingEmail || !lead.email"
                        :title="!lead.email ? 'Le prospect n\'a pas d\'adresse e-mail' : 'Envoyer par e-mail'"
                    >
                        <template v-if="sendingEmail">
                            <span class="w-4 h-4 rounded-full border-2 border-white/40 border-t-white animate-spin"></span>
                            Envoi...
                        </template>
                        <template v-else>
                            <Mail class="w-4 h-4" />
                            Envoyer au client
                        </template>
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Form Area -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Items Section -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
                        <div class="p-6 border-b border-slate-100 bg-white flex items-center justify-between">
                            <h2 class="font-bold text-slate-800 flex items-center gap-2 text-lg">
                                <FileText class="w-5 h-5 text-indigo-500" /> Prestations & Tarification
                            </h2>
                        </div>
                        
                        <div class="p-4 sm:p-6 bg-slate-50/50 space-y-4">
                            <div v-for="(item, index) in form.items" :key="index" class="relative flex flex-col sm:flex-row sm:items-start gap-4 p-5 rounded-2xl border border-slate-200 bg-white shadow-sm group hover:border-indigo-200 hover:shadow-md transition-all">
                                <!-- Order handle visual -->
                                <div class="hidden sm:flex h-11 w-6 items-center justify-center text-slate-200 mt-6 cursor-move">
                                    <div class="w-1.5 h-6 border-l-2 border-r-2 border-dotted border-slate-300 rounded"></div>
                                </div>

                                <div class="w-full sm:flex-1">
                                    <label class="text-[11px] uppercase tracking-wider font-bold text-slate-400 mb-2 block">Description de la prestation</label>
                                    <textarea v-model="item.description" rows="2" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 text-sm resize-none p-3 transition-colors shadow-inner" placeholder="Détail de l'article ou du service..."></textarea>
                                </div>
                                
                                <div class="flex gap-4 sm:gap-4 w-full sm:w-auto">
                                    <div class="flex-1 sm:w-24">
                                        <label class="text-[11px] uppercase tracking-wider font-bold text-slate-400 mb-2 block text-center">Qté</label>
                                        <Input type="number" v-model.number="item.quantity" class="h-11 text-center bg-slate-50 focus:bg-white rounded-xl font-semibold text-lg border-slate-200 shadow-inner" />
                                    </div>
                                    <div class="flex-1 sm:w-36">
                                        <label class="text-[11px] uppercase tracking-wider font-bold text-slate-400 mb-2 block text-right">Prix Unité</label>
                                        <div class="relative">
                                            <Input type="number" step="0.01" v-model.number="item.unit_price" class="h-11 pr-10 text-right font-semibold text-lg bg-slate-50 focus:bg-white rounded-xl border-slate-200 shadow-inner" />
                                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold bg-transparent">{{ settings.currency }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between sm:block w-full sm:w-32 pt-2 sm:pt-0">
                                    <label class="sm:mb-2 block text-[11px] uppercase tracking-wider font-bold text-slate-400 text-left sm:text-right">Total Ligne</label>
                                    <div class="h-11 flex items-center justify-end font-black text-slate-800 text-xl">
                                        {{ (item.quantity * item.unit_price).toFixed(2) }}<span class="text-sm ml-1 text-slate-400 font-bold">{{ settings.currency }}</span>
                                    </div>
                                </div>

                                <div class="absolute right-2 top-2 sm:static sm:pt-6 sm:ml-2">
                                    <button @click="removeItem(index)" type="button" class="h-8 w-8 sm:h-11 sm:w-11 flex items-center justify-center text-slate-300 hover:text-red-600 hover:bg-red-50 rounded-xl transition-colors border border-transparent hover:border-red-100" title="Supprimer">
                                        <Trash2 class="w-4 h-4 sm:w-5 sm:h-5" />
                                    </button>
                                </div>
                            </div>
                            
                            <button @click="addItem" type="button" class="w-full py-6 flex flex-col items-center justify-center gap-2 border-2 border-dashed border-slate-300 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50/50 hover:border-indigo-300 transition-all rounded-2xl group shadow-sm bg-white">
                                <div class="w-10 h-10 rounded-full bg-slate-100 group-hover:bg-indigo-100 flex items-center justify-center transition-colors">
                                    <Plus class="w-5 h-5" />
                                </div>
                                <span class="font-bold text-sm tracking-wide">Ajouter une nouvelle prestation</span>
                            </button>
                        </div>

                        <!-- Summary Footer -->
                        <div class="p-6 bg-white border-t border-slate-100 flex justify-end">
                            <div class="w-full sm:w-80 space-y-3">
                                <div class="flex justify-between items-center text-sm text-slate-500">
                                    <span>Sous-total HT</span>
                                    <span class="font-bold text-slate-700">{{ subtotal.toFixed(2) }}{{ settings.currency }}</span>
                                </div>
                                <div v-if="settings.tax1_name" class="flex justify-between items-center text-sm text-slate-500">
                                    <span>{{ settings.tax1_name }} ({{ form.tax1_rate }}%)</span>
                                    <span class="font-bold text-slate-700">{{ tax1Amount.toFixed(2) }}{{ settings.currency }}</span>
                                </div>
                                <div v-if="settings.tax2_name" class="flex justify-between items-center text-sm text-slate-500">
                                    <span>{{ settings.tax2_name }} ({{ form.tax2_rate }}%)</span>
                                    <span class="font-bold text-slate-700">{{ tax2Amount.toFixed(2) }}{{ settings.currency }}</span>
                                </div>
                                <div class="pt-4 mt-2 border-t border-slate-200 flex justify-between items-end">
                                    <span class="text-sm font-bold text-slate-900 uppercase tracking-widest">Total TTC</span>
                                    <span class="text-2xl font-black text-indigo-600">{{ total.toFixed(2) }}<span class="text-base font-bold ml-1 opacity-70">{{ settings.currency }}</span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms Section -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                        <h2 class="font-bold text-slate-800 flex items-center gap-2 mb-4">
                            <Settings2 class="w-5 h-5 text-indigo-500" /> Conditions & Notes Clients
                        </h2>
                        <textarea v-model="form.terms" rows="4" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white transition-all text-sm p-4" placeholder="Conditions de paiement, délais, etc..."></textarea>
                    </div>
                </div>

                <!-- Sidebar Settings -->
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
                        <h2 class="font-bold text-slate-800 flex items-center gap-2 mb-2">
                            <Settings2 class="w-5 h-5 text-indigo-500" /> Paramètres
                        </h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="text-[11px] font-bold uppercase text-slate-400 block mb-1.5 ml-1">Numéro du devis</label>
                                <Input v-model="form.quote_number" class="bg-slate-50 border-slate-100 font-mono text-sm h-10" />
                            </div>

                            <div>
                                <label class="text-[11px] font-bold uppercase text-slate-400 block mb-1.5 ml-1">Date d'expiration</label>
                                <div class="relative">
                                    <Calendar class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                    <Input type="date" v-model="form.expire_at" class="pl-10 bg-slate-50 border-slate-100 h-10" />
                                </div>
                            </div>

                            <div>
                                <label class="text-[11px] font-bold uppercase text-slate-400 block mb-1.5 ml-1">Statut</label>
                                <select v-model="form.status" class="w-full rounded-md border-slate-100 bg-slate-50 text-sm h-10 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="draft">Brouillon</option>
                                    <option value="sent">Envoyé</option>
                                    <option value="accepted">Accepté</option>
                                    <option value="rejected">Refusé</option>
                                    <option value="expired">Expiré</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Total Card -->
                    <div class="bg-indigo-600 rounded-2xl p-6 text-white shadow-lg shadow-indigo-200 group relative overflow-hidden">
                        <div class="relative z-10">
                            <div class="bg-white/20 w-10 h-10 rounded-lg flex items-center justify-center mb-4">
                                <Calculator class="w-6 h-6" />
                            </div>
                            <h3 class="font-bold text-lg mb-1 italic">Total estimé</h3>
                            <p class="text-3xl font-black">{{ total.toFixed(2) }}{{ settings.currency }}</p>
                            <p class="text-indigo-100 text-xs mt-2 opacity-80">Inclut {{ settings.tax1_name }} et {{ settings.tax2_name }}</p>
                        </div>
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                    </div>

                    <!-- Internal Notes -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                        <h2 class="font-bold text-slate-800 flex items-center gap-2 mb-4">
                            <FileText class="w-5 h-5 text-indigo-500" /> Notes Internes
                        </h2>
                        <textarea v-model="form.notes" rows="3" class="w-full rounded-xl border-slate-200 bg-slate-200/30 text-sm p-4 italic" placeholder="Ces notes ne seront pas visibles par le client..."></textarea>
                    </div>

                    <!-- Public Link Card -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                        <h2 class="font-bold text-slate-800 flex items-center gap-2 mb-3">
                            <ExternalLink class="w-5 h-5 text-indigo-500" /> Lien Public
                        </h2>
                        <p class="text-xs text-slate-500 mb-3">
                            Partagez ce lien avec votre client pour qu'il consulte et accepte le devis.
                        </p>
                        <div class="flex items-center gap-2">
                            <code class="flex-1 text-[11px] text-indigo-700 bg-indigo-50 p-3 rounded-lg break-all border border-indigo-100 font-medium">
                                {{ publicUrl }}
                            </code>
                            <button @click="copyLink" class="shrink-0 p-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors" title="Copier le lien">
                                <ExternalLink class="w-4 h-4" />
                            </button>
                        </div>
                        <a :href="`/devis/${quote.public_uid}`" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-indigo-600 font-medium mt-3 hover:underline">
                            <ExternalLink class="w-3 h-3" /> Aperçu public
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shadcn UI Global Confirmation Dialog -->
        <AlertDialog :open="confirmAction !== null" @update:open="(val) => { if (!val) confirmAction = null }">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>
                        {{ confirmAction === 'deleteQuote' ? 'Supprimer le devis ?' : 'Envoyer ce devis par email ?' }}
                    </AlertDialogTitle>
                    <AlertDialogDescription>
                        {{ confirmAction === 'deleteQuote' ? 'Êtes-vous sûr de vouloir supprimer ce devis ? Cette action est irréversible.' : 'Avez-vous bien relu ce devis ? En confirmant, un email avec le lien de visualisation et de signature sera envoyé directement au prospect.' }}
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="confirmAction = null">Annuler</AlertDialogCancel>
                    <button 
                        @click="executeConfirm" 
                        class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-10 px-4 py-2 text-white"
                        :class="confirmAction === 'sendEmail' ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-red-600 hover:bg-red-700'"
                    >
                        {{ confirmAction === 'sendEmail' ? 'Oui, envoyer au client' : 'Supprimer définitivement' }}
                    </button>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

    </AppLayout>
</template>
