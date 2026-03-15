<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { dashboard, login, register } from '@/routes';
import { useI18n } from '@/composables/useI18n';
import { ArrowRight, BarChart3, Users, Zap, CheckCircle2, LayoutTemplate, Briefcase, Plus, Minus, ChevronDown, Receipt, ShieldCheck, Hammer, Heart, Building2, Home, Menu, X } from 'lucide-vue-next';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const { t } = useI18n();

const faqs = computed(() => [
    { question: t('landing.faq1_q'), answer: t('landing.faq1_a') },
    { question: t('landing.faq2_q'), answer: t('landing.faq2_a') },
    { question: t('landing.faq3_q'), answer: t('landing.faq3_a') },
    { question: t('landing.faq4_q'), answer: t('landing.faq4_a') },
    { question: t('landing.faq5_q'), answer: t('landing.faq5_a') },
]);

const openFaq = ref<number | null>(0);
const mobileMenuOpen = ref(false);

const toggleFaq = (index: number) => {
    openFaq.value = openFaq.value === index ? null : index;
};
</script>

<template>
    <Head :title="t('landing.meta_title')">
        <meta name="description" :content="t('landing.meta_description')" />
        <meta name="keywords" content="CRM, devis en ligne, facturation, gestion clients, leads, prospects, SaaS, PME, entreprise, formulaire, widget" />
        <meta name="author" content="clientux" />
        <meta name="robots" content="index, follow" />
        
        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website" />
        <meta property="og:title" :content="t('landing.meta_og_title')" />
        <meta property="og:description" :content="t('landing.meta_og_description')" />
        <meta property="og:site_name" content="clientux" />
        
        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="t('landing.meta_og_title')" />
        <meta name="twitter:description" :content="t('landing.meta_og_description')" />
    </Head>
    
    <div class="min-h-screen bg-slate-50 font-sans text-slate-900 selection:bg-indigo-500 selection:text-white dark:bg-slate-950 dark:text-white">
        <!-- Navigation -->
        <nav class="absolute inset-x-0 top-0 z-50">
            <div class="mx-auto flex max-w-7xl items-center justify-between p-4 sm:p-6 lg:px-8" aria-label="Global">
                <div class="flex lg:flex-1">
                    <div class="flex items-center gap-2">
                        <div class="flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-lg">
                            <Zap class="h-5 w-5 sm:h-6 sm:w-6" />
                        </div>
                        <span class="text-lg sm:text-xl font-bold tracking-tight text-slate-900 dark:text-white">clientux</span>
                    </div>
                </div>
                
                <!-- Mobile menu button -->
                <div class="flex sm:hidden">
                    <button
                        type="button"
                        class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-slate-700 dark:text-slate-300"
                        @click="mobileMenuOpen = true"
                        aria-label="Ouvrir le menu"
                    >
                        <Menu class="h-6 w-6" />
                    </button>
                </div>

                <!-- Desktop nav buttons -->
                <div class="hidden sm:flex flex-1 justify-end gap-x-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="rounded-full bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-700 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200"
                    >
                        {{ t('landing.go_to_dashboard') }}
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="rounded-full px-5 py-2.5 text-sm font-semibold leading-6 text-slate-900 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            {{ t('landing.login') }}
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        >
                            {{ t('landing.register') }}
                        </Link>
                    </template>
                </div>
            </div>

            <!-- Mobile menu panel -->
            <transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="mobileMenuOpen" class="fixed inset-0 z-50 sm:hidden">
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="mobileMenuOpen = false" />
                    
                    <!-- Panel -->
                    <div class="fixed inset-y-0 right-0 w-full max-w-sm bg-white dark:bg-slate-900 shadow-2xl px-6 py-6 overflow-y-auto">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-2">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-lg">
                                    <Zap class="h-5 w-5" />
                                </div>
                                <span class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">clientux</span>
                            </div>
                            <button
                                type="button"
                                class="-m-2.5 rounded-md p-2.5 text-slate-700 dark:text-slate-300"
                                @click="mobileMenuOpen = false"
                                aria-label="Fermer le menu"
                            >
                                <X class="h-6 w-6" />
                            </button>
                        </div>
                        
                        <div class="flow-root">
                            <div class="space-y-3">
                                <a href="#features" @click="mobileMenuOpen = false" class="block rounded-xl px-4 py-3 text-base font-semibold text-slate-900 hover:bg-slate-50 dark:text-white dark:hover:bg-slate-800 transition-colors">
                                    {{ t('landing.features') }}
                                </a>
                                <a href="#faq" @click="mobileMenuOpen = false" class="block rounded-xl px-4 py-3 text-base font-semibold text-slate-900 hover:bg-slate-50 dark:text-white dark:hover:bg-slate-800 transition-colors">
                                    {{ t('landing.faq') }}
                                </a>
                            </div>
                            
                            <div class="mt-8 pt-6 border-t border-slate-200 dark:border-slate-700 space-y-3">
                                <Link
                                    v-if="$page.props.auth.user"
                                    :href="dashboard()"
                                    class="block w-full rounded-xl bg-slate-900 px-4 py-3 text-center text-base font-semibold text-white shadow-sm hover:bg-slate-700 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200 transition-colors"
                                >
                                    {{ t('landing.go_to_dashboard') }}
                                </Link>
                                <template v-else>
                                    <Link
                                        :href="login()"
                                        class="block w-full rounded-xl px-4 py-3 text-center text-base font-semibold text-slate-900 hover:bg-slate-50 dark:text-white dark:hover:bg-slate-800 transition-colors"
                                    >
                                        {{ t('landing.login') }}
                                    </Link>
                                    <Link
                                        v-if="canRegister"
                                        :href="register()"
                                        class="block w-full rounded-xl bg-indigo-600 px-4 py-3 text-center text-base font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors"
                                    >
                                        {{ t('landing.register') }}
                                    </Link>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </nav>

        <!-- Hero Section -->
        <div class="relative isolate pt-14">
            <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
                <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" />
            </div>
            
            <div class="py-24 sm:py-32 lg:pb-40">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <div class="mb-8 flex justify-center">
                            <span class="relative rounded-full px-3 py-1 text-sm leading-6 text-slate-600 ring-1 ring-slate-900/10 hover:ring-slate-900/20 dark:text-slate-300 dark:ring-white/10 dark:hover:ring-white/20">
                                {{ t('landing.badge') }} <a href="#features" class="font-semibold text-indigo-600 dark:text-indigo-400"><span class="absolute inset-0" aria-hidden="true" />{{ t('landing.badge_cta') }} <span aria-hidden="true">&rarr;</span></a>
                            </span>
                        </div>
                        <h1 class="text-4xl font-bold tracking-tight text-slate-900 sm:text-6xl dark:text-white">
                            {{ t('landing.hero_title') }}
                        </h1>
                        <p class="mt-6 text-lg leading-8 text-slate-600 dark:text-slate-300">
                            {{ t('landing.hero_desc') }}
                        </p>
                        <div class="mt-10 flex items-center justify-center gap-x-6">
                            <Link
                                v-if="!$page.props.auth.user"
                                :href="register()"
                                class="rounded-full bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-transform hover:-translate-y-1"
                            >
                                {{ t('landing.hero_cta') }}
                            </Link>
                            <Link v-else :href="dashboard()" class="rounded-full bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-transform hover:-translate-y-1">
                                {{ t('landing.hero_cta_dashboard') }}
                            </Link>
                            <a href="#features" class="text-sm font-semibold leading-6 text-slate-900 dark:text-white flex items-center gap-1 group">
                                {{ t('landing.hero_learn_more') }} <ArrowRight class="h-4 w-4 group-hover:translate-x-1 transition-transform" />
                            </a>
                        </div>
                    </div>
                    
                    <div class="mt-16 flow-root sm:mt-24">
                        <div class="-m-2 rounded-xl bg-slate-900/5 p-2 ring-1 ring-inset ring-slate-900/10 lg:-m-4 lg:rounded-2xl lg:p-4 dark:bg-white/5 dark:ring-white/10">
                            <div class="relative rounded-lg bg-white overflow-hidden shadow-2xl ring-1 ring-slate-900/10 dark:bg-slate-900 dark:ring-white/10">
                                <!-- Mockup Header -->
                                <div class="border-b border-slate-200 bg-slate-50/50 px-4 py-3 sm:px-6 dark:border-slate-800 dark:bg-slate-900/50 flex space-x-2">
                                    <div class="w-3 h-3 rounded-full bg-red-500/80"></div>
                                    <div class="w-3 h-3 rounded-full bg-amber-500/80"></div>
                                    <div class="w-3 h-3 rounded-full bg-emerald-500/80"></div>
                                </div>
                                
                                <!-- Mockup Content -->
                                <div class="grid grid-cols-1 md:grid-cols-4 min-h-[400px]">
                                    <!-- Sidebar Mock -->
                                    <div class="hidden md:flex flex-col gap-4 border-r border-slate-200 bg-slate-50/20 p-4 dark:border-slate-800 dark:bg-slate-900/20">
                                        <div class="h-8 w-full rounded bg-slate-200 dark:bg-slate-800 animate-pulse"></div>
                                        <div class="h-8 w-3/4 rounded bg-slate-200 dark:bg-slate-800 animate-pulse delay-75"></div>
                                        <div class="h-8 w-5/6 rounded bg-slate-200 dark:bg-slate-800 animate-pulse delay-100"></div>
                                        <div class="h-8 w-full rounded bg-indigo-100 dark:bg-indigo-900/30"></div>
                                    </div>
                                    <!-- Kanban Mock -->
                                    <div class="col-span-3 p-6 bg-slate-50/50 dark:bg-slate-900/50">
                                        <div class="flex gap-4 overflow-hidden h-full">
                                            <!-- Column 1 -->
                                            <div class="flex-1 shrink-0 w-64 bg-slate-100/80 dark:bg-slate-800/50 rounded-lg p-3 flex flex-col gap-3 border border-slate-200 dark:border-slate-700/50">
                                                <div class="font-medium text-sm px-1 text-slate-700 dark:text-slate-300">{{ t('landing.mockup_col1') }}</div>
                                                <div class="bg-white dark:bg-slate-800 p-3 rounded-md shadow-sm border border-slate-200 dark:border-slate-700">
                                                    <div class="h-4 w-1/2 bg-slate-200 dark:bg-slate-700 rounded mb-2"></div>
                                                    <div class="h-3 w-1/3 bg-slate-100 dark:bg-slate-600 rounded"></div>
                                                </div>
                                                <div class="bg-white dark:bg-slate-800 p-3 rounded-md shadow-sm border border-slate-200 dark:border-slate-700">
                                                    <div class="h-4 w-2/3 bg-slate-200 dark:bg-slate-700 rounded mb-2"></div>
                                                    <div class="h-3 w-1/4 bg-slate-100 dark:bg-slate-600 rounded"></div>
                                                </div>
                                            </div>
                                            <!-- Column 2 -->
                                            <div class="flex-1 shrink-0 w-64 bg-slate-100/80 dark:bg-slate-800/50 rounded-lg p-3 flex flex-col gap-3 border border-slate-200 dark:border-slate-700/50">
                                                <div class="font-medium text-sm px-1 text-slate-700 dark:text-slate-300">{{ t('landing.mockup_col2') }}</div>
                                                <div class="bg-white dark:bg-slate-800 p-3 rounded-md shadow-sm border border-slate-200 dark:border-slate-700 border-l-2 border-l-amber-500">
                                                    <div class="h-4 w-3/4 bg-slate-200 dark:bg-slate-700 rounded mb-2"></div>
                                                    <div class="h-3 w-1/2 bg-slate-100 dark:bg-slate-600 rounded"></div>
                                                </div>
                                            </div>
                                            <!-- Column 3 -->
                                            <div class="flex-1 shrink-0 w-64 bg-slate-100/80 dark:bg-slate-800/50 rounded-lg p-3 flex flex-col gap-3 border border-slate-200 dark:border-slate-700/50">
                                                <div class="font-medium text-sm px-1 text-slate-700 dark:text-slate-300">{{ t('landing.mockup_col3') }}</div>
                                                <div class="bg-white dark:bg-slate-800 p-3 rounded-md shadow-sm border border-slate-200 dark:border-slate-700 border-l-2 border-l-emerald-500">
                                                    <div class="h-4 w-1/2 bg-slate-200 dark:bg-slate-700 rounded mb-2"></div>
                                                    <div class="h-3 w-1/4 bg-slate-100 dark:bg-slate-600 rounded mb-3"></div>
                                                    <div class="flex justify-between items-center">
                                                        <div class="h-5 w-16 bg-emerald-100 dark:bg-emerald-900/30 rounded-full"></div>
                                                        <div class="h-5 w-5 bg-slate-100 dark:bg-slate-700 rounded-full"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]" aria-hidden="true">
                <div class="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%+36rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" />
            </div>
        </div>

        <!-- Features Section -->
        <div id="features" class="py-24 sm:py-32 bg-white dark:bg-slate-900/50">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl lg:text-center">
                    <h2 class="text-base font-semibold leading-7 text-indigo-600 dark:text-indigo-400">{{ t('landing.features_badge') }}</h2>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                        {{ t('landing.features_title') }}
                    </p>
                    <p class="mt-6 text-lg leading-8 text-slate-600 dark:text-slate-300">
                        {{ t('landing.features_desc') }}
                    </p>
                </div>
                
                <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-none">
                    <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-12 lg:max-w-none lg:grid-cols-4">
                        <div class="flex flex-col">
                            <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-slate-900 dark:text-white">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600">
                                    <LayoutTemplate class="h-6 w-6 text-white" aria-hidden="true" />
                                </div>
                                {{ t('landing.feature_widget_title') }}
                            </dt>
                            <dd class="mt-4 flex flex-auto flex-col text-sm leading-7 text-slate-600 dark:text-slate-400">
                                <p class="flex-auto">{{ t('landing.feature_widget_desc') }}</p>
                            </dd>
                        </div>
                        <div class="flex flex-col">
                            <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-slate-900 dark:text-white">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600">
                                    <Users class="h-6 w-6 text-white" aria-hidden="true" />
                                </div>
                                {{ t('landing.feature_crm_title') }}
                            </dt>
                            <dd class="mt-4 flex flex-auto flex-col text-sm leading-7 text-slate-600 dark:text-slate-400">
                                <p class="flex-auto">{{ t('landing.feature_crm_desc') }}</p>
                            </dd>
                        </div>
                        <div class="flex flex-col">
                            <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-slate-900 dark:text-white">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600">
                                    <Briefcase class="h-6 w-6 text-white" aria-hidden="true" />
                                </div>
                                {{ t('landing.feature_quotes_title') }}
                            </dt>
                            <dd class="mt-4 flex flex-auto flex-col text-sm leading-7 text-slate-600 dark:text-slate-400">
                                <p class="flex-auto">{{ t('landing.feature_quotes_desc') }}</p>
                            </dd>
                        </div>
                        <div class="flex flex-col">
                            <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-slate-900 dark:text-white">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600">
                                    <Receipt class="h-6 w-6 text-white" aria-hidden="true" />
                                </div>
                                {{ t('landing.feature_invoices_title') }}
                            </dt>
                            <dd class="mt-4 flex flex-auto flex-col text-sm leading-7 text-slate-600 dark:text-slate-400">
                                <p class="flex-auto">{{ t('landing.feature_invoices_desc') }}</p>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Target Audience Section -->
        <div class="py-24 sm:py-32 relative overflow-hidden bg-slate-50 dark:bg-slate-950">
            <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 brightness-100 contrast-150 mix-blend-overlay"></div>
            <div class="mx-auto max-w-7xl px-6 lg:px-8 relative">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-base font-semibold leading-7 text-indigo-600 dark:text-indigo-400">{{ t('landing.audience_badge') }}</h2>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white">{{ t('landing.audience_title') }}</p>
                    <p class="mt-6 text-lg leading-8 text-slate-600 dark:text-slate-300">{{ t('landing.audience_desc') }}</p>
                </div>
                
                <div class="mx-auto mt-16 max-w-7xl">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                        <!-- Card 1 -->
                        <div class="group relative bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 hover:shadow-xl hover:border-indigo-500/30 transition-all duration-300 hover:-translate-y-2 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-br from-indigo-50/50 to-transparent dark:from-indigo-500/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <div class="relative">
                                <div class="w-14 h-14 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                    <Hammer class="w-7 h-7" />
                                </div>
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">{{ t('landing.audience_construction_title') }}</h3>
                                <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">{{ t('landing.audience_construction_desc') }}</p>
                            </div>
                        </div>

                        <!-- Card 2 -->
                        <div class="group relative bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 hover:shadow-xl hover:border-rose-500/30 transition-all duration-300 hover:-translate-y-2 overflow-hidden lg:translate-y-8">
                            <div class="absolute inset-0 bg-gradient-to-br from-rose-50/50 to-transparent dark:from-rose-500/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <div class="relative">
                                <div class="w-14 h-14 bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                    <Heart class="w-7 h-7" />
                                </div>
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">{{ t('landing.audience_beauty_title') }}</h3>
                                <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">{{ t('landing.audience_beauty_desc') }}</p>
                            </div>
                        </div>

                        <!-- Card 3 -->
                        <div class="group relative bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 hover:shadow-xl hover:border-emerald-500/30 transition-all duration-300 hover:-translate-y-2 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-br from-emerald-50/50 to-transparent dark:from-emerald-500/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <div class="relative">
                                <div class="w-14 h-14 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                    <Building2 class="w-7 h-7" />
                                </div>
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">{{ t('landing.audience_b2b_title') }}</h3>
                                <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">{{ t('landing.audience_b2b_desc') }}</p>
                            </div>
                        </div>

                        <!-- Card 4 -->
                        <div class="group relative bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 hover:shadow-xl hover:border-amber-500/30 transition-all duration-300 hover:-translate-y-2 overflow-hidden lg:translate-y-8">
                            <div class="absolute inset-0 bg-gradient-to-br from-amber-50/50 to-transparent dark:from-amber-500/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <div class="relative">
                                <div class="w-14 h-14 bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                    <Home class="w-7 h-7" />
                                </div>
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">{{ t('landing.audience_home_title') }}</h3>
                                <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">{{ t('landing.audience_home_desc') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div id="faq" class="bg-white py-24 sm:py-32 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800/50">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-4xl divide-y divide-slate-900/10 dark:divide-white/10">
                    <h2 class="text-2xl font-bold leading-10 tracking-tight text-slate-900 dark:text-white mb-8">
                        {{ t('landing.faq_title') }}
                    </h2>
                    <dl class="mt-10 space-y-6 divide-y divide-slate-900/10 dark:divide-white/10">
                        <div v-for="(faq, index) in faqs" :key="index" class="pt-6">
                            <dt>
                                <button
                                    type="button"
                                    class="flex w-full items-start justify-between text-left text-slate-900 dark:text-white"
                                    @click="toggleFaq(index)"
                                >
                                    <span class="text-base font-semibold leading-7">{{ faq.question }}</span>
                                    <span class="ml-6 flex h-7 items-center">
                                        <Plus v-if="openFaq !== index" class="h-5 w-5 text-indigo-600" aria-hidden="true" />
                                        <Minus v-else class="h-5 w-5 text-indigo-600" aria-hidden="true" />
                                    </span>
                                </button>
                            </dt>
                            <transition
                                enter-active-class="transition duration-100 ease-out"
                                enter-from-class="transform scale-95 opacity-0"
                                enter-to-class="transform scale-100 opacity-100"
                                leave-active-class="transition duration-75 ease-out"
                                leave-from-class="transform scale-100 opacity-100"
                                leave-to-class="transform scale-95 opacity-0"
                            >
                                <dd v-if="openFaq === index" class="mt-4 pr-12">
                                    <p class="text-base leading-7 text-slate-600 dark:text-slate-400">
                                        {{ faq.answer }}
                                    </p>
                                </dd>
                            </transition>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
        
        <!-- CTA Section -->
        <div class="bg-indigo-100 dark:bg-indigo-900/20 py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                            {{ t('landing.cta_title1') }}
                            <br />
                            <span class="text-indigo-600 dark:text-indigo-400">{{ t('landing.cta_title2') }}</span>
                        </h2>
                        <ul class="mt-6 flex flex-col gap-2">
                            <li class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                                <CheckCircle2 class="h-5 w-5 text-emerald-500" /> {{ t('landing.cta_trial') }}
                            </li>
                            <li class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                                <CheckCircle2 class="h-5 w-5 text-emerald-500" /> {{ t('landing.cta_no_card') }}
                            </li>
                            <li class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                                <CheckCircle2 class="h-5 w-5 text-emerald-500" /> {{ t('landing.cta_support') }}
                            </li>
                        </ul>
                    </div>
                    <div class="mt-10 flex items-center gap-x-6 lg:mt-0 lg:flex-shrink-0">
                        <Link
                            :href="register()"
                            class="rounded-full bg-indigo-600 px-8 py-4 text-base font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-transform hover:-translate-y-1"
                        >
                            {{ t('landing.cta_button') }}
                        </Link>
                        <a href="#" class="text-base font-semibold leading-6 text-slate-900 dark:text-white">
                            {{ t('landing.cta_pricing') }} <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-white py-12 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800">
            <div class="mx-auto max-w-7xl px-6 lg:px-8 text-center sm:text-left flex flex-col sm:flex-row justify-between items-center">
                <div class="flex items-center gap-2 mb-4 sm:mb-0">
                    <Zap class="h-5 w-5 text-indigo-600" />
                    <span class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">clientux</span>
                </div>
                <p class="text-sm leading-5 text-slate-500 dark:text-slate-400">
                    &copy; {{ new Date().getFullYear() }} clientux Inc. {{ t('landing.footer_rights') }}
                </p>
                <div class="flex gap-4 mt-4 sm:mt-0 text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white cursor-pointer">
                    {{ t('landing.footer_terms') }}
                </div>
            </div>
        </footer>
    </div>
</template>
