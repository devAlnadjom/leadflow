<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    CheckCircle2,
    XCircle,
    FileText,
    Clock,
    Calendar,
    Mail,
    Phone,
    MapPin,
    Building2,
    Download,
    ThumbsUp,
    Ban,
    ShieldCheck,
    ChevronDown,
    ChevronUp
} from 'lucide-vue-next';

import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';

interface QuoteItem {
    description: string;
    quantity: number;
    unit_price: number;
    total: number;
}

interface Props {
    quote: {
        public_uid: string;
        quote_number: string;
        status: string;
        subtotal: number;
        tax1_amount: number;
        tax2_amount: number;
        total: number;
        expire_at: string | null;
        terms: string | null;
        created_at: string;
        items: QuoteItem[];
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
    };
}

const props = defineProps<Props>();

const isResponding = ref(false);
const hasResponded = ref(false);
const detailsOpen = ref(true);

const isPending = computed(() => props.quote.status === 'sent');
const isAccepted = computed(() => props.quote.status === 'accepted');
const isRejected = computed(() => props.quote.status === 'rejected');
const isExpired = computed(() => props.quote.status === 'expired');
const isDraft = computed(() => props.quote.status === 'draft');

const confirmAction = ref<'accepted' | 'rejected' | null>(null);

const requestRespond = (action: 'accepted' | 'rejected') => {
    confirmAction.value = action;
};

const confirmRespond = () => {
    if (!confirmAction.value) return;

    const action = confirmAction.value;

    isResponding.value = true;
    router.post(`/devis/${props.quote.public_uid}/respond`, { action }, {
        preserveScroll: true,
        onFinish: () => {
            isResponding.value = false;
            hasResponded.value = true;
            confirmAction.value = null;
        }
    });
};

const statusConfig = computed(() => {
    switch (props.quote.status) {
        case 'accepted':
            return { label: 'Accepté', icon: CheckCircle2, bg: 'bg-emerald-50', text: 'text-emerald-700', border: 'border-emerald-200', dot: 'bg-emerald-500' };
        case 'rejected':
            return { label: 'Refusé', icon: XCircle, bg: 'bg-red-50', text: 'text-red-700', border: 'border-red-200', dot: 'bg-red-500' };
        case 'expired':
            return { label: 'Expiré', icon: Clock, bg: 'bg-amber-50', text: 'text-amber-700', border: 'border-amber-200', dot: 'bg-amber-500' };
        case 'draft':
            return { label: 'Brouillon', icon: FileText, bg: 'bg-slate-50', text: 'text-slate-500', border: 'border-slate-200', dot: 'bg-slate-400' };
        default:
            return { label: 'En attente de réponse', icon: Clock, bg: 'bg-blue-50', text: 'text-blue-700', border: 'border-blue-200', dot: 'bg-blue-500' };
    }
});
</script>

<template>
    <Head :title="`Devis ${quote.quote_number} — ${company.name}`" />

    <!-- Minimal standalone layout (no sidebar) -->
    <div class="min-h-screen bg-gradient-to-br from-slate-100 via-slate-50 to-white pb-20 sm:pb-12">
        <!-- Top ribbon -->
        <div class="h-2 w-full" :style="{ background: `linear-gradient(90deg, ${company.primary_color}, ${company.primary_color}dd)` }"></div>

        <div class="max-w-3xl mx-auto px-4 sm:px-6 py-6 sm:py-10">

            <!-- Company Header -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-6 mb-8 bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-100">
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                    <div v-if="company.logo_path" class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden border border-slate-100 shadow-sm bg-white flex items-center justify-center shrink-0">
                        <img :src="`/storage/${company.logo_path}`" :alt="company.name" class="w-full h-full object-contain p-2" />
                    </div>
                    <div v-else class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl flex items-center justify-center border border-slate-100 shadow-sm shrink-0" :style="{ backgroundColor: company.primary_color + '15' }">
                        <Building2 class="w-8 h-8 sm:w-10 sm:h-10" :style="{ color: company.primary_color }" />
                    </div>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ company.name }}</h1>
                        <div class="flex flex-col sm:flex-row gap-1 sm:gap-4 text-sm text-slate-500 mt-2">
                            <span v-if="company.email" class="flex items-center gap-1.5"><Mail class="w-4 h-4 text-slate-400" /> {{ company.email }}</span>
                            <span v-if="company.phone" class="flex items-center gap-1.5"><Phone class="w-4 h-4 text-slate-400" /> {{ company.phone }}</span>
                        </div>
                    </div>
                </div>
                <div class="text-left sm:text-right bg-slate-50 sm:bg-transparent p-4 sm:p-0 rounded-xl sm:rounded-none border border-slate-100 sm:border-transparent mt-2 sm:mt-0">
                    <p class="text-xs uppercase tracking-widest text-slate-400 font-bold mb-1">Devis Nº</p>
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ quote.quote_number }}</p>
                </div>
            </div>

            <!-- Status Banner -->
            <div :class="[statusConfig.bg, statusConfig.border, statusConfig.text]" class="rounded-2xl border p-4 sm:p-5 mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="w-2.5 h-2.5 rounded-full" :class="[statusConfig.dot, {'animate-pulse': isPending}]"></span>
                    <component :is="statusConfig.icon" class="w-5 h-5 shrink-0" />
                    <span class="font-bold text-base sm:text-lg">{{ statusConfig.label }}</span>
                </div>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
                    <span class="flex items-center gap-1.5 opacity-80 font-medium">
                        <Calendar class="w-4 h-4" /> Le {{ quote.created_at }}
                    </span>
                    <span v-if="quote.expire_at && isPending" class="flex items-center gap-1.5 opacity-80 font-medium">
                        <Clock class="w-4 h-4" /> Expire le {{ quote.expire_at }}
                    </span>
                </div>
            </div>

            <!-- Two Column Layout for Desktop: Client Info & Summary -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Client Info -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-center">
                    <p class="text-xs uppercase tracking-widest text-slate-400 font-bold mb-3 flex items-center gap-2">
                        Préparé pour
                    </p>
                    <p class="text-xl font-bold text-slate-900 mb-1">{{ client.name }}</p>
                    <p v-if="client.email" class="text-sm text-slate-500 flex items-center gap-2">
                        <Mail class="w-4 h-4 shrink-0 text-slate-400" /> <span class="truncate">{{ client.email }}</span>
                    </p>
                </div>

                <!-- Grand Total Card (moves to top right on desktop) -->
                <div class="rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-center relative overflow-hidden text-white" :style="{ backgroundColor: company.primary_color }">
                    <!-- Decorative background element -->
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>
                    <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-black opacity-10 rounded-full blur-2xl"></div>
                    
                    <div class="relative z-10">
                        <p class="text-sm font-semibold opacity-90 mb-1">Montant Total</p>
                        <p class="text-4xl sm:text-5xl font-black tracking-tight">{{ Number(quote.total).toFixed(2) }}<span class="text-2xl sm:text-3xl ml-1 opacity-80">{{ settings.currency }}</span></p>
                        <p class="text-xs opacity-75 mt-2 flex items-center gap-1">
                            <ShieldCheck class="w-3.5 h-3.5" /> Sécurisé et détaillé ci-dessous
                        </p>
                    </div>
                </div>
            </div>

            <!-- Items Details -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-8">
                <!-- Accordion Header for Mobile -->
                <button @click="detailsOpen = !detailsOpen" class="w-full p-5 sm:p-6 flex items-center justify-between bg-slate-50/50 hover:bg-slate-50 transition-colors">
                    <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2.5">
                        <FileText class="w-5 h-5" :style="{ color: company.primary_color }" /> Détail des prestations
                    </h3>
                    <ChevronDown v-if="!detailsOpen" class="w-5 h-5 text-slate-400" />
                    <ChevronUp v-else class="w-5 h-5 text-slate-400" />
                </button>

                <div v-show="detailsOpen">
                    <!-- Desktop Table -->
                    <div class="hidden sm:block">
                        <table class="w-full text-left border-t border-slate-100">
                            <thead class="bg-slate-50 border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-4 text-xs uppercase tracking-wider text-slate-500 font-bold w-1/2">Description</th>
                                    <th class="px-4 py-4 text-xs uppercase tracking-wider text-slate-500 font-bold text-center">Qté</th>
                                    <th class="px-4 py-4 text-xs uppercase tracking-wider text-slate-500 font-bold text-right">Prix Unitaire</th>
                                    <th class="px-6 py-4 text-xs uppercase tracking-wider text-slate-900 font-black text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="(item, index) in quote.items" :key="index" class="hover:bg-slate-50/30 transition-colors">
                                    <td class="px-6 py-4 text-sm text-slate-800 font-medium whitespace-pre-wrap">{{ item.description }}</td>
                                    <td class="px-4 py-4 text-sm text-slate-600 text-center font-semibold">{{ item.quantity }}</td>
                                    <td class="px-4 py-4 text-sm text-slate-600 text-right">{{ item.unit_price.toFixed(2) }}{{ settings.currency }}</td>
                                    <td class="px-6 py-4 text-sm font-bold text-slate-900 text-right">{{ item.total.toFixed(2) }}{{ settings.currency }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards List -->
                    <div class="sm:hidden border-t border-slate-100 divide-y divide-slate-100">
                        <div v-for="(item, index) in quote.items" :key="index" class="p-5 hover:bg-slate-50/50">
                            <p class="text-sm font-bold text-slate-900 mb-2 whitespace-pre-wrap">{{ item.description }}</p>
                            <div class="flex items-center justify-between text-sm text-slate-600">
                                <div>{{ item.quantity }} x {{ item.unit_price.toFixed(2) }}{{ settings.currency }}</div>
                                <div class="font-bold text-slate-900 text-base">{{ item.total.toFixed(2) }}{{ settings.currency }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Breakdown & Summary -->
                    <div class="border-t border-slate-100 p-5 sm:p-6 bg-slate-50/80">
                        <div class="flex flex-col sm:items-end space-y-2">
                            <div class="w-full sm:w-72 space-y-2.5">
                                <div class="flex justify-between items-center text-sm text-slate-500">
                                    <span>Sous-total HT</span>
                                    <span class="font-medium text-slate-700">{{ Number(quote.subtotal).toFixed(2) }}{{ settings.currency }}</span>
                                </div>
                                <div v-if="settings.tax1_name" class="flex justify-between items-center text-sm text-slate-500">
                                    <span>{{ settings.tax1_name }} ({{ settings.tax1_rate }}%)</span>
                                    <span class="font-medium text-slate-700">{{ Number(quote.tax1_amount).toFixed(2) }}{{ settings.currency }}</span>
                                </div>
                                <div v-if="settings.tax2_name" class="flex justify-between items-center text-sm text-slate-500">
                                    <span>{{ settings.tax2_name }} ({{ settings.tax2_rate }}%)</span>
                                    <span class="font-medium text-slate-700">{{ Number(quote.tax2_amount).toFixed(2) }}{{ settings.currency }}</span>
                                </div>
                                <div class="pt-4 border-t border-slate-200 mt-2 flex justify-between items-end">
                                    <div>
                                        <span class="block text-sm font-bold text-slate-900 uppercase tracking-widest">Total TTC</span>
                                        <span class="block text-[10px] text-slate-500 uppercase mt-0.5">En devise locale ({{ settings.currency }})</span>
                                    </div>
                                    <span class="text-2xl font-black" :style="{ color: company.primary_color }">{{ Number(quote.total).toFixed(2) }}{{ settings.currency }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terms -->
            <div v-if="quote.terms" class="bg-slate-50 rounded-2xl border border-slate-200 p-5 sm:p-6 mb-8 text-sm">
                <h3 class="font-bold text-slate-800 mb-3 flex items-center gap-2 uppercase tracking-wide text-xs">
                    Conditions Générales
                </h3>
                <p class="text-slate-600 whitespace-pre-line leading-relaxed">{{ quote.terms }}</p>
            </div>

            <!-- Interactivity Area: Sticky on mobile, normal on desktop -->
            <div 
                v-if="!isDraft" 
                class="fixed bottom-0 left-0 right-0 sm:static bg-white sm:bg-transparent border-t border-slate-200 sm:border-none p-4 sm:p-0 z-50 shadow-2xl sm:shadow-none"
            >
                <div class="max-w-3xl mx-auto">
                    <!-- Response Section (Only if pending) -->
                    <div v-if="isPending" class="sm:bg-white sm:rounded-2xl sm:border-2 sm:border-slate-800 sm:shadow-xl sm:p-8 text-center">
                        <h3 class="hidden sm:block text-2xl font-black text-slate-900 mb-2">Prenez votre décision</h3>
                        <p class="hidden sm:block text-slate-500 mb-8 max-w-md mx-auto text-sm">En acceptant, vous confirmez votre accord sur les prestations et les conditions de l'entreprise {{ company.name }}.</p>
                        
                        <div class="flex flex-row items-center justify-center gap-2 sm:gap-4">
                            <button
                                @click="requestRespond('rejected')"
                                :disabled="isResponding"
                                class="flex-1 sm:flex-none inline-flex justify-center items-center gap-2 px-4 sm:px-8 py-3.5 sm:py-4 rounded-xl text-slate-700 font-bold text-sm sm:text-base border-2 border-slate-200 bg-white hover:bg-slate-50 transition-all hover:border-slate-300 disabled:opacity-50"
                            >
                                <XCircle class="w-4 h-4 sm:w-5 sm:h-5" />
                                <span class="hidden sm:inline">Refuser le devis</span>
                                <span class="sm:hidden">Refuser</span>
                            </button>
                            <button
                                @click="requestRespond('accepted')"
                                :disabled="isResponding"
                                class="flex-[2] sm:flex-none inline-flex justify-center items-center gap-2 px-6 sm:px-10 py-3.5 sm:py-4 rounded-xl text-white font-black text-sm sm:text-lg shadow-lg transition-all hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50"
                                :style="{ backgroundColor: company.primary_color, boxShadow: `0 10px 15px -3px ${company.primary_color}40` }"
                            >
                                <CheckCircle2 class="w-5 h-5 sm:w-6 sm:h-6" />
                                <span class="hidden sm:inline">Accepter et Signer</span>
                                <span class="sm:hidden">Accepter le devis</span>
                            </button>
                        </div>
                    </div>

                    <!-- Already Responded States (Desktop Only to prevent duplication, Mobile handled by top banner) -->
                    <div v-else-if="!isPending && !isDraft" class="hidden sm:flex flex-col items-center justify-center p-8 bg-white rounded-2xl border border-slate-200 shadow-sm text-center">
                        <div v-if="isAccepted" class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mb-4">
                            <CheckCircle2 class="w-8 h-8" />
                        </div>
                        <div v-else-if="isRejected" class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-4">
                            <XCircle class="w-8 h-8" />
                        </div>
                        <div v-else-if="isExpired" class="w-16 h-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mb-4">
                            <Clock class="w-8 h-8" />
                        </div>

                        <h3 class="text-2xl font-black text-slate-900 mb-2">
                            {{ isAccepted ? 'Devis Accepté !' : isRejected ? 'Devis Refusé' : 'Devis Expiré' }}
                        </h3>
                        <p class="text-slate-500 max-w-sm">
                            {{ isAccepted ? 'Merci pour votre confiance. L\'équipe vous contactera très vite.' : isRejected ? 'Votre décision a bien été prise en compte.' : 'Ce document n\'est plus valide.' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center text-[11px] text-slate-400 font-medium py-8" :class="{'mb-20 sm:mb-0': !isDraft && isPending}">
                <p>Créé par {{ company.name }} grâce à <span class="font-bold text-slate-500">clientux</span></p>
                <p class="mt-1 opacity-50">Ref: {{ quote.public_uid }}</p>
            </div>
            
            <!-- AlertDialog for Confirmation -->
            <AlertDialog :open="confirmAction !== null" @update:open="(val) => { if (!val) confirmAction = null }">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            {{ confirmAction === 'accepted' ? 'Confirmer l\'acceptation' : 'Refuser ce devis' }}
                        </AlertDialogTitle>
                        <AlertDialogDescription>
                            {{ confirmAction === 'accepted' ? 'En confirmant, vous acceptez officiellement ce devis et validez les prestations décrites.' : 'Êtes-vous sûr de vouloir refuser ce devis ?' }}
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel :disabled="isResponding" @click="confirmAction = null">Annuler</AlertDialogCancel>
                        <button 
                            @click="confirmRespond" 
                            :disabled="isResponding"
                            class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-10 px-4 py-2 text-primary-foreground"
                            :class="confirmAction === 'accepted' ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-red-600 hover:bg-red-700'"
                        >
                            {{ isResponding ? 'En cours...' : 'Confirmer' }}
                        </button>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    </div>
</template>
