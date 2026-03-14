<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    users: Array<any>;
    companies: Array<any>;
    companyFilter: string | null;
}>();

const filterCompany = ref(props.companyFilter || '');

const applyFilter = () => {
    router.get('/admin/users', filterCompany.value ? { company_id: filterCompany.value } : {}, { preserveState: true });
};

const toggle = (user: any) => {
    if (confirm(`${user.is_active ? 'Désactiver' : 'Activer'} le compte de "${user.name}" ?`)) {
        router.patch(`/admin/users/${user.id}/toggle`);
    }
};

const impersonate = (user: any) => {
    if (confirm(`Activer le mode Ghost en tant que "${user.name}" ?`)) {
        router.post(`/admin/impersonate/${user.id}`);
    }
};

const resetPassword = (user: any) => {
    if (confirm(`Envoyer un reset de mot de passe à ${user.email} ?`)) {
        router.post(`/admin/users/${user.id}/reset-password`);
    }
};
</script>

<template>
    <Head title="Admin — Utilisateurs" />
    <AdminLayout>
        <div class="p-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-white">Utilisateurs</h1>
                    <p class="text-slate-400 text-sm mt-1">{{ users.length }} utilisateur(s)</p>
                </div>
                <Link href="/admin/users/create"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                    + Nouvel utilisateur
                </Link>
            </div>

            <!-- Filter -->
            <div class="flex items-center gap-3 mb-6">
                <select v-model="filterCompany" @change="applyFilter"
                    class="bg-slate-800 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-red-500">
                    <option value="">Toutes les entreprises</option>
                    <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-950/50 border-b border-slate-800">
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Utilisateur</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Entreprise</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Rôle</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Membre depuis</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in users" :key="user.id"
                            class="border-b border-slate-800 hover:bg-slate-800/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-white">{{ user.name }}</div>
                                <div class="text-xs text-slate-500">{{ user.email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span v-if="user.company" class="text-slate-300 text-xs">{{ user.company.name }}</span>
                                <span v-else class="text-slate-600 text-xs italic">Aucune</span>
                            </td>
                            <td class="px-6 py-4">
                                <span v-if="user.is_super_admin"
                                    class="text-xs font-bold bg-red-900/40 text-red-400 px-2 py-1 rounded-full">SuperAdmin</span>
                                <span v-else class="text-xs text-slate-500">Standard</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-semibold"
                                    :class="user.is_active ? 'text-emerald-400' : 'text-red-400'">
                                    {{ user.is_active ? '● Actif' : '● Inactif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-xs">{{ user.created_at }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <button v-if="!user.is_super_admin" @click="impersonate(user)"
                                        class="text-xs text-slate-400 hover:text-white transition-colors" title="Ghost Mode">
                                        👻
                                    </button>
                                    <button @click="resetPassword(user)"
                                        class="text-xs text-amber-500 hover:text-amber-400 transition-colors">
                                        MDP
                                    </button>
                                    <button @click="toggle(user)"
                                        class="text-xs transition-colors"
                                        :class="user.is_active ? 'text-red-500 hover:text-red-400' : 'text-emerald-500 hover:text-emerald-400'">
                                        {{ user.is_active ? 'Désactiver' : 'Activer' }}
                                    </button>
                                    <Link :href="`/admin/users/${user.id}/edit`"
                                        class="text-xs text-red-400 hover:text-red-300 transition-colors">Modifier</Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="users.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-slate-600">Aucun utilisateur.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
