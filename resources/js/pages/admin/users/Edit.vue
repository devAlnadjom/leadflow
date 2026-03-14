<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{ user: any; companies: Array<any> }>();

const form = useForm({
    name:            props.user.name,
    email:           props.user.email,
    company_id:      props.user.company_id || '',
    is_super_admin:  props.user.is_super_admin,
    password:        '',
    password_confirmation: '',
});

const submit = () => form.put(`/admin/users/${props.user.id}`);
</script>

<template>
    <Head :title="`Admin — Modifier ${user.name}`" />
    <AdminLayout>
        <div class="p-8 max-w-2xl">
            <div class="mb-8">
                <Link href="/admin/users" class="text-xs text-slate-500 hover:text-slate-300 mb-2 inline-block">← Utilisateurs</Link>
                <h1 class="text-2xl font-bold text-white">Modifier : {{ user.name }}</h1>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Nom *</label>
                            <input v-model="form.name" type="text" required
                                class="w-full bg-slate-800 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-red-500" />
                            <p v-if="form.errors.name" class="text-red-400 text-xs mt-1">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Email *</label>
                            <input v-model="form.email" type="email" required
                                class="w-full bg-slate-800 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-red-500" />
                            <p v-if="form.errors.email" class="text-red-400 text-xs mt-1">{{ form.errors.email }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Nouveau MDP <span class="text-slate-600">(optionnel)</span></label>
                            <input v-model="form.password" type="password"
                                class="w-full bg-slate-800 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-red-500"
                                placeholder="Laisser vide pour ne pas changer" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Confirmer MDP</label>
                            <input v-model="form.password_confirmation" type="password"
                                class="w-full bg-slate-800 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-red-500" />
                            <p v-if="form.errors.password" class="text-red-400 text-xs mt-1">{{ form.errors.password }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Entreprise</label>
                        <select v-model="form.company_id"
                            class="w-full bg-slate-800 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-red-500">
                            <option value="">— Aucune —</option>
                            <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                </div>

                <!-- Super Admin toggle -->
                <div class="bg-red-950/20 border border-red-900/30 rounded-xl p-5">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input v-model="form.is_super_admin" type="checkbox"
                            class="mt-0.5 h-4 w-4 rounded border-slate-700 bg-slate-800 text-red-600 focus:ring-red-500" />
                        <div>
                            <p class="text-sm font-bold text-red-400">Accès Super Administrateur</p>
                            <p class="text-xs text-slate-500 mt-0.5">Donne accès au panneau /admin et à toutes les données.</p>
                        </div>
                    </label>
                </div>

                <div class="flex justify-end gap-3">
                    <Link href="/admin/users" class="px-4 py-2 text-sm text-slate-400 hover:text-white transition-colors">Annuler</Link>
                    <button type="submit" :disabled="form.processing"
                        class="bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white px-6 py-2 rounded-lg text-sm font-semibold transition-colors">
                        {{ form.processing ? 'Sauvegarde...' : 'Enregistrer' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
