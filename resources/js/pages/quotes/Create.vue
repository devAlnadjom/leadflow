<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';
import { 
    ChevronLeft, 
    Plus, 
    Trash2, 
    Calculator,
    Calendar,
    FileText,
    Settings2,
    Save
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

interface Item {
    description: string;
    quantity: number;
    unit_price: number;
}

interface Props {
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
    suggestedNumber: string;
}

const props = defineProps<Props>();

const form = useForm({
    quote_number: props.suggestedNumber,
    status: 'draft',
    expire_at: '',
    notes: '',
    terms: props.settings.terms_and_conditions || '',
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
    return subtotal.value * (form.tax1_rate / 100);
});

const tax2Amount = computed(() => {
    return subtotal.value * (form.tax2_rate / 100);
});

const total = computed(() => {
    return subtotal.value + tax1Amount.value + tax2Amount.value;
});

const submit = () => {
    form.post(`/leads/${props.lead.id}/quotes`);
};
</script>

<template>
    <Head title="Créer un Devis" />

    <AppLayout>
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header Nav -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <Link :href="`/leads/${lead.id}`" class="inline-flex items-center text-sm text-slate-500 hover:text-indigo-600 transition-colors mb-2">
                        <ChevronLeft class="w-4 h-4 mr-1" /> Retour au lead
                    </Link>
                    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Nouveau Devis</h1>
                    <p class="text-slate-500 mt-1">Générez une proposition commerciale pour <span class="font-semibold text-slate-700">{{ lead.name }}</span></p>
                </div>
                <div class="flex items-center gap-3">
                    <Button variant="outline" as-child>
                        <Link :href="`/leads/${lead.id}`">Annuler</Link>
                    </Button>
                    <Button @click="submit" class="bg-indigo-600 hover:bg-indigo-700 gap-2 shadow-lg shadow-indigo-100" :disabled="form.processing">
                        <Save class="w-4 h-4" />
                        {{ form.processing ? 'Enregistrement...' : 'Enregistrer le Devis' }}
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Form Area -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Items Section -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                            <h2 class="font-bold text-slate-800 flex items-center gap-2">
                                <FileText class="w-5 h-5 text-indigo-500" /> Articles & Services
                            </h2>
                            <Button @click="addItem" variant="outline" size="sm" class="gap-1.5 text-indigo-600 border-indigo-100 bg-white">
                                <Plus class="w-4 h-4" /> Ajouter une ligne
                            </Button>
                        </div>
                        
                        <div class="p-0">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 font-bold border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-3 w-1/2">Description</th>
                                        <th class="px-4 py-3 text-center">Qté</th>
                                        <th class="px-4 py-3 text-right">Prix Unitaire</th>
                                        <th class="px-4 py-3 text-right">Total</th>
                                        <th class="px-6 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="(item, index) in form.items" :key="index" class="group hover:bg-slate-50/30 transition-colors">
                                        <td class="px-6 py-4">
                                            <Input v-model="item.description" placeholder="Ex: Rénovation salle de bain" class="border-transparent bg-transparent group-hover:border-slate-200 focus:bg-white transition-all h-9" />
                                        </td>
                                        <td class="px-4 py-4 w-24">
                                            <Input type="number" v-model.number="item.quantity" class="text-center border-transparent bg-transparent group-hover:border-slate-200 focus:bg-white h-9" />
                                        </td>
                                        <td class="px-4 py-4 w-32">
                                            <div class="relative">
                                                <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm">$</span>
                                                <Input type="number" step="0.01" v-model.number="item.unit_price" class="pl-6 text-right border-transparent bg-transparent group-hover:border-slate-200 focus:bg-white h-9" />
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-right font-semibold text-slate-700 w-32">
                                            {{ (item.quantity * item.unit_price).toFixed(2) }}{{ settings.currency }}
                                        </td>
                                        <td class="px-6 py-4 text-right w-16">
                                            <button @click="removeItem(index)" class="p-1.5 text-slate-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all rounded hover:bg-red-50">
                                                <Trash2 class="w-4 h-4" />
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary Footer -->
                        <div class="p-6 bg-slate-50/30 border-t border-slate-100">
                            <div class="flex flex-col items-end space-y-3">
                                <div class="w-64 space-y-2">
                                    <div class="flex justify-between text-sm text-slate-500">
                                        <span>Sous-total</span>
                                        <span class="font-medium text-slate-800">{{ subtotal.toFixed(2) }}{{ settings.currency }}</span>
                                    </div>
                                    <div v-if="settings.tax1_name" class="flex justify-between text-sm text-slate-500">
                                        <span>{{ settings.tax1_name }} ({{ form.tax1_rate }}%)</span>
                                        <span class="font-medium text-slate-800">{{ tax1Amount.toFixed(2) }}{{ settings.currency }}</span>
                                    </div>
                                    <div v-if="settings.tax2_name" class="flex justify-between text-sm text-slate-500">
                                        <span>{{ settings.tax2_name }} ({{ form.tax2_rate }}%)</span>
                                        <span class="font-medium text-slate-800">{{ tax2Amount.toFixed(2) }}{{ settings.currency }}</span>
                                    </div>
                                    <div class="pt-2 border-t border-slate-200 flex justify-between items-center">
                                        <span class="text-base font-bold text-slate-900">Total</span>
                                        <span class="text-xl font-black text-indigo-600">{{ total.toFixed(2) }}{{ settings.currency }}</span>
                                    </div>
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
                                <label class="text-[11px] font-bold uppercase text-slate-400 block mb-1.5 ml-1">Date d'expiration (optionnel)</label>
                                <div class="relative">
                                    <Calendar class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                    <Input type="date" v-model="form.expire_at" class="pl-10 bg-slate-50 border-slate-100 h-10" />
                                </div>
                            </div>

                            <div>
                                <label class="text-[11px] font-bold uppercase text-slate-400 block mb-1.5 ml-1">Statut Initial</label>
                                <select v-model="form.status" class="w-full rounded-md border-slate-100 bg-slate-50 text-sm h-10 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="draft">Brouillon</option>
                                    <option value="sent">Envoyé</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="bg-indigo-600 rounded-2xl p-6 text-white shadow-lg shadow-indigo-200 group relative overflow-hidden">
                        <div class="relative z-10">
                            <div class="bg-white/20 w-10 h-10 rounded-lg flex items-center justify-center mb-4">
                                <Calculator class="w-6 h-6" />
                            </div>
                            <h3 class="font-bold text-lg mb-1 italic">Total estimé</h3>
                            <p class="text-3xl font-black">{{ total.toFixed(2) }}{{ settings.currency }}</p>
                            <p class="text-indigo-100 text-xs mt-2 opacity-80">Inclut {{ settings.tax1_name }} et {{ settings.tax2_name }}</p>
                        </div>
                        <!-- Micro-animation elements -->
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                        <h2 class="font-bold text-slate-800 flex items-center gap-2 mb-4">
                            <FileText class="w-5 h-5 text-indigo-500" /> Notes Internes
                        </h2>
                        <textarea v-model="form.notes" rows="3" class="w-full rounded-xl border-slate-200 bg-slate-200/30 text-sm p-4 italic" placeholder="Ces notes ne seront pas visibles par le client..."></textarea>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
