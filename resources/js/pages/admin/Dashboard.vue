<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

type Stats = {
    total_companies: number;
    active_companies: number;
    total_users: number;
    active_users: number;
    total_leads: number;
    leads_this_month: number;
    revenue_this_month: string;
    revenue_total: string;
};

const props = defineProps<{
    stats: Stats;
    recentCompanies: Array<any>;
}>();

const statCards = [
    { label: 'Entreprises actives', value: () => `${props.stats.active_companies} / ${props.stats.total_companies}`, color: 'text-white' },
    { label: 'Utilisateurs actifs', value: () => `${props.stats.active_users} / ${props.stats.total_users}`, color: 'text-white' },
    { label: 'Leads ce mois', value: () => props.stats.leads_this_month, color: 'text-red-400' },
    { label: 'Revenus ce mois', value: () => `$${props.stats.revenue_this_month}`, color: 'text-emerald-400' },
];
</script>

<template>
    <Head title="Super Admin — Dashboard" />
    <AdminLayout>
        <div class="p-8">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-white">Tableau de bord</h1>
                <p class="text-slate-400 text-sm mt-1">Vue globale de la plateforme LeadFlow</p>
            </div>

            <!-- Stat Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div v-for="card in statCards" :key="card.label"
                    class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                    <p class="text-xs text-slate-500 uppercase tracking-widest font-semibold mb-2">{{ card.label }}</p>
                    <p class="text-3xl font-black" :class="card.color">{{ card.value() }}</p>
                </div>
            </div>

            <!-- Revenue Total -->
            <div class="bg-gradient-to-r from-red-900/30 to-slate-900 border border-red-800/30 rounded-xl p-6 mb-8 flex items-center justify-between">
                <div>
                    <p class="text-xs text-red-400 uppercase tracking-widest font-semibold">Revenus totaux (all time)</p>
                    <p class="text-4xl font-black text-white mt-1">${{ stats.revenue_total }}</p>
                </div>
                <div class="text-6xl opacity-20">💰</div>
            </div>

            <!-- Recent Companies -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-white uppercase tracking-widest">Dernières entreprises</h2>
                    <Link href="/admin/companies" class="text-xs text-red-400 hover:text-red-300">Voir tout →</Link>
                </div>
                <table class="w-full text-sm">
                    <tbody>
                        <tr v-for="company in recentCompanies" :key="company.id"
                            class="border-b border-slate-800 hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-2 w-2 rounded-full"
                                        :class="company.is_active ? 'bg-emerald-500' : 'bg-red-500'"></div>
                                    <span class="text-white font-medium">{{ company.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-slate-500">{{ company.users_count }} utilisateur(s)</td>
                            <td class="px-6 py-3 text-slate-600 text-xs">{{ company.created_at }}</td>
                            <td class="px-6 py-3 text-right">
                                <Link :href="`/admin/companies/${company.id}`"
                                    class="text-xs text-slate-400 hover:text-white">Voir</Link>
                            </td>
                        </tr>
                        <tr v-if="recentCompanies.length === 0">
                            <td colspan="4" class="px-6 py-8 text-center text-slate-600">Aucune entreprise.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
