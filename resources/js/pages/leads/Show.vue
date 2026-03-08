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
    MapPin,
    Briefcase,
    MessageSquare,
    FileText,
    Activity,
    Edit2,
    Trash2,
    MoreHorizontal,
    Plus,
    UserCircle2,
    Check
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
    updated_at: string | null;
    notes?: Array<{
        id: number;
        content: string;
        type: 'note' | 'call' | 'email' | 'meeting';
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
    content: '',
    type: 'note' as 'note' | 'call' | 'email' | 'meeting'
});

const submitNote = () => {
    noteForm.post(`/leads/${props.lead.id}/notes`, {
        preserveScroll: true,
        onSuccess: () => {
            noteForm.reset('content');
            // Keep the selected type for convenience, or reset it: noteForm.reset('content', 'type')
        },
    });
};

const deleteNote = (noteId: number) => {
    if(confirm('Voulez-vous vraiment supprimer cette note ?')) {
        router.delete(`/leads/${props.lead.id}/notes/${noteId}`, {
            preserveScroll: true
        });
    }
};

const activities = computed(() => {
    let events = [];

    if (props.lead.created_at) {
        events.push({
            id: 'created',
            title: 'Nouveau lead généré',
            description: `Reçu depuis le formulaire "${props.lead.lead_form_name}"`,
            date: props.lead.created_at,
            icon: Inbox,
            color: 'text-emerald-600',
            bg: 'bg-emerald-100',
            border: 'border-emerald-200'
        });
    }

    if (props.lead.notes) {
        props.lead.notes.forEach(note => {
            let icon = MessageSquare;
            let title = 'Note interne ajoutée';
            let color = 'text-amber-600';
            let bg = 'bg-amber-100';
            let border = 'border-amber-200';

            if (note.type === 'call') {
                icon = Phone;
                title = 'Appel consigné';
                color = 'text-blue-600';
                bg = 'bg-blue-100';
                border = 'border-blue-200';
            } else if (note.type === 'email') {
                icon = Mail;
                title = 'Email consigné';
                color = 'text-cyan-600';
                bg = 'bg-cyan-100';
                border = 'border-cyan-200';
            } else if (note.type === 'meeting') {
                icon = Calendar;
                title = 'Rendez-vous consigné';
                color = 'text-purple-600';
                bg = 'bg-purple-100';
                border = 'border-purple-200';
            }

            events.push({
                id: `note-${note.id}`,
                title: title,
                description: `Par ${note.author}: "${note.content.substring(0, 50)}${note.content.length > 50 ? '...' : ''}"`,
                date: note.created_at,
                icon: icon,
                color: color,
                bg: bg,
                border: border
            });
        });
    }

    if (props.lead.quotes) {
        props.lead.quotes.forEach(quote => {
            events.push({
                id: `quote-${quote.id}`,
                title: 'Nouveau devis créé',
                description: `Devis N°${quote.quote_number} estimé à ${quote.total}$`,
                date: quote.created_at,
                icon: FileText,
                color: 'text-indigo-600',
                bg: 'bg-indigo-100',
                border: 'border-indigo-200'
            });
        });
    }

    // Sort descending by date
    return events.sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());
});
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
                            <div class="bg-white border rounded-xl shadow-sm overflow-hidden border-slate-200">
                                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex align-center justify-between">
                                    <h3 class="font-semibold text-slate-800 flex items-center gap-2">
                                        <Activity class="w-4 h-4 text-indigo-500" /> Historique d'Activité
                                    </h3>
                                    <Badge variant="outline" class="font-normal bg-white text-slate-500 border-slate-200">{{ activities.length }} événements</Badge>
                                </div>
                                <div class="p-6">
                                    <div class="relative border-l-2 border-slate-100 ml-3 md:ml-4 space-y-8 pb-4 mt-2">
                                        <div v-for="activity in activities" :key="activity.id" class="relative pl-6 md:pl-8 group">
                                            <!-- Timeline Dot -->
                                            <div class="absolute -left-[17px] top-1 w-8 h-8 rounded-full border-2 border-white flex items-center justify-center shrink-0 z-10 shadow-sm transition-transform group-hover:scale-110" :class="[activity.bg, activity.border, activity.color]">
                                                <component :is="activity.icon" class="w-4 h-4" />
                                            </div>
                                            <!-- Content -->
                                            <div class="bg-slate-50/50 rounded-xl p-4 border border-slate-100 shadow-sm hover:border-slate-200 hover:bg-slate-50 transition-colors">
                                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 sm:gap-4 mb-2">
                                                    <h4 class="font-bold text-slate-800 text-sm">{{ activity.title }}</h4>
                                                    <span class="text-xs font-semibold text-slate-400 flex items-center gap-1.5 shrink-0">
                                                        <Clock class="w-3.5 h-3.5" /> {{ new Date(activity.date).toLocaleString([], { dateStyle: 'short', timeStyle: 'short' }) }}
                                                    </span>
                                                </div>
                                                <p class="text-sm text-slate-600">{{ activity.description }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                                <h3 class="text-sm font-semibold text-slate-800 mb-4 flex items-center gap-2">
                                    <MessageSquare class="w-4 h-4 text-amber-500" /> Ajouter une activité ou une note
                                </h3>
                                <form @submit.prevent="submitNote">
                                    <!-- Type Selection Widgets -->
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        <button 
                                            type="button" 
                                            @click="noteForm.type = 'note'"
                                            class="px-4 py-2 rounded-lg text-sm font-medium border transition-colors flex items-center gap-2"
                                            :class="noteForm.type === 'note' ? 'bg-amber-50 border-amber-200 text-amber-700' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'"
                                        >
                                            <MessageSquare class="w-4 h-4" /> Note générique
                                        </button>
                                        <button 
                                            type="button" 
                                            @click="noteForm.type = 'call'"
                                            class="px-4 py-2 rounded-lg text-sm font-medium border transition-colors flex items-center gap-2"
                                            :class="noteForm.type === 'call' ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'"
                                        >
                                            <Phone class="w-4 h-4" /> Appel effectif
                                        </button>
                                        <button 
                                            type="button" 
                                            @click="noteForm.type = 'email'"
                                            class="px-4 py-2 rounded-lg text-sm font-medium border transition-colors flex items-center gap-2"
                                            :class="noteForm.type === 'email' ? 'bg-cyan-50 border-cyan-200 text-cyan-700' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'"
                                        >
                                            <Mail class="w-4 h-4" /> Email envoyé
                                        </button>
                                        <button 
                                            type="button" 
                                            @click="noteForm.type = 'meeting'"
                                            class="px-4 py-2 rounded-lg text-sm font-medium border transition-colors flex items-center gap-2"
                                            :class="noteForm.type === 'meeting' ? 'bg-purple-50 border-purple-200 text-purple-700' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'"
                                        >
                                            <Calendar class="w-4 h-4" /> Rendez-vous
                                        </button>
                                    </div>

                                    <textarea 
                                        v-model="noteForm.content"
                                        rows="3" 
                                        class="w-full text-sm rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mb-3 resize-none p-3 bg-slate-50"
                                        :placeholder="noteForm.type === 'call' ? 'Résumé de l\'appel...' : noteForm.type === 'email' ? 'Sujet ou contenu de l\'email...' : noteForm.type === 'meeting' ? 'Compte-rendu du rendez-vous...' : 'Note confidentielle...'"
                                        required
                                    ></textarea>
                                    <div class="flex justify-end">
                                        <Button 
                                            type="submit" 
                                            :disabled="noteForm.processing || !noteForm.content.trim()"
                                            class="gap-2 bg-indigo-600 hover:bg-indigo-700 h-9"
                                        >
                                            <Plus class="w-4 h-4" /> Consigner l'activité
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
                                    class="border rounded-xl p-5 relative group"
                                    :class="{
                                        'bg-amber-50/50 border-amber-100': note.type === 'note',
                                        'bg-blue-50/50 border-blue-100': note.type === 'call',
                                        'bg-cyan-50/50 border-cyan-100': note.type === 'email',
                                        'bg-purple-50/50 border-purple-100': note.type === 'meeting'
                                    }"
                                >
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex items-center gap-3">
                                            <div 
                                                class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 border"
                                                :class="{
                                                    'bg-amber-100 text-amber-600 border-amber-200': note.type === 'note',
                                                    'bg-blue-100 text-blue-600 border-blue-200': note.type === 'call',
                                                    'bg-cyan-100 text-cyan-600 border-cyan-200': note.type === 'email',
                                                    'bg-purple-100 text-purple-600 border-purple-200': note.type === 'meeting'
                                                }"
                                            >
                                                <MessageSquare v-if="note.type === 'note'" class="w-4 h-4" />
                                                <Phone v-if="note.type === 'call'" class="w-4 h-4" />
                                                <Mail v-if="note.type === 'email'" class="w-4 h-4" />
                                                <Calendar v-if="note.type === 'meeting'" class="w-4 h-4" />
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-[10px] uppercase font-bold tracking-wider"
                                                        :class="{
                                                            'text-amber-700': note.type === 'note',
                                                            'text-blue-700': note.type === 'call',
                                                            'text-cyan-700': note.type === 'email',
                                                            'text-purple-700': note.type === 'meeting'
                                                        }"
                                                    >
                                                        {{ note.type === 'note' ? 'Note interne' : note.type === 'call' ? 'Appel' : note.type === 'email' ? 'Email' : 'Rendez-vous' }}
                                                    </span>
                                                    <span class="text-slate-300">•</span>
                                                    <p class="text-xs font-semibold text-slate-700">{{ note.author }}</p>
                                                </div>
                                                <p class="text-[10px] text-slate-500">{{ new Date(note.created_at).toLocaleString([], { dateStyle: 'long', timeStyle: 'short' }) }}</p>
                                            </div>
                                        </div>
                                        <button 
                                            @click="deleteNote(note.id)" 
                                            class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded opacity-0 group-hover:opacity-100 transition-all border border-transparent hover:border-red-100"
                                            title="Supprimer"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                    <div class="pl-11 pr-2 pt-1 text-sm text-slate-800 whitespace-pre-wrap leading-relaxed">
                                        {{ note.content }}
                                    </div>
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
