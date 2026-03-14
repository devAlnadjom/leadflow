<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{ company: any }>();

const form = useForm({
    name:      props.company.name,
    email:     props.company.email || '',
    phone:     props.company.phone || '',
    industry:  props.company.industry || '',
    website:   props.company.website || '',
});

const submit = () => form.put(`/admin/companies/${props.company.id}`);
</script>

<template>
    <Head :title="`Admin — Modifier ${company.name}`" />
    <AdminLayout>
        <div class="p-8 max-w-2xl">
            <div class="mb-8">
                <Link :href="`/admin/companies/${company.id}`" class="text-xs text-slate-500 hover:text-slate-300 mb-2 inline-block">← Retour</Link>
                <h1 class="text-2xl font-bold text-white">Modifier : {{ company.name }}</h1>
            </div>

            <form @submit.prevent="submit" class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Nom *</label>
                        <input v-model="form.name" type="text" required
                            class="w-full bg-slate-800 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-red-500" />
                        <p v-if="form.errors.name" class="text-red-400 text-xs mt-1">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Email</label>
                        <input v-model="form.email" type="email"
                            class="w-full bg-slate-800 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-red-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Téléphone</label>
                        <input v-model="form.phone" type="tel"
                            class="w-full bg-slate-800 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-red-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Industrie</label>
                        <input v-model="form.industry" type="text"
                            class="w-full bg-slate-800 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-red-500" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-slate-400 mb-1">Site web</label>
                        <input v-model="form.website" type="url"
                            class="w-full bg-slate-800 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-red-500"
                            placeholder="https://..." />
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
                    <Link :href="`/admin/companies/${company.id}`"
                        class="px-4 py-2 text-sm text-slate-400 hover:text-white transition-colors">Annuler</Link>
                    <button type="submit" :disabled="form.processing"
                        class="bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white px-6 py-2 rounded-lg text-sm font-semibold transition-colors">
                        {{ form.processing ? 'Sauvegarde...' : 'Enregistrer' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
