<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { 
    Search,
    MapPin,
    Building2,
    Briefcase,
    ArrowRight,
    Star,
    CheckCircle2
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

interface CompanyPreview {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    industry: string | null;
    served_areas: string[];
    logo_url: string | null;
    cover_url: string | null;
    primary_color: string | null;
}

interface PaginationData {
    current_page: number;
    data: CompanyPreview[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: { url: string | null; label: string; active: boolean }[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

const props = defineProps<{
    companies: PaginationData;
    filters: {
        q: string;
        location: string;
    };
    popularLocations: string[];
}>();

const searchQuery = ref(props.filters.q);
const locationQuery = ref(props.filters.location);

// Simple debounce function setup
let timeout: any = null;

const applyFilters = () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get('/annuaire', {
            q: searchQuery.value,
            location: locationQuery.value
        }, { preserveState: true, replace: true });
    }, 400);
};

const selectLocation = (loc: string) => {
    if (locationQuery.value === loc) {
        locationQuery.value = '';
    } else {
        locationQuery.value = loc;
    }
    applyFilters();
};

watch(searchQuery, applyFilters);
watch(locationQuery, applyFilters);

</script>

<template>
    <div class="min-h-screen bg-slate-50 font-sans text-slate-900 selection:bg-indigo-100 selection:text-indigo-900">
        <Head>
            <title>Annuaire des Professionnels et Entreprises Locales | LeadFlow</title>
            <meta name="description" content="Découvrez les meilleurs professionnels, artisans et entreprises locales près de chez vous. Consultez les profils, les services et demandez des devis gratuitement." />
            <meta property="og:title" content="Annuaire des Professionnels | LeadFlow" />
            <meta property="og:description" content="Trouvez le prestataire idéal pour vos projets parmi notre sélection d'entreprises locales certifiées." />
            <meta property="og:type" content="website" />
            <meta name="robots" content="index, follow" />
        </Head>

        <!-- Navbar -->
        <header class="sticky top-0 z-50 bg-white/70 backdrop-blur-xl border-b border-slate-200/50 transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
                <Link href="/" class="flex items-center gap-3 group focus:outline-none">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-600 to-violet-600 flex items-center justify-center text-white font-black text-sm shadow-lg shadow-indigo-500/30 group-hover:shadow-indigo-500/50 transition-all duration-300">LF</div>
                    <span class="font-black text-2xl tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-slate-900 to-slate-700">LeadFlow</span>
                </Link>
                <nav class="hidden md:flex items-center gap-8" aria-label="Navigation principale">
                    <Link href="/" class="text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors">Accueil</Link>
                    <span class="text-sm font-bold text-indigo-600 relative after:absolute after:bottom-[-26px] after:left-0 after:w-full after:h-[2px] after:bg-indigo-600">Annuaire</span>
                </nav>
                <div class="flex items-center gap-4">
                    <Button variant="ghost" class="hidden sm:flex font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-full" as-child>
                        <Link href="/login">Espace Pro</Link>
                    </Button>
                    <Button class="font-bold bg-slate-900 text-white hover:bg-slate-800 rounded-full shadow-lg hover:shadow-xl transition-all" as-child>
                        <Link href="/onboarding/company">Inscrire mon entreprise</Link>
                    </Button>
                </div>
            </div>
        </header>

        <main>
            <!-- Hero Section -->
            <section class="bg-white relative overflow-hidden pb-12 pt-20 lg:pt-32 border-b border-slate-200/50">
                <!-- Abstract Background Elements -->
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-full pointer-events-none">
                    <div class="absolute top-[-20%] right-[-10%] w-[600px] h-[600px] rounded-full bg-gradient-to-br from-indigo-100/40 to-violet-100/40 blur-3xl mix-blend-multiply"></div>
                    <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] rounded-full bg-gradient-to-tr from-blue-100/40 to-indigo-100/40 blur-3xl mix-blend-multiply"></div>
                </div>

                <div class="max-w-4xl mx-auto px-4 relative z-10 text-center space-y-10 animate-in fade-in slide-in-from-bottom-8 duration-700">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-50/80 backdrop-blur-sm border border-indigo-100/50 text-xs font-black uppercase tracking-widest text-indigo-600 shadow-sm">
                        <CheckCircle2 class="w-4 h-4" />
                        Réseau de professionnels certifiés
                    </div>
                    
                    <h1 class="text-5xl lg:text-7xl font-black tracking-tight text-slate-900 leading-[1.1] md:leading-[1.1]">
                        Trouvez le <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-violet-600">professionnel</span> de confiance.
                    </h1>
                    
                    <p class="text-xl text-slate-500 leading-relaxed max-w-2xl mx-auto font-medium">
                        Découvrez les meilleurs artisans et experts locaux prêts à vous accompagner dans la réalisation de vos projets.
                    </p>

                    <!-- Interactive Search Interface -->
                    <div class="bg-white/60 backdrop-blur-2xl p-2 rounded-[2rem] shadow-2xl shadow-indigo-500/10 border border-white max-w-3xl mx-auto flex flex-col md:flex-row gap-2 relative z-20">
                        <div class="relative flex-1 flex items-center bg-white rounded-full transition-shadow focus-within:shadow-md">
                            <Search class="absolute left-6 w-5 h-5 text-indigo-400" />
                            <Input v-model="searchQuery" placeholder="Quel service recherchez-vous ?" class="pl-14 h-16 rounded-full border-0 bg-transparent text-lg focus-visible:ring-0 shadow-none font-medium placeholder:text-slate-400" aria-label="Rechercher un service" />
                        </div>
                        <div class="relative flex-1 flex items-center bg-white rounded-full transition-shadow focus-within:shadow-md">
                            <MapPin class="absolute left-6 w-5 h-5 text-indigo-400" />
                            <Input v-model="locationQuery" placeholder="Code postal, Ville..." class="pl-14 h-16 rounded-full border-0 bg-transparent text-lg focus-visible:ring-0 shadow-none font-medium placeholder:text-slate-400" aria-label="Localisation" />
                        </div>
                    </div>
                </div>
            </section>

            <section class="max-w-7xl mx-auto px-4 py-16 sm:px-6 lg:px-8 flex flex-col md:flex-row gap-12">
                <!-- Sidebar Filters -->
                <aside class="w-full md:w-64 shrink-0 animate-in fade-in slide-in-from-left duration-1000">
                    <div class="bg-white p-6 rounded-3xl border border-slate-200/60 shadow-sm sticky top-28">
                        <h2 class="font-black text-sm uppercase tracking-widest text-slate-900 mb-6 flex items-center gap-2">
                            <MapPin class="w-4 h-4 text-indigo-600" />
                            Villes Populaires
                        </h2>
                        <div class="flex flex-wrap gap-2">
                            <button 
                                v-for="loc in popularLocations" 
                                :key="loc"
                                @click="selectLocation(loc)"
                                :class="[
                                    'px-4 py-2 rounded-xl text-sm font-bold transition-all duration-200',
                                    locationQuery === loc 
                                        ? 'bg-slate-900 text-white shadow-md' 
                                        : 'bg-slate-50 text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                                ]"
                                :aria-pressed="locationQuery === loc"
                            >
                                {{ loc }}
                            </button>
                        </div>
                        <p v-if="popularLocations.length === 0" class="text-sm text-slate-500 italic mt-4">Aucune localité définie.</p>
                    </div>
                </aside>

                <!-- Results -->
                <div class="flex-1 space-y-8">
                    <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200/60 pb-6">
                        <h2 class="text-2xl font-black text-slate-900 tracking-tight">
                            Résultats de recherche
                        </h2>
                        <span class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-sm font-bold">
                            {{ companies.total }} {{ companies.total > 1 ? 'entreprises trouvées' : 'entreprise trouvée' }}
                        </span>
                    </header>

                    <!-- Grid -->
                    <div v-if="companies.data.length > 0" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        <article v-for="company in companies.data" :key="company.id" class="group bg-white rounded-[2rem] border border-slate-200/60 shadow-sm hover:shadow-2xl hover:shadow-indigo-500/10 hover:border-indigo-100 transition-all duration-500 flex flex-col overflow-hidden relative">
                            <!-- Cover Banner -->
                            <Link :href="`/c/${company.slug}`" class="block aspect-[16/10] w-full relative bg-slate-100 overflow-hidden focus:outline-none" :aria-label="`Voir le profil de ${company.name}`">
                                <img v-if="company.cover_url" :src="company.cover_url" :alt="`Couverture de ${company.name}`" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out" />
                                <div v-else class="absolute inset-0 opacity-30" :style="{ background: `linear-gradient(135deg, ${company.primary_color || '#4f46e5'} 0%, transparent 100%)` }"></div>
                                
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/0 to-black/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                                <!-- Industry Badge Overlay -->
                                <div class="absolute top-4 right-4 z-10">
                                    <span v-if="company.industry" class="px-3 py-1.5 rounded-full bg-white/95 backdrop-blur shadow-sm text-[10px] font-black uppercase tracking-widest text-slate-800">
                                        {{ company.industry }}
                                    </span>
                                </div>
                            </Link>

                            <div class="p-6 flex-1 flex flex-col relative pt-12">
                                <!-- Logo overlapping banner -->
                                <div class="absolute -top-10 left-6">
                                    <div 
                                        class="w-20 h-20 rounded-2xl bg-white flex items-center justify-center border-4 border-white shadow-lg z-20 overflow-hidden relative"
                                        :style="company.logo_url ? {} : { backgroundColor: `${company.primary_color || '#4f46e5'}15` }"
                                    >
                                        <img v-if="company.logo_url" :src="company.logo_url" :alt="`Logo de ${company.name}`" class="w-full h-full object-contain p-1" />
                                        <Building2 v-else class="w-8 h-8" :style="{ color: company.primary_color || '#4f46e5' }" />
                                    </div>
                                </div>
                                
                                <h3 class="text-xl font-black mb-2 line-clamp-1 group-hover:text-indigo-600 transition-colors">
                                    <Link :href="`/c/${company.slug}`" class="focus:outline-none before:absolute before:inset-0">
                                        {{ company.name }}
                                    </Link>
                                </h3>
                                
                                <p class="text-slate-500 line-clamp-2 mb-6 flex-1 font-medium leading-relaxed">
                                    {{ company.description || 'Prestations et services professionnels de qualité.' }}
                                </p>

                                <div class="mt-auto space-y-5 pt-5 border-t border-slate-100">
                                    <!-- Tags for served areas -->
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="area in company.served_areas.slice(0, 2)" :key="area" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-600 relative z-10">
                                            <MapPin class="w-3 h-3 text-slate-400" />
                                            {{ area }}
                                        </span>
                                        <span v-if="company.served_areas.length > 2" class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-600 relative z-10">
                                            +{{ company.served_areas.length - 2 }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center text-sm font-bold text-indigo-600 group/link relative z-10">
                                        Voir le profil professionnel
                                        <ArrowRight class="w-4 h-4 ml-1.5 transition-transform group-hover/link:translate-x-1" />
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                    
                    <div v-else class="text-center py-24 bg-white rounded-[2rem] border-2 border-dashed border-slate-200 animate-in fade-in zoom-in-95 duration-500">
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <Search class="w-8 h-8 text-slate-400" />
                        </div>
                        <h3 class="text-2xl font-black mb-2">Aucun professionnel trouvé</h3>
                        <p class="text-slate-500 max-w-md mx-auto">Essayez d'ajuster vos critères de recherche ou de sélectionner une autre localité pour trouver le prestataire idéal.</p>
                        <Button variant="outline" class="mt-6 rounded-full font-bold" @click="() => { searchQuery = ''; locationQuery = ''; applyFilters(); }">
                            Réinitialiser les filtres
                        </Button>
                    </div>

                    <!-- Pagination -->
                    <nav v-if="companies.last_page > 1" class="flex justify-center flex-wrap gap-2 pt-12 border-t border-slate-200/60" aria-label="Pagination">
                        <template v-for="(link, i) in companies.links" :key="i">
                            <Link 
                                v-if="link.url"
                                :href="link.url" 
                                v-html="link.label.replace('Next', 'Suivant').replace('Previous', 'Précédent')"
                                :class="[
                                    'px-4 py-2 min-w-[40px] text-center rounded-xl text-sm font-bold transition-all duration-200',
                                    link.active 
                                        ? 'bg-slate-900 text-white shadow-md' 
                                        : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900'
                                ]"
                                :aria-current="link.active ? 'page' : undefined"
                            />
                            <span 
                                v-else 
                                v-html="link.label.replace('Next', 'Suivant').replace('Previous', 'Précédent')"
                                class="px-4 py-2 min-w-[40px] text-center rounded-xl text-sm font-bold text-slate-400 bg-slate-50 cursor-not-allowed"
                            />
                        </template>
                    </nav>
                </div>
            </section>
        </main>
        
        <!-- Footer simple -->
        <footer class="bg-white border-t border-slate-200 mt-12 py-12">
            <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row items-center justify-between gap-4 text-center md:text-left">
                <div class="flex items-center gap-2 text-slate-400">
                    <span class="font-black text-slate-900 text-lg">LeadFlow</span>
                    <span class="text-sm font-medium">© {{ new Date().getFullYear() }} Tous droits réservés.</span>
                </div>
                <div class="flex gap-6 text-sm font-bold text-slate-500">
                    <Link href="#" class="hover:text-slate-900 transition-colors">Mentions légales</Link>
                    <Link href="#" class="hover:text-slate-900 transition-colors">Confidentialité</Link>
                </div>
            </div>
        </footer>
    </div>
</template>

