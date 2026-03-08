<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';
import { ArrowRight, BarChart3, Users, Zap, CheckCircle2, LayoutTemplate, Briefcase } from 'lucide-vue-next';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head title="Leadflow - Générez et gérez vos leads plus intelligemment" />
    
    <div class="min-h-screen bg-slate-50 font-sans text-slate-900 selection:bg-indigo-500 selection:text-white dark:bg-slate-950 dark:text-white">
        <!-- Navigation -->
        <nav class="absolute inset-x-0 top-0 z-50">
            <div class="mx-auto flex max-w-7xl items-center justify-between p-6 lg:px-8" aria-label="Global">
                <div class="flex lg:flex-1">
                    <div class="flex items-center gap-2">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-lg">
                            <Zap class="h-6 w-6" />
                        </div>
                        <span class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Leadflow</span>
                    </div>
                </div>
                
                <div class="flex flex-1 justify-end gap-x-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="rounded-full bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-700 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200"
                    >
                        Accéder au Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="rounded-full px-5 py-2.5 text-sm font-semibold leading-6 text-slate-900 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            Connexion
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        >
                            Démarrer gratuitement
                        </Link>
                    </template>
                </div>
            </div>
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
                                Nouveau: Interface CRM repensée <a href="#" class="font-semibold text-indigo-600 dark:text-indigo-400"><span class="absolute inset-0" aria-hidden="true" />Voir la démo <span aria-hidden="true">&rarr;</span></a>
                            </span>
                        </div>
                        <h1 class="text-4xl font-bold tracking-tight text-slate-900 sm:text-6xl dark:text-white">
                            Transformez votre site en machine à générer des leads
                        </h1>
                        <p class="mt-6 text-lg leading-8 text-slate-600 dark:text-slate-300">
                            Capturez des prospects avec un widget intelligent, gérez-les dans un CRM intuitif, envoyez des devis et facturez vos clients. Tout ça, au même endroit.
                        </p>
                        <div class="mt-10 flex items-center justify-center gap-x-6">
                            <Link
                                v-if="!$page.props.auth.user"
                                :href="register()"
                                class="rounded-full bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-transform hover:-translate-y-1"
                            >
                                Créer mon compte gratuit
                            </Link>
                            <Link v-else :href="dashboard()" class="rounded-full bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-transform hover:-translate-y-1">
                                App Dashboard
                            </Link>
                            <a href="#features" class="text-sm font-semibold leading-6 text-slate-900 dark:text-white flex items-center gap-1 group">
                                En savoir plus <ArrowRight class="h-4 w-4 group-hover:translate-x-1 transition-transform" />
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
                                                <div class="font-medium text-sm px-1 text-slate-700 dark:text-slate-300">Nouveaux leads</div>
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
                                                <div class="font-medium text-sm px-1 text-slate-700 dark:text-slate-300">En cours</div>
                                                <div class="bg-white dark:bg-slate-800 p-3 rounded-md shadow-sm border border-slate-200 dark:border-slate-700 border-l-2 border-l-amber-500">
                                                    <div class="h-4 w-3/4 bg-slate-200 dark:bg-slate-700 rounded mb-2"></div>
                                                    <div class="h-3 w-1/2 bg-slate-100 dark:bg-slate-600 rounded"></div>
                                                </div>
                                            </div>
                                            <!-- Column 3 -->
                                            <div class="flex-1 shrink-0 w-64 bg-slate-100/80 dark:bg-slate-800/50 rounded-lg p-3 flex flex-col gap-3 border border-slate-200 dark:border-slate-700/50">
                                                <div class="font-medium text-sm px-1 text-slate-700 dark:text-slate-300">Converti</div>
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
                    <h2 class="text-base font-semibold leading-7 text-indigo-600 dark:text-indigo-400">Tout-en-un</h2>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                        L'outil complet pour les entreprises locales
                    </p>
                    <p class="mt-6 text-lg leading-8 text-slate-600 dark:text-slate-300">
                        Leadflow réunit tous les outils dont vous avez besoin pour capturer des prospects, les convertir en clients, et gérer la facturation.
                    </p>
                </div>
                
                <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-none">
                    <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-16 lg:max-w-none lg:grid-cols-3">
                        <div class="flex flex-col">
                            <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-slate-900 dark:text-white">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600">
                                    <LayoutTemplate class="h-6 w-6 text-white" aria-hidden="true" />
                                </div>
                                Widget LeadGen Personnalisé
                            </dt>
                            <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-slate-600 dark:text-slate-400">
                                <p class="flex-auto">Créez des formulaires sur-mesure pour votre site web. Un bout de code à copier, et c'est en ligne.</p>
                            </dd>
                        </div>
                        <div class="flex flex-col">
                            <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-slate-900 dark:text-white">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600">
                                    <Users class="h-6 w-6 text-white" aria-hidden="true" />
                                </div>
                                Mini CRM Intégré
                            </dt>
                            <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-slate-600 dark:text-slate-400">
                                <p class="flex-auto">Oubliez les emails perdus et les post-its. Gérez vos opportunités à travers un processus visuel (Kanban) simple et ordonné.</p>
                            </dd>
                        </div>
                        <div class="flex flex-col">
                            <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-slate-900 dark:text-white">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600">
                                    <Briefcase class="h-6 w-6 text-white" aria-hidden="true" />
                                </div>
                                Devis & Facturation
                            </dt>
                            <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-slate-600 dark:text-slate-400">
                                <p class="flex-auto">Envoyez des soumissions professionnelles en un clic, directement depuis la fiche de votre lead. Convertissez-les en factures sans perte de temps.</p>
                            </dd>
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
                            Prêt à optimiser vos ventes?
                            <br />
                            <span class="text-indigo-600 dark:text-indigo-400">Commencez dès aujourd'hui.</span>
                        </h2>
                        <ul class="mt-6 flex flex-col gap-2">
                            <li class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                                <CheckCircle2 class="h-5 w-5 text-emerald-500" /> Essai de 14 jours gratuits
                            </li>
                            <li class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                                <CheckCircle2 class="h-5 w-5 text-emerald-500" /> Pas de carte de crédit requise
                            </li>
                            <li class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                                <CheckCircle2 class="h-5 w-5 text-emerald-500" /> Support client réactif
                            </li>
                        </ul>
                    </div>
                    <div class="mt-10 flex items-center gap-x-6 lg:mt-0 lg:flex-shrink-0">
                        <Link
                            :href="register()"
                            class="rounded-full bg-indigo-600 px-8 py-4 text-base font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-transform hover:-translate-y-1"
                        >
                            Créer mon entreprise
                        </Link>
                        <a href="#" class="text-base font-semibold leading-6 text-slate-900 dark:text-white">
                            Voir les prix <span aria-hidden="true">→</span>
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
                    <span class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">Leadflow</span>
                </div>
                <p class="text-sm leading-5 text-slate-500 dark:text-slate-400">
                    &copy; {{ new Date().getFullYear() }} Leadflow Inc. Tous droits réservés.
                </p>
                <div class="flex gap-4 mt-4 sm:mt-0 text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white cursor-pointer">
                    Termes et conditions
                </div>
            </div>
        </footer>
    </div>
</template>
