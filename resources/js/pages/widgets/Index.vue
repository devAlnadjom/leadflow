<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { 
    LayoutList, 
    LayoutGrid, 
    Search,
    Plus, 
    Code, 
    Eye, 
    Edit, 
    Trash2, 
    ExternalLink, 
    Settings2,
    Layers,
    CheckCircle2,
    XCircle
} from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { useI18n } from '@/composables/useI18n';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type LeadFormListItem = {
    id: number;
    name: string;
    embed_key: string;
    is_active: boolean;
    submit_button_label: string;
    fields_count: number;
    embed_script: string;
};

type Props = {
    leadForms: LeadFormListItem[];
};

const props = defineProps<Props>();
const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('widgets.title'),
        href: '/widgets',
    },
]);

const removeWidget = (id: number): void => {
    if (confirm('Voulez-vous vraiment supprimer ce widget ?')) {
        router.delete(`/widgets/${id}`);
    }
};

const search = ref('');
const filteredLeadForms = computed(() => {
    if (!search.value) return props.leadForms;
    return props.leadForms.filter(lf => 
        lf.name.toLowerCase().includes(search.value.toLowerCase())
    );
});

const copyCode = (code: string) => {
    navigator.clipboard.writeText(code);
};

const viewMode = ref(localStorage.getItem('widgetflow_view_mode') || 'card');
const setViewMode = (mode: string) => {
    viewMode.value = mode;
    localStorage.setItem('widgetflow_view_mode', mode);
};
</script>

<template>
    <Head :title="t('widgets.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 lg:p-8 bg-slate-50/50 min-h-screen">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <Heading
                    :title="t('widgets.title')"
                    :description="t('widgets.description')"
                />

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <div class="relative flex-1 sm:flex-none sm:min-w-[240px]">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Rechercher un widget..."
                            class="pl-9 h-10 w-full bg-white shadow-sm border-slate-200"
                        />
                    </div>

                    <div class="flex items-center p-1 bg-white border rounded-lg shadow-sm shrink-0">
                        <button type="button" @click="setViewMode('list')" 
                                :class="['p-1.5 rounded text-sm transition-colors', viewMode === 'list' ? 'bg-slate-100 text-slate-900' : 'text-slate-500 hover:text-slate-700']">
                            <LayoutList class="w-4 h-4" />
                        </button>
                        <button type="button" @click="setViewMode('card')"
                                :class="['p-1.5 rounded text-sm transition-colors', viewMode === 'card' ? 'bg-slate-100 text-slate-900' : 'text-slate-500 hover:text-slate-700']">
                            <LayoutGrid class="w-4 h-4" />
                        </button>
                    </div>

                    <Button as-child class="gap-2 shadow-sm shrink-0">
                        <Link href="/widgets/create">
                            <Plus class="w-4 h-4" />
                            <span class="hidden sm:inline">{{ t('widgets.new') }}</span>
                        </Link>
                    </Button>
                </div>
            </div>

            <div v-if="props.leadForms.length === 0" class="bg-white rounded-2xl border border-dashed border-slate-300 p-12 text-center flex flex-col items-center justify-center min-h-[300px]">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 border border-slate-100">
                    <Settings2 class="w-8 h-8 text-slate-300" />
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-2">{{ t('widgets.none') }}</h3>
                <p class="text-slate-500 max-w-sm mx-auto mb-6">Commencez par créer votre premier widget pour capturer des prospects sur votre site.</p>
                <Button as-child variant="outline">
                    <Link href="/widgets/create">{{ t('widgets.new') }}</Link>
                </Button>
            </div>

            <!-- Card View -->
            <div v-if="viewMode === 'card' && filteredLeadForms.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div 
                    v-for="leadForm in filteredLeadForms" 
                    :key="leadForm.id"
                    class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 hover:shadow-md transition-shadow group flex flex-col"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                                <Layers class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 truncate max-w-[180px]">
                                    {{ leadForm.name }}
                                </h3>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span v-if="leadForm.is_active" class="flex items-center gap-1 text-[10px] font-bold text-emerald-600 uppercase tracking-wider">
                                        <CheckCircle2 class="w-3 h-3" /> {{ t('widgets.active') }}
                                    </span>
                                    <span v-else class="flex items-center gap-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                        <XCircle class="w-3 h-3" /> {{ t('widgets.inactive') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-1">
                            <Button size="icon" variant="ghost" class="h-8 w-8 text-slate-400 hover:text-indigo-600" as-child>
                                <Link :href="`/widgets/${leadForm.id}/edit`" :title="t('widgets.edit')">
                                    <Edit class="w-4 h-4" />
                                </Link>
                            </Button>
                            <Button size="icon" variant="ghost" class="h-8 w-8 text-slate-400 hover:text-red-600" @click="removeWidget(leadForm.id)" :title="t('widgets.delete')">
                                <Trash2 class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>

                    <div class="bg-slate-50 rounded-lg p-3 mb-5 border border-slate-100">
                        <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                            <span class="flex items-center gap-1"><Code class="w-3 h-3" /> Code d'intégration</span>
                            <button @click="copyCode(leadForm.embed_script)" class="hover:text-indigo-600 font-medium text-slate-400">Copier</button>
                        </div>
                        <code class="text-[10px] text-slate-600 block truncate font-mono bg-white px-2 py-1.5 rounded border border-slate-200">
                            {{ leadForm.embed_script }}
                        </code>
                    </div>

                    <div class="mt-auto flex items-center justify-between">
                        <div class="flex items-center gap-4 text-xs font-medium text-slate-500">
                            <div class="flex items-center gap-1">
                                <span class="text-slate-900 font-bold">{{ leadForm.fields_count }}</span> champs
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <Button size="sm" variant="outline" class="h-8 text-xs gap-1.5 px-3" as-child>
                                <a :href="`/widget-preview?uid=${leadForm.embed_key}`" target="_blank">
                                    <Eye class="w-3.5 h-3.5" /> {{ t('widgets.integration') }}
                                </a>
                            </Button>
                            <Button size="sm" class="h-8 text-xs gap-1.5 px-3" as-child>
                                <Link :href="`/widgets/${leadForm.id}`">
                                    {{ t('widgets.view') }} <ExternalLink class="w-3 h-3" />
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div v-else-if="viewMode === 'list' && filteredLeadForms.length > 0" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-medium whitespace-nowrap">
                            <tr>
                                <th class="px-6 py-4">{{ t('widgets.name') }}</th>
                                <th class="px-6 py-4">{{ t('widgets.status') }}</th>
                                <th class="px-6 py-4">{{ t('widgets.fields') }}</th>
                                <th class="px-6 py-4">{{ t('widgets.script') }}</th>
                                <th class="px-6 py-4 text-right">{{ t('widgets.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="leadForm in filteredLeadForms" :key="leadForm.id" class="bg-white hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <Link :href="`/widgets/${leadForm.id}`" class="font-bold text-slate-900 hover:text-indigo-600 transition-colors">
                                        {{ leadForm.name }}
                                    </Link>
                                </td>
                                <td class="px-6 py-4">
                                    <Badge :variant="leadForm.is_active ? 'default' : 'secondary'" class="text-[10px] px-2 py-0">
                                        {{ leadForm.is_active ? t('widgets.active') : t('widgets.inactive') }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-slate-500 font-medium">
                                    {{ leadForm.fields_count }}
                                </td>
                                <td class="px-6 py-4 max-w-xs">
                                    <code class="text-[10px] text-slate-400 block truncate bg-slate-50 px-2 py-1 rounded border">
                                        {{ leadForm.embed_script }}
                                    </code>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Button size="icon" variant="ghost" class="h-8 w-8 text-slate-400 hover:text-indigo-600" as-child>
                                            <Link :href="`/widgets/${leadForm.id}`">
                                                <Eye class="w-4 h-4" />
                                            </Link>
                                        </Button>
                                        <Button size="icon" variant="ghost" class="h-8 w-8 text-slate-400 hover:text-indigo-600" as-child>
                                            <Link :href="`/widgets/${leadForm.id}/edit`">
                                                <Edit class="w-4 h-4" />
                                            </Link>
                                        </Button>
                                        <Button size="icon" variant="ghost" class="h-8 w-8 text-slate-400 hover:text-red-600" @click="removeWidget(leadForm.id)">
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

