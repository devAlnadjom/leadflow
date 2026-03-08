<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { 
    ChevronLeft, Edit, Trash2, Mail, Phone, Building, MapPin, 
    FileText, Users, Calendar, ArrowRight, ExternalLink, Plus, MessageSquare, Briefcase
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import {
    AlertDialog,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import type { BreadcrumbItem } from '@/types';

interface Note {
    id: number;
    content: string;
    type: string;
    author: string;
    created_at: string;
}

interface Lead {
    id: number;
    name: string | null;
    status: string;
    created_at: string;
}

interface Quote {
    id: number;
    quote_number: string;
    public_uid: string;
    status: string;
    total: number;
    expire_at: string | null;
    created_at: string;
}

interface ClientPayload {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    company_name: string | null;
    address: string | null;
    city: string | null;
    postal_code: string | null;
    tax_number: string | null;
    created_at: string;
    leads: Lead[];
    quotes: Quote[];
    notes: Note[];
}

const props = defineProps<{
    client: ClientPayload;
}>();

const noteForm = useForm({
    content: '',
    type: 'note',
});

const submitNote = () => {
    noteForm.post(`/clients/${props.client.id}/notes`, {
        preserveScroll: true,
        onSuccess: () => {
            noteForm.reset('content');
            noteForm.clearErrors();
        },
    });
};

const deleteNote = (noteId: number) => {
    if (confirm('Voulez-vous vraiment supprimer cette note ?')) {
        router.delete(`/clients/${props.client.id}/notes/${noteId}`, {
            preserveScroll: true,
        });
    }
};

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Clients', href: '/clients' },
    { title: props.client.name, href: '#' },
]);

const confirmAction = ref<'deleteClient' | null>(null);

const confirmDeleteClient = () => confirmAction.value = 'deleteClient';

const executeConfirm = () => {
    if (confirmAction.value === 'deleteClient') {
        router.delete(`/clients/${props.client.id}`, {
            onFinish: () => confirmAction.value = null
        });
    }
};

const isEditDialogOpen = ref(false);

const editForm = useForm({
    name: props.client.name,
    email: props.client.email || '',
    phone: props.client.phone || '',
    company_name: props.client.company_name || '',
    address: props.client.address || '',
    city: props.client.city || '',
    postal_code: props.client.postal_code || '',
    tax_number: props.client.tax_number || '',
});

const openEditDialog = () => {
    editForm.reset();
    editForm.name = props.client.name;
    editForm.email = props.client.email || '';
    editForm.phone = props.client.phone || '';
    editForm.company_name = props.client.company_name || '';
    editForm.address = props.client.address || '';
    editForm.city = props.client.city || '';
    editForm.postal_code = props.client.postal_code || '';
    editForm.tax_number = props.client.tax_number || '';
    isEditDialogOpen.value = true;
};

const submitEdit = () => {
    editForm.put(`/clients/${props.client.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditDialogOpen.value = false;
        },
    });
};

const leadStatuses: Record<string, { label: string; class: string }> = {
    new: { label: 'Nouveau', class: 'bg-blue-100 text-blue-700' },
    contacted: { label: 'Contacté', class: 'bg-yellow-100 text-yellow-700' },
    qualified: { label: 'Qualifié', class: 'bg-indigo-100 text-indigo-700' },
    won: { label: 'Gagné', class: 'bg-green-100 text-green-700' },
    lost: { label: 'Perdu', class: 'bg-red-100 text-red-700' },
};

const quoteStatuses: Record<string, { label: string; class: string }> = {
    draft: { label: 'Brouillon', class: 'bg-slate-100 text-slate-700' },
    sent: { label: 'Envoyé', class: 'bg-blue-100 text-blue-700' },
    accepted: { label: 'Accepté', class: 'bg-emerald-100 text-emerald-700' },
    rejected: { label: 'Refusé', class: 'bg-red-100 text-red-700' },
    expired: { label: 'Expiré', class: 'bg-amber-100 text-amber-700' },
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(value);
};
</script>

<template>
    <Head :title="client.name + ' | Client'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="h-full flex flex-col bg-slate-50 min-h-screen">
            <!-- Contextual Header -->
            <div class="bg-white border-b border-slate-200 px-4 md:px-8 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 sticky top-0 z-10 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center border border-slate-200 shrink-0">
                        <Briefcase class="w-6 h-6 text-slate-400" />
                    </div>
                    <div>
                        <h1 class="text-xl md:text-2xl font-bold text-slate-900 leading-tight">
                            {{ client.name }}
                        </h1>
                        <p class="text-slate-500 mt-1 flex items-center gap-2 text-sm">
                            <Badge variant="secondary" class="bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border-indigo-200">
                                Client
                            </Badge>
                            &bull; Depuis le {{ new Date(client.created_at).toLocaleDateString() }}
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                    <Button variant="outline" class="gap-2 text-slate-600" @click="openEditDialog">
                        <Edit class="w-4 h-4" /> Modifier
                    </Button>
                    <Button variant="outline" class="gap-2 text-red-500 hover:bg-red-50 hover:text-red-600 border-red-200" @click="confirmDeleteClient">
                        <Trash2 class="w-4 h-4" /> Supprimer
                    </Button>
                </div>
            </div>

            <div class="p-4 md:p-8 flex-1 flex flex-col w-full mx-auto max-w-7xl">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8 flex-1">
                    <!-- Left Column: Details -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Contact Card -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 relative overflow-hidden group">
                            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-indigo-50 to-indigo-100/50 rounded-bl-full -z-10 transition-transform group-hover:scale-110"></div>
                            
                            <h2 class="font-bold text-slate-800 flex items-center gap-2 mb-6">
                                <Users class="w-5 h-5 text-indigo-500" /> Profil Client
                            </h2>
                            
                            <div class="space-y-4">
                                <div>
                                    <span class="text-[11px] font-bold uppercase text-slate-400 block mb-1">Email</span>
                                    <div class="flex items-center gap-2" v-if="client.email">
                                        <Mail class="w-4 h-4 text-slate-400" />
                                        <a :href="'mailto:' + client.email" class="text-slate-700 hover:text-indigo-600 font-medium">{{ client.email }}</a>
                                    </div>
                                    <span v-else class="text-slate-500 italic text-sm">Non renseigné</span>
                                </div>
                                
                                <div class="w-full h-px bg-slate-100"></div>
                                
                                <div>
                                    <span class="text-[11px] font-bold uppercase text-slate-400 block mb-1">Téléphone</span>
                                    <div class="flex items-center gap-2" v-if="client.phone">
                                        <Phone class="w-4 h-4 text-slate-400" />
                                        <a :href="'tel:' + client.phone" class="text-slate-700 hover:text-indigo-600 font-medium">{{ client.phone }}</a>
                                    </div>
                                    <span v-else class="text-slate-500 italic text-sm">Non renseigné</span>
                                </div>

                                <div class="w-full h-px bg-slate-100"></div>
                                
                                <div>
                                    <span class="text-[11px] font-bold uppercase text-slate-400 block mb-1">Entreprise</span>
                                    <div class="flex items-center gap-2" v-if="client.company_name">
                                        <Building class="w-4 h-4 text-slate-400" />
                                        <span class="text-slate-700 font-medium">{{ client.company_name }}</span>
                                    </div>
                                    <span v-else class="text-slate-500 italic text-sm">Non renseigné</span>
                                    
                                    <div class="mt-2 pl-6" v-if="client.tax_number">
                                        <span class="text-xs text-slate-500 block">TVA / SIRET : {{ client.tax_number }}</span>
                                    </div>
                                </div>

                                <div class="w-full h-px bg-slate-100"></div>

                                <div>
                                    <span class="text-[11px] font-bold uppercase text-slate-400 block mb-1">Adresse</span>
                                    <div class="flex items-start gap-2" v-if="client.address || client.city">
                                        <MapPin class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" />
                                        <div class="text-slate-700 font-medium text-sm leading-relaxed">
                                            <span v-if="client.address" class="block">{{ client.address }}</span>
                                            <span v-if="client.city || client.postal_code" class="block text-slate-500">
                                                {{ client.postal_code }} {{ client.city }}
                                            </span>
                                        </div>
                                    </div>
                                    <span v-else class="text-slate-500 italic text-sm">Non renseigné</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Activity Tabs -->
                    <div class="lg:col-span-2 space-y-6">
                        <Tabs defaultValue="overview" class="w-full">
                            <!-- Scrollable tabs list for smaller screens -->
                            <div class="overflow-x-auto pb-2 -mb-2">
                                <TabsList class="mb-4 inline-flex w-max min-w-full md:w-auto h-11">
                                    <TabsTrigger value="overview" class="flex items-center gap-2 px-4">
                                        <FileText class="w-4 h-4" /> Aperçu
                                    </TabsTrigger>
                                    <TabsTrigger value="notes" class="flex items-center gap-2 px-4">
                                        <MessageSquare class="w-4 h-4" /> 
                                        Notes internes
                                        <Badge variant="secondary" class="ml-1 px-1.5 py-0 min-w-5 rounded-full text-xs font-normal" v-if="client.notes.length > 0">{{ client.notes.length }}</Badge>
                                    </TabsTrigger>
                                    <TabsTrigger value="invoices" class="flex items-center gap-2 px-4">
                                        Factures
                                    </TabsTrigger>
                                    <TabsTrigger value="activities" class="flex items-center gap-2 px-4">
                                        Détail activités
                                    </TabsTrigger>
                                    <TabsTrigger value="payments" class="flex items-center gap-2 px-4">
                                        Paiements
                                    </TabsTrigger>
                                    <TabsTrigger value="projects" class="flex items-center gap-2 px-4">
                                        Projets
                                    </TabsTrigger>
                                </TabsList>
                            </div>

                            <!-- Overview Tab -->
                            <TabsContent value="overview" class="space-y-6 mt-0">
                                <!-- Quotes Section -->
                                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 overflow-hidden">
                                    <div class="flex items-center justify-between mb-6">
                                        <h2 class="font-bold text-slate-800 flex items-center gap-2 leading-none">
                                            <FileText class="w-5 h-5 text-indigo-500" /> 
                                            Devis
                                            <Badge variant="secondary" class="ml-2 font-normal">{{ client.quotes.length }}</Badge>
                                        </h2>
                                        <!-- TODO: Lead conversion context to pass client later -->
                                        <Button size="sm" variant="outline" class="h-8 gap-1.5 hidden" title="Bientôt">
                                            <Plus class="w-3.5 h-3.5" /> Nouveau
                                        </Button>
                                    </div>

                                    <div v-if="client.quotes.length === 0" class="text-center py-8 px-4 bg-slate-50 rounded-xl border border-slate-100 border-dashed">
                                        <FileText class="w-8 h-8 text-slate-300 mx-auto mb-3" />
                                        <p class="text-slate-500 text-sm">Aucun devis lié à ce client.</p>
                                    </div>

                                    <div v-else class="grid gap-3">
                                        <div v-for="quote in client.quotes" :key="quote.id" class="flex items-center justify-between p-4 rounded-xl border border-slate-100 hover:border-slate-300 hover:shadow-sm transition-all group bg-white">
                                            <div>
                                                <div class="flex items-center gap-2 mb-1">
                                                    <Link :href="`/quotes/${quote.id}/edit`" class="font-bold text-slate-800 hover:text-indigo-600 flex items-center gap-1.5 transition-colors">
                                                        {{ quote.quote_number }}
                                                        <ExternalLink class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" />
                                                    </Link>
                                                    <span :class="['text-[10px] uppercase tracking-wider font-bold px-2 py-0.5 rounded-full', quoteStatuses[quote.status]?.class || 'bg-slate-100 text-slate-500']">
                                                        {{ quoteStatuses[quote.status]?.label || quote.status }}
                                                    </span>
                                                </div>
                                                <div class="text-xs text-slate-500 flex items-center gap-2">
                                                    <span>Le {{ new Date(quote.created_at).toLocaleDateString() }}</span>
                                                    <span v-if="quote.expire_at" class="text-slate-400">&bull; Expire le {{ new Date(quote.expire_at).toLocaleDateString() }}</span>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="font-black text-slate-900 text-lg">{{ formatCurrency(quote.total) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Leads Section -->
                                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 overflow-hidden">
                                    <div class="flex items-center justify-between mb-6">
                                        <h2 class="font-bold text-slate-800 flex items-center gap-2 leading-none">
                                            <Users class="w-5 h-5 text-indigo-500" /> 
                                            Prospects liés (Historique)
                                            <Badge variant="secondary" class="ml-2 font-normal">{{ client.leads.length }}</Badge>
                                        </h2>
                                    </div>

                                    <div v-if="client.leads.length === 0" class="text-center py-8 px-4 bg-slate-50 rounded-xl border border-slate-100 border-dashed">
                                        <Users class="w-8 h-8 text-slate-300 mx-auto mb-3" />
                                        <p class="text-slate-500 text-sm">Aucun historique de lead pour ce client.</p>
                                    </div>

                                    <div v-else class="grid gap-3">
                                        <div v-for="lead in client.leads" :key="lead.id" class="flex items-center justify-between p-4 rounded-xl border border-slate-100 hover:border-slate-300 hover:shadow-sm transition-all group bg-white">
                                            <div>
                                                <div class="flex items-center gap-2 mb-1">
                                                    <Link :href="`/leads/${lead.id}`" class="font-bold text-slate-800 hover:text-indigo-600 flex items-center gap-1.5 transition-colors">
                                                        {{ lead.name || 'Lead Anonyme' }} 
                                                        <ExternalLink class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" />
                                                    </Link>
                                                    <span :class="['text-[10px] uppercase tracking-wider font-bold px-2 py-0.5 rounded-full', leadStatuses[lead.status]?.class || 'bg-slate-100 text-slate-500']">
                                                        {{ leadStatuses[lead.status]?.label || lead.status }}
                                                    </span>
                                                </div>
                                                <div class="text-xs text-slate-500 flex items-center gap-2">
                                                    <span>Créé le {{ new Date(lead.created_at).toLocaleDateString() }}</span>
                                                </div>
                                            </div>
                                            <div>
                                                <Link :href="`/leads/${lead.id}`" class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-colors">
                                                    <ArrowRight class="w-4 h-4" />
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </TabsContent>

                            <!-- Notes internes Tab -->
                            <TabsContent value="notes" class="mt-0">
                                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                                    <!-- Add Note Form -->
                                    <div class="p-4 border-b border-slate-100 bg-slate-50/50">
                                        <form @submit.prevent="submitNote" class="relative">
                                            <div class="overflow-hidden rounded-xl border bg-white focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500 transition-shadow">
                                                <Textarea 
                                                    v-model="noteForm.content"
                                                    class="min-h-[100px] border-0 focus-visible:ring-0 resize-none p-4 w-full shadow-none" 
                                                    placeholder="Écrivez une note interne concernant ce client..." 
                                                    required
                                                />
                                                <div class="flex items-center justify-between p-3 bg-slate-50 border-t">
                                                    <div class="text-xs text-slate-500 flex items-center gap-2">
                                                        <Badge variant="secondary" class="font-normal text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border-none">
                                                            Auteur: Vous
                                                        </Badge>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <Button type="button" variant="ghost" size="sm" class="h-8 text-slate-500" @click="noteForm.reset('content')" v-if="noteForm.content">
                                                            Annuler
                                                        </Button>
                                                        <Button type="submit" :disabled="noteForm.processing || !noteForm.content" size="sm" class="h-8 bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm font-medium px-4">
                                                            Ajouter
                                                        </Button>
                                                    </div>
                                                </div>
                                            </div>
                                            <p v-if="noteForm.errors.content" class="text-red-500 text-xs mt-2">{{ noteForm.errors.content }}</p>
                                        </form>
                                    </div>

                                    <!-- Notes List -->
                                    <div class="divide-y divide-slate-100 max-h-[600px] overflow-y-auto">
                                        <div v-if="client.notes.length === 0" class="p-12 text-center flex flex-col items-center">
                                            <div class="w-12 h-12 bg-indigo-50 rounded-full flex items-center justify-center mb-3">
                                                <MessageSquare class="w-6 h-6 text-indigo-300" />
                                            </div>
                                            <p class="text-slate-500 font-medium">Aucune note pour le moment</p>
                                            <p class="text-sm text-slate-400 mt-1 max-w-[250px]">Laissez votre première note pour garder une trace des échanges ou rappels.</p>
                                        </div>

                                        <div v-for="note in client.notes" :key="note.id" class="p-5 hover:bg-slate-50/50 transition-colors group">
                                            <div class="flex items-start gap-4">
                                                <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center shrink-0 uppercase text-sm border border-indigo-200 shadow-sm">
                                                    {{ note.author.substring(0, 2) }}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center justify-between mb-1.5 flex-wrap gap-2">
                                                        <div class="flex items-center gap-2">
                                                            <span class="font-bold text-slate-900 text-sm">{{ note.author }}</span>
                                                            <span class="text-xs text-slate-400 whitespace-nowrap">&bull; {{ new Date(note.created_at).toLocaleString('fr-FR', {
                                                                day: '2-digit', month: 'short', hour: '2-digit', minute:'2-digit'
                                                            }) }}</span>
                                                        </div>
                                                        <button 
                                                            @click="deleteNote(note.id)" 
                                                            class="text-slate-400 hover:text-red-500 p-1.5 rounded-md hover:bg-red-50 transition-colors opacity-0 group-hover:opacity-100 focus:opacity-100"
                                                            title="Supprimer la note"
                                                        >
                                                            <Trash2 class="w-3.5 h-3.5" />
                                                        </button>
                                                    </div>
                                                    <div class="text-sm text-slate-700 whitespace-pre-wrap leading-relaxed">{{ note.content }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </TabsContent>

                            <!-- Factures Placeholder Tab -->
                            <TabsContent value="invoices" class="mt-0">
                                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-12 text-center">
                                    <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <FileText class="w-8 h-8 text-slate-300" />
                                    </div>
                                    <h3 class="font-bold text-lg text-slate-900 mb-2">Factures</h3>
                                    <p class="text-slate-500 max-w-sm mx-auto">La gestion des factures pour ce client sera bientôt disponible dans cette section.</p>
                                </div>
                            </TabsContent>

                            <!-- Détail activités Placeholder Tab -->
                            <TabsContent value="activities" class="mt-0">
                                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-12 text-center">
                                    <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <Calendar class="w-8 h-8 text-slate-300" />
                                    </div>
                                    <h3 class="font-bold text-lg text-slate-900 mb-2">Historique d'activités</h3>
                                    <p class="text-slate-500 max-w-sm mx-auto">Journalisation de toutes les actions liées à ce client : envois de devis, clics, etc. (En développement)</p>
                                </div>
                            </TabsContent>

                            <!-- Paiements Placeholder Tab -->
                            <TabsContent value="payments" class="mt-0">
                                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-12 text-center">
                                    <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <FileText class="w-8 h-8 text-slate-300" /> <!-- Replace with payment icon when adding -->
                                    </div>
                                    <h3 class="font-bold text-lg text-slate-900 mb-2">Paiements</h3>
                                    <p class="text-slate-500 max-w-sm mx-auto">Suivi des paiements pour les factures de ce client. Connexion Stripe/PayPal à venir.</p>
                                </div>
                            </TabsContent>

                            <!-- Projets Placeholder Tab -->
                            <TabsContent value="projects" class="mt-0">
                                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-12 text-center">
                                    <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <Building class="w-8 h-8 text-slate-300" />
                                    </div>
                                    <h3 class="font-bold text-lg text-slate-900 mb-2">Projets en cours</h3>
                                    <p class="text-slate-500 max-w-sm mx-auto">Organisez les devis et factures par projet pour ce client (ex: Rénovation Cuisine, Extension Maison).</p>
                                </div>
                            </TabsContent>
                        </Tabs>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirm Dialog for Client Deletion -->
        <AlertDialog :open="confirmAction !== null" @update:open="(val) => { if (!val) confirmAction = null }">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Supprimer ce client ?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Êtes-vous sûr de vouloir supprimer définitivement {{ client.name }} de vos clients ? 
                        Attention, la suppression d'un client pourrait affecter ses factures et devis liés.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="confirmAction = null">Annuler</AlertDialogCancel>
                    <button 
                        @click="executeConfirm" 
                        class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-red-600 hover:bg-red-700 h-10 px-4 py-2 text-white"
                    >
                        Oui, supprimer
                    </button>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <!-- Edit Client Form Sheet -->
        <Sheet v-model:open="isEditDialogOpen">
            <SheetContent class="w-full sm:max-w-md overflow-y-auto p-0 gap-0">
                <SheetHeader class="px-6 py-6 bg-slate-50 border-b border-slate-100 mb-6">
                    <SheetTitle>Modifier le client</SheetTitle>
                    <SheetDescription>
                        Mettez à jour les informations de {{ client.name }}.
                    </SheetDescription>
                </SheetHeader>

                <form @submit.prevent="submitEdit" class="space-y-4 px-6 pb-6">
                    <div class="space-y-2">
                        <Label for="name">Nom / Contact Principal *</Label>
                        <Input id="name" v-model="editForm.name" required />
                        <span class="text-xs text-red-500" v-if="editForm.errors.name">{{ editForm.errors.name }}</span>
                    </div>

                    <div class="space-y-2">
                        <Label for="company_name">Entreprise</Label>
                        <Input id="company_name" v-model="editForm.company_name" />
                        <span class="text-xs text-red-500" v-if="editForm.errors.company_name">{{ editForm.errors.company_name }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="email">Email</Label>
                            <Input id="email" type="email" v-model="editForm.email" />
                            <span class="text-xs text-red-500" v-if="editForm.errors.email">{{ editForm.errors.email }}</span>
                        </div>

                        <div class="space-y-2">
                            <Label for="phone">Téléphone</Label>
                            <Input id="phone" v-model="editForm.phone" />
                            <span class="text-xs text-red-500" v-if="editForm.errors.phone">{{ editForm.errors.phone }}</span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="tax_number">N° TVA / SIRET</Label>
                        <Input id="tax_number" v-model="editForm.tax_number" />
                    </div>

                    <div class="space-y-2">
                        <Label for="address">Adresse postale</Label>
                        <Input id="address" v-model="editForm.address" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="postal_code">Code postal</Label>
                            <Input id="postal_code" v-model="editForm.postal_code" />
                        </div>

                        <div class="space-y-2">
                            <Label for="city">Ville</Label>
                            <Input id="city" v-model="editForm.city" />
                        </div>
                    </div>

                    <div class="pt-6 flex justify-end gap-3">
                        <Button type="button" variant="outline" @click="isEditDialogOpen = false">
                            Annuler
                        </Button>
                        <Button type="submit" :disabled="editForm.processing" class="bg-indigo-600 hover:bg-indigo-700 text-white">
                            Enregistrer
                        </Button>
                    </div>
                </form>
            </SheetContent>
        </Sheet>

    </AppLayout>
</template>
