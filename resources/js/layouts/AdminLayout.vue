<script setup lang="ts">
import { router, usePage, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    LayoutDashboard, Building2, Users, ArrowLeft, LogOut, Eye
} from 'lucide-vue-next';

const page = usePage();
const auth = computed(() => (page.props.auth as any));
const ghostMode = computed(() => (page.props.ghostMode as any));

const navItems = [
    { label: 'Tableau de bord', href: '/admin', icon: LayoutDashboard, active: (h: string) => h === '/admin' },
    { label: 'Entreprises', href: '/admin/companies', icon: Building2, active: (h: string) => h.startsWith('/admin/companies') },
    { label: 'Utilisateurs', href: '/admin/users', icon: Users, active: (h: string) => h.startsWith('/admin/users') },
];

const currentPath = computed(() => page.url);

const stopImpersonating = () => {
    router.post('/admin/stop-impersonating');
};
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-white flex">

        <!-- Ghost Mode Banner -->
        <div v-if="ghostMode?.active"
            class="fixed top-0 left-0 right-0 z-50 bg-amber-500 text-black px-4 py-2 flex items-center justify-between shadow-lg text-sm font-semibold">
            <div class="flex items-center gap-2">
                <Eye class="w-4 h-4" />
                <span>Mode Ghost actif — vous naviguez en tant que
                    <strong>{{ auth.user?.name }}</strong></span>
            </div>
            <button @click="stopImpersonating"
                class="bg-black text-amber-400 px-3 py-1 rounded-md text-xs font-bold hover:bg-slate-900 transition-colors">
                Quitter le mode Ghost
            </button>
        </div>

        <!-- Sidebar -->
        <aside class="w-60 shrink-0 bg-slate-900 border-r border-slate-800 flex flex-col"
               :class="ghostMode?.active ? 'pt-10' : ''">

            <!-- Logo / Header -->
            <div class="px-6 py-5 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <div class="h-7 w-7 rounded-md bg-red-600 flex items-center justify-center">
                        <span class="text-white text-xs font-black">S</span>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-white leading-none">LeadFlow</p>
                        <p class="text-[10px] text-red-400 font-semibold uppercase tracking-widest">Super Admin</p>
                    </div>
                </div>
            </div>

            <!-- Nav -->
            <nav class="flex-1 py-4 px-3 space-y-1">
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                    :class="item.active(currentPath)
                        ? 'bg-red-600/20 text-red-400'
                        : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                >
                    <component :is="item.icon" class="w-4 h-4" />
                    {{ item.label }}
                </Link>
            </nav>

            <!-- Footer -->
            <div class="border-t border-slate-800 p-4 space-y-2">
                <Link href="/dashboard"
                    class="flex items-center gap-2 text-xs text-slate-500 hover:text-slate-300 transition-colors">
                    <ArrowLeft class="w-3.5 h-3.5" />
                    Retour à l'app
                </Link>
                <div class="text-xs text-slate-600 truncate">{{ auth.user?.name }}</div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-auto" :class="ghostMode?.active ? 'pt-10' : ''">
            <slot />
        </main>

    </div>
</template>
