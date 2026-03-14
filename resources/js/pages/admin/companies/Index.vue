<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps<{
    companies: Array<any>;
}>();

const toggle = (company: any) => {
    if (confirm(`${company.is_active ? 'Désactiver' : 'Activer'} "${company.name}" ?`)) {
        router.patch(`/admin/companies/${company.id}/toggle`);
    }
};
</script>

<template>
    <Head title="Admin — Entreprises" />
    <AdminLayout>
        <div class="p-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-white">Entreprises</h1>
                    <p class="text-slate-400 text-sm mt-1">{{ companies.length }} entreprise(s) sur la plateforme</p>
                </div>
                <Link href="/admin/companies/create"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2">
                    + Nouvelle entreprise
                </Link>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-950/50 border-b border-slate-800">
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Entreprise</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Industrie</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Users</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Créée</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="company in companies" :key="company.id"
                            class="border-b border-slate-800 hover:bg-slate-800/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-white">{{ company.name }}</div>
                                <div class="text-xs text-slate-500">{{ company.email }}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-400">{{ company.industry || '—' }}</td>
                            <td class="px-6 py-4 text-slate-300">{{ company.users_count }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2 py-1 rounded-full"
                                    :class="company.is_active
                                        ? 'bg-emerald-950/60 text-emerald-400 border border-emerald-800/40'
                                        : 'bg-red-950/60 text-red-400 border border-red-800/40'">
                                    <span class="h-1.5 w-1.5 rounded-full"
                                        :class="company.is_active ? 'bg-emerald-400' : 'bg-red-400'"></span>
                                    {{ company.is_active ? 'Actif' : 'Suspendu' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-xs">{{ company.created_at }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <Link :href="`/admin/companies/${company.id}`"
                                        class="text-xs text-slate-400 hover:text-white transition-colors">Voir</Link>
                                    <Link :href="`/admin/companies/${company.id}/edit`"
                                        class="text-xs text-red-400 hover:text-red-300 transition-colors">Modifier</Link>
                                    <button @click="toggle(company)"
                                        class="text-xs transition-colors"
                                        :class="company.is_active ? 'text-amber-500 hover:text-amber-400' : 'text-emerald-500 hover:text-emerald-400'">
                                        {{ company.is_active ? 'Suspendre' : 'Rétablir' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="companies.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-slate-600">Aucune entreprise enregistrée.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
