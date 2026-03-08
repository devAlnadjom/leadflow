<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { 
    Clock, 
    Inbox, 
    CheckCircle2, 
    ThumbsUp, 
    XCircle, 
    Mail, 
    Phone, 
    Calendar,
    MapPin,
    Briefcase,
    MessageSquare,
    FileText,
    Activity,
    Edit2,
    Trash2,
    MoreHorizontal,
    Plus,
    UserCircle2
} from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/AppLayout.vue';
import { useI18n } from '@/composables/useI18n';
import type { BreadcrumbItem } from '@/types';

type LeadPayload = {
    id: number;
    lead_form_id: number;
    lead_form_name: string;
    name: string | null;
    email: string | null;
    phone: string | null;
    source: string;
    status: string;
    value: number;
    payload: Record<string, unknown>;
    created_at: string | null;
    updated_at: string | null;
    notes?: Array<{
        id: number;
        author: string;
        created_at: string;
    }>;
    quotes?: Array<{
        id: number;
        public_uid: string;
        quote_number: string;
        status: string;
        total: number;
        expire_at: string | null;
        created_at: string;
    }>;
};

type Props = {
    lead: LeadPayload;
};

const props = defineProps<Props>();
const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('leads.title'),
        href: '/leads',
    },
    {
        title: props.lead.name || `Lead #${props.lead.id}`,
        href: `/leads/${props.lead.id}`,
    },
]);

const removeLead = (): void => {
    if(confirm('Voulez-vous vraiment supprimer ce lead ? Il sera effacé définitivement.')) {
        router.delete(`/leads/${props.lead.id}`);
    }
};

const statuses = [
    { id: 'new', label: 'Nouveau', icon: Inbox, color: 'text-blue-600 bg-blue-100' },
    { id: 'contacted', label: 'Contacté', icon: Clock, color: 'text-yellow-600 bg-yellow-100' },
    { id: 'qualified', label: 'Qualifié', icon: CheckCircle2, color: 'text-indigo-600 bg-indigo-100' },
    { id: 'won', label: 'Gagné', icon: ThumbsUp, color: 'text-green-600 bg-green-100' },
    { id: 'lost', label: 'Perdu', icon: XCircle, color: 'text-red-600 bg-red-100' }
];

const currentStatus = computed(() => {
    return statuses.find(s => s.id === props.lead.status) || statuses[0];
});

const updateStatus = (newStatus: string) => {
    router.patch(`/leads/${props.lead.id}/status`, { status: newStatus }, {
        preserveScroll: true,
        preserveState: true,
    });
};

const activeTab = ref('details');

const dynamicPayloadFields = computed(() => {
    const p = props.lead.payload;
    if (!p) return [];
    return Object.keys(p).filter(k => k !== '_leadflow_email').map(key => ({
        label: key,
        value: p[key]
    }));
});

const noteForm = useForm({
    content: ''
});

const submitNote = () => {
    noteForm.post(`/leads/${props.lead.id}/notes`, {
        preserveScroll: true,
        onSuccess: () => noteForm.reset('content'),
    });
};

const deleteNote = (noteId: number) => {
    if(confirm('Voulez-vous vraiment supprimer cette note ?')) {
        router.delete(`/leads/${props.lead.id}/notes/${noteId}`, {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head :title="`${lead.name || t('leads.show_title')} | LeadFlow`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="h-full flex flex-col bg-slate-50 min-h-screen">
            
            <!-- Contextual Header -->
            <div class="bg-white border-b border-slate-200 px-4 md:px-8 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 sticky top-0 z-10 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center border border-slate-200 shrink-0">
                        <UserCircle2 class="w-6 h-6 text-slate-400" />
                    </div>
                    <div>
                        <h1 class="text-xl md:text-2xl font-bold text-slate-900 leading-tight">
                            {{ lead.name || 'Contact Anonyme' }}
                        </h1>
                        <div class="flex items-center gap-3 text-sm text-slate-500 mt-1">
                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <button class="inline-flex items-center gap-1.5 font-medium hover:opacity-80 transition-opacity" :class="currentStatus.color.split(' ')[0]">
                                        <component :is="currentStatus.icon" class="w-4 h-4" />
                                        {{ currentStatus.label }}
                                        <Badge variant="outline" class="ml-1 text-[10px] uppercase font-bold text-slate-400 border-slate-200 hover:bg-slate-100 transition-colors">Changer</Badge>
                                    </button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start">
                                    <DropdownMenuLabel>Mettre à jour le statut</DropdownMenuLabel>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem 
                                        v-for="s in statuses" 
                                        :key="s.id"
                                        @click="updateStatus(s.id)"
                                        :class="{'bg-slate-50 font-medium': s.id === lead.status}"
                                    >
                                        <component :is="s.icon" class="w-4 h-4 mr-2" :class="s.color.split(' ')[0]" />
                                        {{ s.label }}
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                            <span>Ajouté le {{ lead.created_at ? new Date(lead.created_at).toLocaleDateString() : '-' }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto overflow-x-auto pb-1 sm:pb-0">
                    <Button variant="outline" class="gap-2 bg-white" as-child>
                        <Link :href="`/leads/${lead.id}/edit`">
                            <Edit2 class="w-4 h-4" /> Modifier
                        </Link>
                    </Button>
                    <!-- Placeholder future features -->
                    <Button class="gap-2 bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm" as-child>
                        <Link :href="`/leads/${lead.id}/quotes/create`">
                            <FileText class="w-4 h-4" /> Créer Devis
                        </Link>
                    </Button>
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="ghost" class="w-10 px-0 hover:bg-slate-200">
                                <MoreHorizontal class="w-5 h-5" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-48">
                            <DropdownMenuItem class="text-red-600 focus:bg-red-50 focus:text-red-700" @click="removeLead">
                                <Trash2 class="w-4 h-4 mr-2" /> Supprimer le lead
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>

            <!-- Page Content (Dashboard Grid) -->
            <div class="flex-1 p-4 md:p-8 max-w-7xl mx-auto w-full grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 items-start">
                
                <!-- Left Sidebar: Contact Info & Custom Fields -->
                <div class="lg:col-span-1 space-y-6">
                    
                    <!-- Quick Info Card -->
                    <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                            <h3 class="font-semibold text-slate-800">Coordonnées</h3>
                            <Button variant="ghost" size="sm" class="h-8 px-2 text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50" as-child>
                                <Link :href="`/leads/${lead.id}/edit`">Éditer</Link>
                            </Button>
                        </div>
                        <div class="p-5 space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shrink-0 mt-0.5">
                                    <Mail class="w-4 h-4 text-slate-500" />
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-slate-500 mb-0.5">Courriel</p>
                                    <p class="text-sm font-medium text-slate-900 break-words">
                                        <a v-if="lead.email" :href="'mailto:' + lead.email" class="hover:underline hover:text-indigo-600">{{ lead.email }}</a>
                                        <span v-else class="text-slate-400 italic">Non renseigné</span>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shrink-0 mt-0.5">
                                    <Phone class="w-4 h-4 text-slate-500" />
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-slate-500 mb-0.5">Téléphone</p>
                                    <p class="text-sm font-medium text-slate-900">
                                        <a v-if="lead.phone" :href="'tel:' + lead.phone" class="hover:underline hover:text-indigo-600">{{ lead.phone }}</a>
                                        <span v-else class="text-slate-400 italic">Non renseigné</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Widget Payload Card -->
                    <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                            <h3 class="font-semibold text-slate-800 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-green-500"></span> Source du Lead
                            </h3>
                        </div>
                        <div class="p-5">
                            <div class="mb-4">
                                <p class="text-xs font-medium text-slate-500 mb-1">Formulaire/Widget utilisé</p>
                                <Badge variant="secondary" class="font-medium bg-slate-100 text-slate-700 hover:bg-slate-200">{{ lead.lead_form_name }}</Badge>
                            </div>
                            
                            <div class="space-y-4 pt-4 border-t border-slate-100">
                                <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Réponses au formulaire</h4>
                                
                                <div v-if="dynamicPayloadFields.length === 0" class="text-sm text-slate-500 italic">
                                    Aucune donnée supplémentaire.
                                </div>

                                <div v-for="(field, index) in dynamicPayloadFields" :key="index" class="space-y-1">
                                    <p class="text-xs font-medium text-slate-500 capitalize">{{ field.label.replace(/_/g, ' ') }}</p>
                                    <p class="text-sm text-slate-900 bg-slate-50 p-2 rounded border border-slate-100 break-words">
                                        {{ Array.isArray(field.value) ? field.value.join(', ') : (field.value || '-') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                </div>

                <!-- Right Main Area: Tabs (Activity, Devis, Notes) -->
                <div class="lg:col-span-2">
                    <Tabs v-model="activeTab" class="w-full">
                        <TabsList class="w-full bg-slate-200/50 p-1 mb-6 h-auto flex flex-wrap gap-1 border border-slate-200">
                            <TabsTrigger value="details" class="flex-1 py-2 data-[state=active]:bg-white data-[state=active]:shadow-sm rounded-md transition-all text-sm font-medium flex items-center justify-center gap-2">
                                <Activity class="w-4 h-4" /> Détails & Activité
                            </TabsTrigger>
                            <TabsTrigger value="quotes" class="flex-1 py-2 data-[state=active]:bg-white data-[state=active]:shadow-sm rounded-md transition-all text-sm font-medium flex items-center justify-center gap-2 text-slate-500 data-[state=active]:text-slate-900">
                                <FileText class="w-4 h-4" /> Devis <Badge class="ml-1 bg-slate-100 text-slate-500 hover:bg-slate-200 px-1 py-0 h-4 min-w-4 flex items-center justify-center">{{ lead.quotes?.length ?? 0 }}</Badge>
                            </TabsTrigger>
                            <TabsTrigger value="notes" class="flex-1 py-2 data-[state=active]:bg-white data-[state=active]:shadow-sm rounded-md transition-all text-sm font-medium flex items-center justify-center gap-2 text-slate-500 data-[state=active]:text-slate-900">
                                <MessageSquare class="w-4 h-4" /> Notes internes
                            </TabsTrigger>
                        </TabsList>

                        <TabsContent value="details" class="focus-visible:outline-none focus-visible:ring-0 mt-0">
                            <div class="bg-white border text-center p-12 rounded-xl shadow-sm border-dashed">
                                <div class="w-16 h-16 mx-auto bg-indigo-50 text-indigo-500 flex items-center justify-center rounded-full mb-4">
                                    <Activity class="w-8 h-8" />
                                </div>
                                <h3 class="text-lg font-semibold text-slate-900 mb-2">Historique d'Activité</h3>
                                <p class="text-slate-500 mb-6 max-w-sm mx-auto">
                                    Bientôt, vous pourrez voir ici l'historique complet des intéractions, appels, courriels et changements de statuts pour ce lead.
                                </p>
                                <Button variant="outline" class="gap-2 mx-auto">
                                    <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                                    En développement
                                </Button>
                            </div>
                        </TabsContent>

                        <TabsContent value="quotes" class="focus-visible:outline-none focus-visible:ring-0 mt-0">
                            <!-- Quotes List -->
                            <div v-if="lead.quotes && lead.quotes.length > 0" class="space-y-4">
                                <div v-for="quote in lead.quotes" :key="quote.id" class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:border-indigo-200 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 border border-slate-100">
                                            <FileText class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900">{{ quote.quote_number }}</p>
                                            <p class="text-xs text-slate-500">Créé le {{ new Date(quote.created_at).toLocaleDateString() }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-6">
                                        <div class="text-right">
                                            <p class="text-sm font-bold text-slate-900">{{ quote.total }}$</p>
                                            <Badge variant="outline" class="text-[10px] uppercase px-1.5 py-0 h-4.5 border-slate-200 text-slate-500">{{ quote.status }}</Badge>
                                        </div>
                                        <Button variant="ghost" size="sm" class="text-indigo-600 font-semibold" as-child>
                                            <Link :href="`/quotes/${quote.id}/edit`">Ouvrir</Link>
                                        </Button>
                                    </div>
                                </div>
                                <div class="pt-2 flex justify-center">
                                    <Button variant="outline" class="gap-2 text-indigo-600 border-indigo-100 hover:bg-indigo-50" as-child>
                                        <Link :href="`/leads/${lead.id}/quotes/create`">
                                            <Plus class="w-4 h-4" /> Nouveau Devis
                                        </Link>
                                    </Button>
                                </div>
                            </div>
                            <!-- Empty State -->
                            <div v-else class="bg-white border text-center p-12 rounded-xl shadow-sm border-dashed">
                                <div class="w-16 h-16 mx-auto bg-green-50 text-green-500 flex items-center justify-center rounded-full mb-4">
                                    <FileText class="w-8 h-8" />
                                </div>
                                <h3 class="text-lg font-semibold text-slate-900 mb-2">Devis associés</h3>
                                <p class="text-slate-500 mb-6 max-w-sm mx-auto">
                                    Convertissez ce lead en client payant en lui envoyant un devis esthétique et détaillé.
                                </p>
                                <Button class="gap-2 bg-indigo-600 hover:bg-indigo-700 text-white mx-auto" as-child>
                                    <Link :href="`/leads/${lead.id}/quotes/create`">
                                        <Plus class="w-4 h-4" />
                                        Créer un Devis
                                    </Link>
                                </Button>
                            </div>
                        </TabsContent>

                        <TabsContent value="notes" class="focus-visible:outline-none focus-visible:ring-0 mt-0">
                            <!-- New Note Form -->
                            <div class="bg-white border rounded-xl shadow-sm mb-6 p-5">
                                <h3 class="text-sm font-semibold text-slate-800 mb-3 flex items-center gap-2">
                                    <MessageSquare class="w-4 h-4 text-amber-500" /> Ajouter une note
                                </h3>
                                <form @submit.prevent="submitNote">
                                    <textarea 
                                        v-model="noteForm.content"
                                        rows="3" 
                                        class="w-full text-sm rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mb-3 resize-none p-3 bg-slate-50"
                                        placeholder="Note confidentielle pour l'équipe..."
                                        required
                                    ></textarea>
                                    <div class="flex justify-end">
                                        <Button 
                                            type="submit" 
                                            :disabled="noteForm.processing || !noteForm.content.trim()"
                                            class="gap-2 bg-indigo-600 hover:bg-indigo-700 h-9"
                                        >
                                            <Plus class="w-4 h-4" /> Enregistrer la note
                                        </Button>
                                    </div>
                                    <p v-if="noteForm.errors.content" class="text-sm text-red-600 mt-2">{{ noteForm.errors.content }}</p>
                                </form>
                            </div>

                            <!-- List of Notes -->
                            <div v-if="lead.notes && lead.notes.length > 0" class="space-y-4">
                                <div 
                                    v-for="note in lead.notes" 
                                    :key="note.id" 
                                    class="bg-amber-50/50 border border-amber-100 rounded-xl p-5 relative group"
                                >
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-xs font-bold uppercase">
                                                {{ note.author.substring(0, 2) }}
                                            </div>
                                            <div>
                                                <p class="text-xs font-semibold text-slate-800">{{ note.author }}</p>
                                                <p class="text-[10px] text-slate-500">{{ new Date(note.created_at).toLocaleString() }}</p>
                                            </div>
                                        </div>
                                        <button 
                                            @click="deleteNote(note.id)" 
                                            class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded opacity-0 group-hover:opacity-100 transition-all"
                                            title="Supprimer"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                    <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ note.content }}</p>
                                </div>
                            </div>

                            <!-- Empty State if no notes -->
                            <div v-else class="bg-white border text-center p-12 rounded-xl shadow-sm border-dashed">
                                <div class="w-16 h-16 mx-auto bg-amber-50 text-amber-500 flex items-center justify-center rounded-full mb-4">
                                    <MessageSquare class="w-8 h-8" />
                                </div>
                                <h3 class="text-lg font-semibold text-slate-900 mb-2">Aucune note</h3>
                                <p class="text-slate-500 mb-6 max-w-sm mx-auto">
                                    Utilisez cet espace pour conserver des informations que le client ne doit pas voir.
                                </p>
                            </div>
                        </TabsContent>
                    </Tabs>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
