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
    ShieldCheck
} from 'lucide-vue-next';

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

const isPending = computed(() => props.quote.status === 'sent');
const isAccepted = computed(() => props.quote.status === 'accepted');
const isRejected = computed(() => props.quote.status === 'rejected');
const isExpired = computed(() => props.quote.status === 'expired');
const isDraft = computed(() => props.quote.status === 'draft');

const respond = (action: 'accepted' | 'rejected') => {
    if (!confirm(action === 'accepted'
        ? 'Confirmez-vous l\'acceptation de ce devis ?'
        : 'Confirmez-vous le refus de ce devis ?'
    )) return;

    isResponding.value = true;
    router.post(`/devis/${props.quote.public_uid}/respond`, { action }, {
        preserveScroll: true,
        onFinish: () => {
            isResponding.value = false;
            hasResponded.value = true;
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
    <div class="min-h-screen bg-gradient-to-br from-slate-100 via-white to-slate-50">
        <!-- Top ribbon -->
        <div class="h-1.5 w-full" :style="{ background: `linear-gradient(90deg, ${company.primary_color}, ${company.primary_color}88)` }"></div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-12">

            <!-- Company Header -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-6 mb-10">
                <div class="flex items-center gap-4">
                    <div v-if="company.logo_path" class="w-14 h-14 rounded-xl overflow-hidden border border-slate-200 shadow-sm bg-white flex items-center justify-center">
                        <img :src="`/storage/${company.logo_path}`" :alt="company.name" class="w-full h-full object-contain p-1" />
                    </div>
                    <div v-else class="w-14 h-14 rounded-xl flex items-center justify-center border border-slate-200 shadow-sm" :style="{ backgroundColor: company.primary_color + '15' }">
                        <Building2 class="w-7 h-7" :style="{ color: company.primary_color }" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">{{ company.name }}</h2>
                        <div class="flex items-center gap-3 text-xs text-slate-500 mt-0.5">
                            <span v-if="company.email" class="flex items-center gap-1"><Mail class="w-3 h-3" /> {{ company.email }}</span>
                            <span v-if="company.phone" class="flex items-center gap-1"><Phone class="w-3 h-3" /> {{ company.phone }}</span>
                        </div>
                    </div>
                </div>
                <div class="text-center sm:text-right">
                    <p class="text-[11px] uppercase tracking-wider text-slate-400 font-bold">Devis Nº</p>
                    <p class="text-2xl font-black text-slate-900 tracking-tight">{{ quote.quote_number }}</p>
                </div>
            </div>

            <!-- Status Banner -->
            <div :class="[statusConfig.bg, statusConfig.border, statusConfig.text]" class="rounded-2xl border p-5 mb-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span :class="statusConfig.dot" class="w-3 h-3 rounded-full animate-pulse"></span>
                    <component :is="statusConfig.icon" class="w-5 h-5" />
                    <span class="font-bold text-base">{{ statusConfig.label }}</span>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <span class="flex items-center gap-1.5 opacity-75">
                        <Calendar class="w-4 h-4" /> Créé le {{ quote.created_at }}
                    </span>
                    <span v-if="quote.expire_at" class="flex items-center gap-1.5 opacity-75">
                        <Clock class="w-4 h-4" /> Expire le {{ quote.expire_at }}
                    </span>
                </div>
            </div>

            <!-- Client Info -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-6">
                <div class="flex items-center gap-3 mb-1">
                    <p class="text-[11px] uppercase tracking-wider text-slate-400 font-bold">Devis préparé pour</p>
                </div>
                <p class="text-lg font-bold text-slate-900">{{ client.name }}</p>
                <p v-if="client.email" class="text-sm text-slate-500 flex items-center gap-1.5 mt-0.5">
                    <Mail class="w-3.5 h-3.5" /> {{ client.email }}
                </p>
            </div>

            <!-- Items Table -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <FileText class="w-5 h-5" :style="{ color: company.primary_color }" /> Détail des prestations
                    </h3>
                </div>
                <table class="w-full text-left">
                    <thead class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 font-bold border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-3 w-1/2">Description</th>
                            <th class="px-4 py-3 text-center">Quantité</th>
                            <th class="px-4 py-3 text-right">Prix unitaire</th>
                            <th class="px-6 py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="(item, index) in quote.items" :key="index" class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-slate-800 font-medium">{{ item.description }}</td>
                            <td class="px-4 py-4 text-sm text-slate-600 text-center">{{ item.quantity }}</td>
                            <td class="px-4 py-4 text-sm text-slate-600 text-right">{{ item.unit_price.toFixed(2) }}{{ settings.currency }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-slate-900 text-right">{{ item.total.toFixed(2) }}{{ settings.currency }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Summary -->
                <div class="border-t border-slate-200 p-6 bg-slate-50/30">
                    <div class="flex flex-col items-end space-y-2">
                        <div class="w-72 space-y-2">
                            <div class="flex justify-between text-sm text-slate-500">
                                <span>Sous-total</span>
                                <span class="font-medium text-slate-700">{{ Number(quote.subtotal).toFixed(2) }}{{ settings.currency }}</span>
                            </div>
                            <div v-if="settings.tax1_name" class="flex justify-between text-sm text-slate-500">
                                <span>{{ settings.tax1_name }} ({{ settings.tax1_rate }}%)</span>
                                <span class="font-medium text-slate-700">{{ Number(quote.tax1_amount).toFixed(2) }}{{ settings.currency }}</span>
                            </div>
                            <div v-if="settings.tax2_name" class="flex justify-between text-sm text-slate-500">
                                <span>{{ settings.tax2_name }} ({{ settings.tax2_rate }}%)</span>
                                <span class="font-medium text-slate-700">{{ Number(quote.tax2_amount).toFixed(2) }}{{ settings.currency }}</span>
                            </div>
                            <div class="pt-3 mt-1 border-t border-slate-300 flex justify-between items-center">
                                <span class="text-lg font-bold text-slate-900">Total</span>
                                <span class="text-2xl font-black" :style="{ color: company.primary_color }">{{ Number(quote.total).toFixed(2) }}{{ settings.currency }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terms -->
            <div v-if="quote.terms" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-8">
                <h3 class="font-bold text-slate-800 mb-3">Conditions générales</h3>
                <p class="text-sm text-slate-600 whitespace-pre-line leading-relaxed">{{ quote.terms }}</p>
            </div>

            <!-- Response Section -->
            <div v-if="isPending" class="bg-white rounded-2xl border-2 border-dashed border-slate-300 shadow-sm p-8 text-center mb-8">
                <div class="w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-4" :style="{ backgroundColor: company.primary_color + '15' }">
                    <ShieldCheck class="w-8 h-8" :style="{ color: company.primary_color }" />
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">Quelle est votre décision ?</h3>
                <p class="text-slate-500 mb-8 max-w-md mx-auto">En acceptant, vous confirmez votre accord sur les prestations et les conditions décrites ci-dessus.</p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <button
                        @click="respond('accepted')"
                        :disabled="isResponding"
                        class="inline-flex items-center gap-3 px-8 py-3.5 rounded-xl text-white font-bold text-base shadow-lg transition-all hover:shadow-xl hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50"
                        :style="{ backgroundColor: company.primary_color }"
                    >
                        <ThumbsUp class="w-5 h-5" />
                        {{ isResponding ? 'En cours...' : 'Accepter le Devis' }}
                    </button>
                    <button
                        @click="respond('rejected')"
                        :disabled="isResponding"
                        class="inline-flex items-center gap-3 px-8 py-3.5 rounded-xl text-red-600 font-bold text-base border-2 border-red-200 bg-white hover:bg-red-50 transition-all hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50"
                    >
                        <Ban class="w-5 h-5" />
                        {{ isResponding ? 'En cours...' : 'Refuser' }}
                    </button>
                </div>
            </div>

            <!-- Accepted confirmation -->
            <div v-if="isAccepted" class="bg-emerald-50 rounded-2xl border border-emerald-200 p-8 text-center mb-8">
                <div class="w-20 h-20 mx-auto bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mb-4">
                    <CheckCircle2 class="w-10 h-10" />
                </div>
                <h3 class="text-2xl font-bold text-emerald-800 mb-2">Devis Accepté ✓</h3>
                <p class="text-emerald-600 max-w-md mx-auto">Merci pour votre confiance ! L'équipe de <span class="font-bold">{{ company.name }}</span> vous contactera prochainement pour les prochaines étapes.</p>
            </div>

            <!-- Rejected confirmation -->
            <div v-if="isRejected" class="bg-red-50 rounded-2xl border border-red-200 p-8 text-center mb-8">
                <div class="w-20 h-20 mx-auto bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-4">
                    <XCircle class="w-10 h-10" />
                </div>
                <h3 class="text-2xl font-bold text-red-800 mb-2">Devis Refusé</h3>
                <p class="text-red-600 max-w-md mx-auto">Votre décision a été enregistrée. N'hésitez pas à contacter <span class="font-bold">{{ company.name }}</span> pour discuter d'une alternative.</p>
            </div>

            <!-- Expired notice -->
            <div v-if="isExpired" class="bg-amber-50 rounded-2xl border border-amber-200 p-8 text-center mb-8">
                <div class="w-20 h-20 mx-auto bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mb-4">
                    <Clock class="w-10 h-10" />
                </div>
                <h3 class="text-2xl font-bold text-amber-800 mb-2">Devis Expiré</h3>
                <p class="text-amber-600 max-w-md mx-auto">Ce devis a dépassé sa date de validité. Veuillez contacter <span class="font-bold">{{ company.name }}</span> pour obtenir une nouvelle proposition.</p>
            </div>

            <!-- Draft notice -->
            <div v-if="isDraft" class="bg-slate-50 rounded-2xl border border-slate-200 p-8 text-center mb-8">
                <div class="w-20 h-20 mx-auto bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mb-4">
                    <FileText class="w-10 h-10" />
                </div>
                <h3 class="text-2xl font-bold text-slate-700 mb-2">Brouillon</h3>
                <p class="text-slate-500 max-w-md mx-auto">Ce devis est encore en cours de préparation. Vous recevrez une notification lorsqu'il sera finalisé.</p>
            </div>

            <!-- Footer -->
            <div class="text-center text-xs text-slate-400 pt-4 pb-8 border-t border-slate-200">
                <p>Ce devis a été généré par <span class="font-semibold text-slate-500">{{ company.name }}</span> via LeadFlow</p>
                <p class="mt-1">Identifiant sécurisé : <code class="bg-slate-100 px-2 py-0.5 rounded text-[10px]">{{ quote.public_uid }}</code></p>
            </div>
        </div>
    </div>
</template>
