<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    Facebook, 
    Instagram, 
    Phone, 
    Mail, 
    MapPin, 
    CheckCircle2, 
    ArrowRight,
    Search,
    Globe,
    Building2,
    Briefcase,
    Images,
    Clock,
    Star
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { toast } from 'vue-sonner';

interface Service {
    id: number;
    name: string;
    description: string | null;
    image_url: string | null;
}

interface Gallery {
    id: number;
    image_url: string;
    caption: string | null;
}

const props = defineProps<{
    company: {
        name: string;
        description: string | null;
        phone: string | null;
        email: string | null;
        address: string | null;
        website: string | null;
        industry: string | null;
        served_areas: string[];
        primary_color: string | null;
        secondary_color: string | null;
        logo_url: string | null;
        slug: string;
    };
    settings: {
        facebook_url: string | null;
        instagram_url: string | null;
        seo_title: string | null;
        seo_description: string | null;
        seo_keywords: string | null;
    };
    services: Service[];
    galleries: Gallery[];
    leadForm: {
        id: number;
        name: string;
        button_label: string;
    } | null;
}>();

const primaryColor = props.company.primary_color || '#4f46e5'; // Default to indigo-600
const bgColor = computed(() => {
    return props.company.primary_color ? `${props.company.primary_color}08` : '#f8fafc';
});

const searchCity = ref('');
const filteredCities = computed(() => {
    if (!searchCity.value) return props.company.served_areas;
    return props.company.served_areas.filter(city => city.toLowerCase().includes(searchCity.value.toLowerCase()));
});

// Simple lead form logic
const form = useForm({
    name: '',
    email: '',
    phone: '',
    message: '',
});

const submitLead = () => {
    form.post(`/c/${props.company.slug}/contact`, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            toast.success('Message envoyé avec succès !');
        },
    });
};

const seoTitle = computed(() => props.settings.seo_title || `${props.company.name} - ${props.company.industry || 'Professionnel'}`);
const seoDesc = computed(() => props.settings.seo_description || props.company.description || `Découvrez les services de ${props.company.name}. Contactez-nous pour un devis gratuit.`);

</script>

<template>
    <div class="min-h-screen bg-white font-sans text-slate-900 selection:bg-brand-primary/20 selection:text-brand-primary scroll-smooth">
        <Head>
            <title>{{ seoTitle }}</title>
            <meta name="description" :content="seoDesc" />
            <meta v-if="settings.seo_keywords" name="keywords" :content="settings.seo_keywords" />
            
            <!-- Open Graph / SEO -->
            <meta property="og:type" content="profile" />
            <meta property="og:title" :content="seoTitle" />
            <meta property="og:description" :content="seoDesc" />
            <meta v-if="company.logo_url" property="og:image" :content="company.logo_url" />
            <meta property="profile:username" :content="company.slug" />
            
            <meta name="robots" content="index, follow" />
        </Head>

        <!-- Dynamic styles for brand colors -->
        <component :is="'style'">
            :root {
                --brand-primary: {{ primaryColor }};
                --brand-bg: {{ bgColor }};
            }
            .brand-bg { background-color: var(--brand-primary); }
            .brand-text { color: var(--brand-primary); }
            .brand-border { border-color: var(--brand-primary); }
            .brand-bg-soft { background-color: var(--brand-bg); }
            .brand-ring { --tw-ring-color: var(--brand-primary); }
        </component>

        <!-- Navbar -->
        <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b border-slate-200/50 transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div v-if="company.logo_url" class="w-12 h-12 bg-contain bg-no-repeat bg-center rounded-xl border border-slate-100 shadow-sm" :style="{ backgroundImage: `url(${company.logo_url})` }"></div>
                    <div v-else class="w-12 h-12 rounded-xl brand-bg flex items-center justify-center text-white shadow-lg">
                        <Building2 class="w-6 h-6" />
                    </div>
                    <div>
                        <h1 class="font-black text-xl tracking-tight leading-none">{{ company.name }}</h1>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mt-1 block">{{ company.industry }}</span>
                    </div>
                </div>
                <nav class="hidden lg:flex items-center gap-8" aria-label="Navigation interne">
                    <a href="#services" class="text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors">Services</a>
                    <a v-if="galleries.length > 0" href="#gallery" class="text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors">Réalisations</a>
                    <a v-if="company.served_areas.length > 0" href="#areas" class="text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors">Zones d'intervention</a>
                </nav>
                <div class="flex items-center gap-4">
                    <div class="hidden md:flex items-center gap-2 mr-2">
                        <a v-if="settings.facebook_url" :href="settings.facebook_url" target="_blank" rel="noopener noreferrer" aria-label="Facebook" class="p-2.5 rounded-full bg-slate-50 hover:brand-bg hover:text-white transition-all text-slate-400 shadow-sm">
                            <Facebook class="w-4 h-4" />
                        </a>
                        <a v-if="settings.instagram_url" :href="settings.instagram_url" target="_blank" rel="noopener noreferrer" aria-label="Instagram" class="p-2.5 rounded-full bg-slate-50 hover:brand-bg hover:text-white transition-all text-slate-400 shadow-sm">
                            <Instagram class="w-4 h-4" />
                        </a>
                    </div>
                    <Button class="brand-bg text-white hover:opacity-90 rounded-full font-bold shadow-lg shadow-brand-primary/20 hover:shadow-xl hover:shadow-brand-primary/30 transition-all" as-child>
                        <a href="#contact">Demander un devis</a>
                    </Button>
                </div>
            </div>
        </header>

        <main>
            <!-- Hero Section -->
            <section class="relative pt-24 pb-32 overflow-hidden brand-bg-soft">
                <!-- Decorative background -->
                <div class="absolute inset-0 z-0 pointer-events-none">
                    <div class="absolute top-0 right-0 -translate-y-1/4 translate-x-1/3 w-[800px] h-[800px] brand-bg rounded-full blur-[120px] opacity-[0.05]"></div>
                    <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/4 w-[600px] h-[600px] brand-bg rounded-full blur-[100px] opacity-[0.08]"></div>
                    <!-- Grid Pattern -->
                    <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:24px_24px]"></div>
                </div>

                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-16 items-center relative z-10">
                    <div class="lg:col-span-7 space-y-10 animate-in fade-in slide-in-from-left duration-1000">
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white border border-slate-200/60 shadow-sm">
                            <span class="w-2 h-2 rounded-full brand-bg animate-pulse"></span>
                            <span class="text-xs font-black uppercase tracking-widest text-slate-700">Disponible pour vos projets</span>
                        </div>
                        
                        <h2 class="text-5xl lg:text-7xl font-black tracking-tight text-slate-900 leading-[1.1]">
                            Excellence & Savoir-faire en <span class="brand-text">{{ company.industry || 'services' }}</span>.
                        </h2>
                        
                        <p class="text-xl text-slate-600 leading-relaxed font-medium max-w-2xl">
                            {{ company.description || 'Un professionnel de confiance à votre écoute. Contactez-nous dès aujourd\'hui pour échanger sur vos besoins et obtenir une estimation gratuite.' }}
                        </p>
                        
                        <div class="flex flex-col sm:flex-row gap-4 pt-4">
                            <Button class="brand-bg h-14 px-8 rounded-full text-lg font-bold text-white shadow-xl shadow-brand-primary/20 hover:shadow-brand-primary/40 hover:-translate-y-1 transition-all" as-child>
                                <a href="#contact">
                                    Obtenir un devis gratuit
                                    <ArrowRight class="w-5 h-5 ml-2" />
                                </a>
                            </Button>
                            <Button variant="outline" class="h-14 px-8 rounded-full text-lg font-bold border-slate-200 hover:bg-slate-50 shadow-sm bg-white" as-child>
                                <a href="#services">
                                    Découvrir nos services
                                </a>
                            </Button>
                        </div>

                        <div class="flex flex-wrap items-center gap-8 pt-8 border-t border-slate-200/60">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-white brand-text flex items-center justify-center shadow-md border border-slate-100">
                                    <Phone class="w-5 h-5" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Contact Direct</p>
                                    <p class="font-bold text-slate-900">{{ company.phone || 'Sur demande' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-white brand-text flex items-center justify-center shadow-md border border-slate-100">
                                    <Mail class="w-5 h-5" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Email</p>
                                    <p class="font-bold text-slate-900">{{ company.email || 'Sur demande' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Form Card -->
                    <div class="lg:col-span-5 relative group animate-in fade-in slide-in-from-right duration-1000 delay-150">
                        <!-- Decorative glow behind card -->
                        <div class="absolute -inset-1 brand-bg rounded-[2.5rem] blur-2xl opacity-20 group-hover:opacity-30 transition-opacity duration-500"></div>
                        
                        <div id="contact" class="bg-white/80 backdrop-blur-xl rounded-[2rem] p-8 sm:p-10 shadow-2xl border border-white relative z-10">
                            <div class="mb-8">
                                <h3 class="text-2xl font-black tracking-tight mb-2">Parlez-nous de votre projet</h3>
                                <p class="text-sm text-slate-500 font-medium">Remplissez ce formulaire court, nous vous recontacterons rapidement.</p>
                            </div>
                            
                            <form @submit.prevent="submitLead" class="space-y-5">
                                <div>
                                    <label class="block text-[11px] font-black uppercase tracking-widest text-slate-500 mb-2">Votre nom complet <span class="text-red-500">*</span></label>
                                    <Input v-model="form.name" placeholder="Ex: Jean Dupont" class="h-14 rounded-xl bg-slate-50 border-slate-200 focus:bg-white focus:brand-ring transition-colors" required aria-required="true" />
                                    <span v-if="form.errors.name" class="text-xs text-red-500 mt-1 font-medium">{{ form.errors.name }}</span>
                                </div>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <div>
                                        <label class="block text-[11px] font-black uppercase tracking-widest text-slate-500 mb-2">Téléphone <span class="text-red-500">*</span></label>
                                        <Input v-model="form.phone" type="tel" placeholder="Ex: 06 12 34 56 78" class="h-14 rounded-xl bg-slate-50 border-slate-200 focus:bg-white focus:brand-ring transition-colors" required aria-required="true" />
                                        <span v-if="form.errors.phone" class="text-xs text-red-500 mt-1 font-medium">{{ form.errors.phone }}</span>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-black uppercase tracking-widest text-slate-500 mb-2">Email</label>
                                        <Input v-model="form.email" type="email" placeholder="jean@exemple.com" class="h-14 rounded-xl bg-slate-50 border-slate-200 focus:bg-white focus:brand-ring transition-colors" />
                                        <span v-if="form.errors.email" class="text-xs text-red-500 mt-1 font-medium">{{ form.errors.email }}</span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-[11px] font-black uppercase tracking-widest text-slate-500 mb-2">Description du besoin</label>
                                    <Textarea v-model="form.message" placeholder="Détaillez brièvement votre projet ou votre demande..." class="rounded-xl bg-slate-50 border-slate-200 focus:bg-white focus:brand-ring min-h-[120px] resize-y transition-colors p-4" />
                                    <span v-if="form.errors.message" class="text-xs text-red-500 mt-1 font-medium">{{ form.errors.message }}</span>
                                </div>
                                
                                <Button type="submit" class="w-full brand-bg text-white h-14 rounded-xl text-base font-bold shadow-md hover:shadow-lg transition-all mt-2" :disabled="form.processing">
                                    <span v-if="form.recentlySuccessful">Demande envoyée avec succès !</span>
                                    <span v-else>{{ leadForm?.button_label || 'Envoyer ma demande' }}</span>
                                    <ArrowRight v-if="!form.recentlySuccessful" class="w-5 h-5 ml-2" />
                                </Button>
                                
                                <p class="text-[10px] text-center text-slate-400 font-medium mt-4">Vos données sont sécurisées et ne seront utilisées que pour vous recontacter.</p>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Services Section -->
            <section id="services" class="py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 scroll-mt-20">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 mb-16">
                    <div class="space-y-4 max-w-2xl">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full brand-bg-soft brand-text text-xs font-black uppercase tracking-widest">
                            <Star class="w-3.5 h-3.5" />
                            Notre Expertise
                        </span>
                        <h2 class="text-4xl lg:text-5xl font-black tracking-tight">Services & Prestations</h2>
                        <p class="text-slate-500 text-lg font-medium leading-relaxed">Découvrez l'étendue de nos compétences. Nous proposons des solutions sur-mesure adaptées à vos exigences.</p>
                    </div>
                </div>

                <div v-if="services.length > 0" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    <article v-for="service in services" :key="service.id" class="group bg-white rounded-[2rem] p-8 border border-slate-100 shadow-sm hover:shadow-2xl hover:shadow-slate-200 transition-all duration-500 relative overflow-hidden">
                        <!-- Hover background effect -->
                        <div class="absolute inset-0 brand-bg opacity-0 group-hover:opacity-5 transition-opacity duration-500 pointer-events-none"></div>
                        
                        <div class="w-16 h-16 mb-8 rounded-2xl brand-bg flex items-center justify-center text-white shadow-lg shadow-brand-primary/20 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-500">
                            <div v-if="service.image_url" class="w-full h-full rounded-2xl bg-cover bg-center" :style="{ backgroundImage: `url(${service.image_url})` }"></div>
                            <Briefcase v-else class="w-8 h-8" />
                        </div>
                        <h3 class="text-2xl font-black mb-4 tracking-tight group-hover:brand-text transition-colors">{{ service.name }}</h3>
                        <p class="text-slate-500 leading-relaxed font-medium">{{ service.description || 'Service professionnel réalisé dans les règles de l\'art.' }}</p>
                    </article>
                </div>
                <div v-else class="text-center py-20 bg-slate-50 rounded-[2rem] border-2 border-dashed border-slate-200">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                        <Briefcase class="w-8 h-8 text-slate-300" />
                    </div>
                    <p class="text-slate-500 font-bold text-lg">Services en cours de mise à jour.</p>
                    <p class="text-slate-400 mt-2">N'hésitez pas à nous contacter directement pour en savoir plus.</p>
                </div>
            </section>

            <!-- Gallery Section -->
            <section v-if="galleries.length > 0" id="gallery" class="py-32 bg-slate-900 text-white overflow-hidden scroll-mt-20">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-20 space-y-6">
                        <span class="inline-block px-4 py-1.5 rounded-full bg-white/10 text-white text-xs font-black uppercase tracking-widest border border-white/5">Portfolio</span>
                        <h2 class="text-4xl lg:text-6xl font-black tracking-tight">Nos Réalisations</h2>
                        <p class="text-slate-400 max-w-2xl mx-auto text-lg">Un aperçu de notre savoir-faire à travers nos projets récents. La qualité est notre meilleure signature.</p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-6 auto-rows-[250px] lg:auto-rows-[300px]">
                        <figure v-for="(photo, index) in galleries" :key="photo.id" 
                            class="relative group rounded-[2rem] overflow-hidden bg-slate-800"
                            :class="{ 
                                'md:col-span-2 md:row-span-2': index === 0,
                                'md:col-span-2': index === 3 || index === 6
                            }"
                        >
                            <img :src="photo.image_url" :alt="photo.caption || 'Réalisation de projet'" class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110" loading="lazy" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-60 group-hover:opacity-80 transition-opacity duration-300"></div>
                            
                            <figcaption v-if="photo.caption" class="absolute bottom-0 left-0 w-full p-6 lg:p-8 translate-y-4 group-hover:translate-y-0 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <span class="block text-lg font-black tracking-tight text-white drop-shadow-md">{{ photo.caption }}</span>
                            </figcaption>
                        </figure>
                    </div>
                </div>
            </section>

            <!-- Areas Served Section -->
            <section v-if="company.served_areas.length > 0" id="areas" class="py-32 relative bg-white scroll-mt-20 border-b border-slate-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-20 items-center">
                     <div class="space-y-8 order-2 lg:order-1">
                        <div class="space-y-4">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full brand-bg-soft brand-text text-xs font-black uppercase tracking-widest">
                                <MapPin class="w-3.5 h-3.5" />
                                Zone d'action
                            </span>
                            <h2 class="text-4xl lg:text-5xl font-black tracking-tight">Où intervenons-nous ?</h2>
                            <p class="text-slate-500 text-lg leading-relaxed font-medium">
                                Nous couvrons un large secteur pour répondre à vos besoins. Vérifiez si votre commune fait partie de notre zone de service.
                            </p>
                        </div>

                        <div class="relative max-w-md">
                            <Search class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                            <Input v-model="searchCity" placeholder="Rechercher une ville..." class="pl-14 h-16 rounded-2xl border-slate-200 bg-slate-50 focus:bg-white focus:brand-ring shadow-sm transition-all text-lg" aria-label="Rechercher une ville d'intervention" />
                        </div>

                        <div class="bg-slate-50 rounded-[2rem] p-8 border border-slate-100 max-h-[400px] overflow-y-auto scrollbar-thin">
                            <div class="flex flex-wrap gap-3">
                                <div v-for="city in filteredCities" :key="city" class="px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-sm font-bold text-slate-700 shadow-sm hover:shadow-md hover:brand-border transition-all flex items-center gap-2 cursor-default">
                                    <MapPin class="w-4 h-4 brand-text" />
                                    {{ city }}
                                </div>
                            </div>
                            <div v-if="filteredCities.length === 0" class="text-center py-8">
                                <MapPin class="w-8 h-8 text-slate-300 mx-auto mb-3" />
                                <p class="text-slate-500 font-bold">Aucune ville trouvée.</p>
                                <p class="text-sm text-slate-400 mt-1">Contactez-nous pour vérifier notre disponibilité.</p>
                            </div>
                        </div>
                     </div>

                     <div class="relative order-1 lg:order-2">
                        <!-- Abstract Map Illustration -->
                        <div class="aspect-square bg-slate-50 rounded-[3rem] overflow-hidden relative flex items-center justify-center border border-slate-100 shadow-2xl group">
                            <!-- SVG pattern background for map feel -->
                            <svg class="absolute inset-0 w-full h-full text-slate-200/50" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 100 Q 150 50, 300 150 T 600 100" stroke="currentColor" stroke-width="2" />
                                <path d="M0 200 Q 150 250, 300 150 T 600 200" stroke="currentColor" stroke-width="2" />
                                <path d="M0 300 Q 150 350, 300 250 T 600 300" stroke="currentColor" stroke-width="2" />
                                <path d="M0 400 Q 150 450, 300 350 T 600 400" stroke="currentColor" stroke-width="2" />
                                <circle cx="300" cy="300" r="150" fill="currentColor" opacity="0.5"/>
                                <circle cx="300" cy="300" r="100" fill="currentColor" opacity="0.5"/>
                                <circle cx="300" cy="300" r="50" fill="currentColor" opacity="0.5"/>
                            </svg>

                             <div class="relative z-10 flex flex-col items-center gap-6">
                                <div class="w-20 h-20 brand-bg rounded-2xl rotate-3 flex items-center justify-center text-white shadow-2xl shadow-brand-primary/40 group-hover:rotate-12 transition-transform duration-500">
                                    <MapPin class="w-10 h-10 -rotate-3 group-hover:-rotate-12 transition-transform" />
                                </div>
                                <div class="bg-white px-8 py-4 rounded-2xl shadow-xl border border-slate-100 flex items-center gap-3">
                                    <span class="relative flex h-3 w-3">
                                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full brand-bg opacity-75"></span>
                                      <span class="relative inline-flex rounded-full h-3 w-3 brand-bg"></span>
                                    </span>
                                    <span class="font-black text-sm uppercase tracking-widest text-slate-800">Proche de vous</span>
                                </div>
                             </div>
                        </div>

                        <!-- Floating Address Card -->
                        <div v-if="company.address" class="absolute -bottom-8 -left-8 lg:-left-12 bg-white p-6 rounded-2xl shadow-2xl border border-slate-100 max-w-[280px] z-20 animate-in slide-in-from-bottom-8 duration-1000 delay-300">
                            <div class="flex items-start gap-4">
                                <div class="p-3 rounded-xl brand-bg-soft brand-text mt-1 shrink-0">
                                    <Building2 class="w-5 h-5" />
                                </div>
                                <div>
                                    <h4 class="text-[10px] font-black mb-1 uppercase tracking-widest text-slate-400">Siège Social</h4>
                                    <address class="font-bold text-sm leading-snug not-italic text-slate-800">{{ company.address }}</address>
                                </div>
                            </div>
                        </div>
                     </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="bg-slate-950 pt-24 pb-12 px-4 sm:px-6 lg:px-8 text-slate-300">
            <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-12 gap-12 lg:gap-8 mb-16">
                <div class="md:col-span-5 space-y-6">
                    <div class="flex items-center gap-3">
                         <div v-if="company.logo_url" class="w-10 h-10 bg-contain bg-no-repeat bg-center bg-white rounded-lg p-1" :style="{ backgroundImage: `url(${company.logo_url})` }"></div>
                         <Building2 v-else class="w-10 h-10 text-white" />
                         <span class="font-black text-2xl tracking-tight text-white">{{ company.name }}</span>
                    </div>
                    <p class="text-slate-400 max-w-sm leading-relaxed">{{ company.description || 'Votre partenaire de confiance pour des résultats professionnels et durables.' }}</p>
                    
                    <div class="flex items-center gap-4 pt-2">
                        <a v-if="company.website" :href="company.website" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center hover:bg-white hover:text-slate-900 transition-colors" aria-label="Site Web">
                            <Globe class="w-4 h-4" />
                        </a>
                        <a v-if="settings.facebook_url" :href="settings.facebook_url" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center hover:bg-[#1877F2] hover:text-white transition-colors" aria-label="Facebook">
                            <Facebook class="w-4 h-4" />
                        </a>
                        <a v-if="settings.instagram_url" :href="settings.instagram_url" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center hover:bg-[#E4405F] hover:text-white transition-colors" aria-label="Instagram">
                            <Instagram class="w-4 h-4" />
                        </a>
                    </div>
                </div>
                
                <div class="md:col-span-3 space-y-6">
                    <h5 class="text-xs font-black uppercase tracking-widest text-slate-500">Liens Utiles</h5>
                    <ul class="space-y-4">
                        <li><a href="#services" class="font-medium hover:text-white transition-colors">Nos Services</a></li>
                        <li v-if="galleries.length > 0"><a href="#gallery" class="font-medium hover:text-white transition-colors">Nos Réalisations</a></li>
                        <li v-if="company.served_areas.length > 0"><a href="#areas" class="font-medium hover:text-white transition-colors">Zones Desservies</a></li>
                        <li><a href="#contact" class="font-medium hover:text-white transition-colors">Demander un Devis</a></li>
                    </ul>
                </div>

                <div class="md:col-span-4 space-y-6">
                    <h5 class="text-xs font-black uppercase tracking-widest text-slate-500">Contact</h5>
                    <ul class="space-y-5">
                        <li v-if="company.phone" class="flex items-start gap-4">
                            <Phone class="w-5 h-5 brand-text shrink-0 mt-0.5" />
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-1">Téléphone</span>
                                <a :href="`tel:${company.phone}`" class="font-bold text-white hover:brand-text transition-colors">{{ company.phone }}</a>
                            </div>
                        </li>
                        <li v-if="company.email" class="flex items-start gap-4">
                            <Mail class="w-5 h-5 brand-text shrink-0 mt-0.5" />
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-1">Email</span>
                                <a :href="`mailto:${company.email}`" class="font-bold text-white hover:brand-text transition-colors">{{ company.email }}</a>
                            </div>
                        </li>
                        <li v-if="company.address" class="flex items-start gap-4">
                            <MapPin class="w-5 h-5 brand-text shrink-0 mt-0.5" />
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-1">Adresse</span>
                                <address class="font-bold text-white not-italic">{{ company.address }}</address>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="max-w-7xl mx-auto pt-8 border-t border-slate-800 flex flex-col md:flex-row justify-between items-center gap-6">
                <p class="text-sm font-medium text-slate-500">
                    © {{ new Date().getFullYear() }} {{ company.name }}. Propulsé par <a href="/" class="font-bold text-slate-300 hover:text-white">LeadFlow</a>.
                </p>
                <div class="flex gap-6">
                    <a href="#" class="text-[10px] font-bold text-slate-500 uppercase tracking-widest hover:text-slate-300 transition-colors">Mentions Légales</a>
                    <a href="#" class="text-[10px] font-bold text-slate-500 uppercase tracking-widest hover:text-slate-300 transition-colors">Confidentialité</a>
                </div>
            </div>
        </footer>
    </div>
</template>
