<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps<{
    company: any;
}>();

const toggle = () => {
    const action = props.company.is_active ? 'désactiver' : 'réactiver';
    if (confirm(`Voulez-vous vraiment ${action} cette entreprise ?`)) {
        router.patch(`/admin/companies/${props.company.id}/toggle`);
    }
};

const impersonate = (userId: number) => {
    router.post(`/admin/impersonate/${userId}`);
};

const resetPassword = (userId: number, name: string) => {
    if (confirm(`Envoyer un email de réinitialisation de mot de passe à ${name} ?`)) {
        router.post(`/admin/users/${userId}/reset-password`);
    }
};
</script>

<template>
    <Head :title="`Admin — ${company.name}`" />
    <AdminLayout>
        <div class="p-8">
            <!-- Header -->
            <div class="flex items-start justify-between mb-8">
                <div>
                    <Link href="/admin/companies" class="text-xs text-slate-500 hover:text-slate-300 mb-2 inline-block">← Entreprises</Link>
                    <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                        {{ company.name }}
                        <span class="text-xs font-semibold px-2 py-1 rounded-full"
                            :class="company.is_active ? 'bg-emerald-950/60 text-emerald-400' : 'bg-red-950/60 text-red-400'">
                            {{ company.is_active ? 'Actif' : 'Suspendu' }}
                        </span>
                    </h1>
                    <p class="text-slate-500 text-sm mt-1">{{ company.email }} · créée le {{ company.created_at }}</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="`/admin/companies/${company.id}/edit`"
                        class="px-3 py-1.5 text-xs bg-slate-800 hover:bg-slate-700 text-white rounded-lg transition-colors">
                        Modifier
                    </Link>
                    <button @click="toggle"
                        class="px-3 py-1.5 text-xs rounded-lg transition-colors font-semibold"
                        :class="company.is_active
                            ? 'bg-amber-900/40 text-amber-400 hover:bg-amber-900/60'
                            : 'bg-emerald-900/40 text-emerald-400 hover:bg-emerald-900/60'">
                        {{ company.is_active ? 'Suspendre' : 'Réactiver' }}
                    </button>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                    <p class="text-xs text-slate-500 uppercase tracking-widest font-semibold">Utilisateurs</p>
                    <p class="text-3xl font-black text-white mt-1">{{ company.stats.users_count }}</p>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                    <p class="text-xs text-slate-500 uppercase tracking-widest font-semibold">Widgets</p>
                    <p class="text-3xl font-black text-white mt-1">{{ company.stats.lead_forms_count }}</p>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                    <p class="text-xs text-slate-500 uppercase tracking-widest font-semibold">Leads total</p>
                    <p class="text-3xl font-black text-red-400 mt-1">{{ company.stats.leads_count }}</p>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                    <p class="text-xs text-slate-500 uppercase tracking-widest font-semibold">Revenus payés</p>
                    <p class="text-3xl font-black text-emerald-400 mt-1">${{ company.stats.revenue }}</p>
                </div>
            </div>

            <!-- Users table -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-white uppercase tracking-widest">Utilisateurs</h2>
                    <Link :href="`/admin/users/create`"
                        class="text-xs text-red-400 hover:text-red-300">+ Ajouter</Link>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-950/40 border-b border-slate-800">
                            <th class="px-6 py-3 text-left text-xs text-slate-500 uppercase font-semibold">Nom</th>
                            <th class="px-6 py-3 text-left text-xs text-slate-500 uppercase font-semibold">Email</th>
                            <th class="px-6 py-3 text-left text-xs text-slate-500 uppercase font-semibold">Statut</th>
                            <th class="px-6 py-3 text-right text-xs text-slate-500 uppercase font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in company.users" :key="user.id"
                            class="border-b border-slate-800 hover:bg-slate-800/30 transition-colors">
                            <td class="px-6 py-3">
                                <span class="text-white font-medium">{{ user.name }}</span>
                                <span v-if="user.is_super_admin"
                                    class="ml-2 text-[10px] bg-red-900/40 text-red-400 px-1.5 py-0.5 rounded font-bold">ADMIN</span>
                            </td>
                            <td class="px-6 py-3 text-slate-400">{{ user.email }}</td>
                            <td class="px-6 py-3">
                                <span class="text-xs font-semibold"
                                    :class="user.is_active ? 'text-emerald-400' : 'text-red-400'">
                                    {{ user.is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <button v-if="!user.is_super_admin" @click="impersonate(user.id)"
                                        class="text-xs text-slate-400 hover:text-white transition-colors">
                                        👻 Ghost
                                    </button>
                                    <button @click="resetPassword(user.id, user.name)"
                                        class="text-xs text-amber-500 hover:text-amber-400 transition-colors">
                                        Reset MDP
                                    </button>
                                    <Link :href="`/admin/users/${user.id}/edit`"
                                        class="text-xs text-red-400 hover:text-red-300 transition-colors">Modifier</Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!company.users?.length">
                            <td colspan="4" class="px-6 py-8 text-center text-slate-600">Aucun utilisateur.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
