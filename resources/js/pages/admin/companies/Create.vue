<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    company_name: '',
    company_email: '',
    company_phone: '',
    company_industry: '',
    owner_name: '',
    owner_email: '',
    owner_password: '',
});

const submit = () => form.post('/admin/companies');
</script>

<template>
    <Head title="Admin — Créer une entreprise" />
    <AdminLayout>
        <div class="p-8 max-w-3xl">
            <div class="mb-8">
                <Link href="/admin/companies" class="text-xs text-slate-500 hover:text-slate-300 mb-3 inline-block">← Retour</Link>
                <h1 class="text-2xl font-bold text-white">Nouvelle entreprise</h1>
                <p class="text-slate-400 text-sm mt-1">Crée l'entreprise et son utilisateur propriétaire en une seule opération.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Company Info -->
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
                    <h2 class="text-sm font-bold text-white uppercase tracking-widest mb-4">Informations de l'entreprise</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Nom *</label>
                            <input v-model="form.company_name" type="text" required
                                class="w-full bg-slate-800 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-red-500"
                                placeholder="Acme Inc." />
                            <p v-if="form.errors.company_name" class="text-red-400 text-xs mt-1">{{ form.errors.company_name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Email</label>
                            <input v-model="form.company_email" type="email"
                                class="w-full bg-slate-800 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-red-500"
                                placeholder="contact@acme.com" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Téléphone</label>
                            <input v-model="form.company_phone" type="tel"
                                class="w-full bg-slate-800 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-red-500"
                                placeholder="+1 514 000 0000" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Industrie</label>
                            <input v-model="form.company_industry" type="text"
                                class="w-full bg-slate-800 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-red-500"
                                placeholder="Construction, Esthétique..." />
                        </div>
                    </div>
                </div>

                <!-- Owner Info -->
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
                    <h2 class="text-sm font-bold text-white uppercase tracking-widest mb-4">Utilisateur propriétaire</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Nom complet *</label>
                            <input v-model="form.owner_name" type="text" required
                                class="w-full bg-slate-800 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-red-500"
                                placeholder="Jean Dupont" />
                            <p v-if="form.errors.owner_name" class="text-red-400 text-xs mt-1">{{ form.errors.owner_name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Email *</label>
                            <input v-model="form.owner_email" type="email" required
                                class="w-full bg-slate-800 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-red-500"
                                placeholder="jean@acme.com" />
                            <p v-if="form.errors.owner_email" class="text-red-400 text-xs mt-1">{{ form.errors.owner_email }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-slate-400 mb-1">Mot de passe initial *</label>
                            <input v-model="form.owner_password" type="password" required
                                class="w-full bg-slate-800 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-red-500"
                                placeholder="Minimum 8 caractères" />
                            <p v-if="form.errors.owner_password" class="text-red-400 text-xs mt-1">{{ form.errors.owner_password }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <Link href="/admin/companies" class="px-4 py-2 text-sm text-slate-400 hover:text-white transition-colors">Annuler</Link>
                    <button type="submit" :disabled="form.processing"
                        class="bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white px-6 py-2 rounded-lg text-sm font-semibold transition-colors">
                        {{ form.processing ? 'Création...' : 'Créer l\'entreprise' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
