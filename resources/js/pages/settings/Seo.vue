<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useI18n } from '@/composables/useI18n';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Separator } from '@/components/ui/separator';
import { edit } from '@/routes/seo-settings';
import { toast } from 'vue-sonner';
import {
    Facebook,
    Instagram,
    Globe,
    Search,
    Image as ImageIcon,
    Trash2,
    Plus,
    UploadCloud,
    ExternalLink,
    Sparkles,
    Eye,
    X,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface Service {
    id: number;
    name: string;
    description: string | null;
    image_path: string | null;
}

interface Gallery {
    id: number;
    image_path: string;
    caption: string | null;
}

const props = defineProps<{
    settings: {
        facebook_url: string | null;
        instagram_url: string | null;
        seo_title: string | null;
        seo_description: string | null;
        seo_keywords: string | null;
    };
    services: Service[];
    galleries: Gallery[];
    companySlug?: string;
}>();

const { t } = useI18n();

const breadcrumbItems = computed<BreadcrumbItem[]>(() => [
    {
        title: t('seo_settings.title'),
        href: edit(),
    },
]);

// ─── SEO Form ───
const form = useForm({
    facebook_url: props.settings.facebook_url || '',
    instagram_url: props.settings.instagram_url || '',
    seo_title: props.settings.seo_title || '',
    seo_description: props.settings.seo_description || '',
    seo_keywords: props.settings.seo_keywords || '',
});

const seoTitleLength = computed(() => form.seo_title.length);
const seoDescLength = computed(() => form.seo_description.length);

const updateSeo = () => {
    form.patch('/settings/seo', {
        preserveScroll: true,
        onSuccess: () => toast(t('common.saved')),
    });
};

// ─── Services ───
const showServiceForm = ref(false);
const serviceForm = useForm({
    name: '',
    description: '',
    image: null as File | null,
});
const serviceImagePreview = ref<string | null>(null);

const handleServiceImage = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        serviceForm.image = target.files[0];
        serviceImagePreview.value = URL.createObjectURL(target.files[0]);
    }
};

const addService = () => {
    serviceForm.post('/settings/services', {
        preserveScroll: true,
        onSuccess: () => {
            serviceForm.reset();
            serviceImagePreview.value = null;
            showServiceForm.value = false;
            toast('Service ajouté');
        },
    });
};

const cancelServiceForm = () => {
    serviceForm.reset();
    serviceImagePreview.value = null;
    showServiceForm.value = false;
};

const deleteService = (id: number) => {
    if (confirm('Supprimer ce service ?')) {
        router.delete(`/settings/services/${id}`, {
            preserveScroll: true,
            onSuccess: () => toast('Service supprimé'),
        });
    }
};

// ─── Gallery ───
const galleryForm = useForm({
    image: null as File | null,
    caption: '',
});
const galleryImagePreview = ref<string | null>(null);
const showCaptionInput = ref(false);

const handleGalleryImage = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        galleryForm.image = target.files[0];
        galleryImagePreview.value = URL.createObjectURL(target.files[0]);
        showCaptionInput.value = true;
    }
};

const addToGallery = () => {
    galleryForm.post('/settings/galleries', {
        preserveScroll: true,
        onSuccess: () => {
            galleryForm.reset();
            galleryImagePreview.value = null;
            showCaptionInput.value = false;
            toast('Photo ajoutée');
        },
    });
};

const cancelGalleryUpload = () => {
    galleryForm.reset();
    galleryImagePreview.value = null;
    showCaptionInput.value = false;
};

const deleteGallery = (id: number) => {
    if (confirm('Supprimer cette photo ?')) {
        router.delete(`/settings/galleries/${id}`, {
            preserveScroll: true,
            onSuccess: () => toast('Photo supprimée'),
        });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="t('seo_settings.title')" />
        <h1 class="sr-only">SEO Settings</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    :title="t('seo_settings.title')"
                    :description="t('seo_settings.description')"
                />

                <!-- Preview Link -->
                <div v-if="companySlug" class="flex items-center gap-3 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-lg">
                    <Eye class="w-4 h-4 text-emerald-600 shrink-0" />
                    <p class="text-sm text-emerald-800 flex-1">
                        {{ t('seo_settings.public_page_available') }}
                        <a :href="`/c/${companySlug}`" target="_blank" class="font-semibold underline underline-offset-2 hover:text-emerald-900">
                            /c/{{ companySlug }}
                        </a>
                    </p>
                    <a :href="`/c/${companySlug}`" target="_blank">
                        <Button variant="outline" size="sm" class="gap-1.5 text-emerald-700 border-emerald-300 hover:bg-emerald-100">
                            <ExternalLink class="w-3.5 h-3.5" />
                            {{ t('seo_settings.view_page') }}
                        </Button>
                    </a>
                </div>

                <form @submit.prevent="updateSeo" class="space-y-8">

                    <!-- ══════════════ Section 1 : Réseaux Sociaux ══════════════ -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                <Globe class="w-4 h-4 text-blue-600" />
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold">{{ t('seo_settings.social_title') }}</h3>
                                <p class="text-xs text-muted-foreground">{{ t('seo_settings.social_desc') }}</p>
                            </div>
                        </div>

                        <div class="grid gap-3">
                            <div class="grid gap-1.5">
                                <Label for="facebook_url" class="text-xs">{{ t('seo_settings.facebook') }}</Label>
                                <div class="relative">
                                    <Facebook class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-blue-500" />
                                    <Input
                                        id="facebook_url"
                                        v-model="form.facebook_url"
                                        type="url"
                                        :placeholder="t('seo_settings.facebook_placeholder')"
                                        class="pl-10"
                                    />
                                </div>
                            </div>
                            <div class="grid gap-1.5">
                                <Label for="instagram_url" class="text-xs">{{ t('seo_settings.instagram') }}</Label>
                                <div class="relative">
                                    <Instagram class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-pink-500" />
                                    <Input
                                        id="instagram_url"
                                        v-model="form.instagram_url"
                                        type="url"
                                        :placeholder="t('seo_settings.instagram_placeholder')"
                                        class="pl-10"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <Separator />

                    <!-- ══════════════ Section 2 : Métadonnées SEO ══════════════ -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                                <Search class="w-4 h-4 text-amber-600" />
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold">{{ t('seo_settings.metadata_title') }}</h3>
                                <p class="text-xs text-muted-foreground">{{ t('seo_settings.metadata_desc') }}</p>
                            </div>
                        </div>

                        <!-- Google Preview -->
                        <div class="rounded-lg border border-dashed border-slate-200 bg-slate-50/50 p-4 space-y-1">
                            <p class="text-xs font-medium text-slate-400 mb-2 flex items-center gap-1.5">
                                <Sparkles class="w-3 h-3" />
                                {{ t('seo_settings.google_preview') }}
                            </p>
                            <p class="text-lg font-medium text-blue-700 leading-snug truncate">
                                {{ form.seo_title || t('seo_settings.title_placeholder') }}
                            </p>
                            <p class="text-xs text-emerald-700 truncate">
                                {{ companySlug ? `votresite.com/c/${companySlug}` : 'votresite.com/c/votre-page' }}
                            </p>
                            <p class="text-sm text-slate-600 line-clamp-2 leading-relaxed">
                                {{ form.seo_description || t('seo_settings.desc_placeholder') }}
                            </p>
                        </div>

                        <div class="grid gap-3">
                            <div class="grid gap-1.5">
                                <div class="flex items-center justify-between">
                                    <Label for="seo_title" class="text-xs">{{ t('seo_settings.seo_title') }}</Label>
                                    <span :class="[
                                        'text-[10px] font-mono tabular-nums',
                                        seoTitleLength > 60 ? 'text-red-500' : 'text-muted-foreground'
                                    ]">
                                        {{ seoTitleLength }}/60
                                    </span>
                                </div>
                                <Input id="seo_title" v-model="form.seo_title" :placeholder="t('seo_settings.title_input_placeholder')" />
                            </div>

                            <div class="grid gap-1.5">
                                <div class="flex items-center justify-between">
                                    <Label for="seo_description" class="text-xs">{{ t('seo_settings.seo_description') }}</Label>
                                    <span :class="[
                                        'text-[10px] font-mono tabular-nums',
                                        seoDescLength > 160 ? 'text-red-500' : 'text-muted-foreground'
                                    ]">
                                        {{ seoDescLength }}/160
                                    </span>
                                </div>
                                <Textarea
                                    id="seo_description"
                                    v-model="form.seo_description"
                                    :placeholder="t('seo_settings.desc_input_placeholder')"
                                    class="min-h-[80px]"
                                />
                            </div>

                            <div class="grid gap-1.5">
                                <Label for="seo_keywords" class="text-xs">{{ t('seo_settings.seo_keywords') }}</Label>
                                <Input
                                    id="seo_keywords"
                                    v-model="form.seo_keywords"
                                    :placeholder="t('seo_settings.keywords_placeholder')"
                                />
                                <p class="text-[11px] text-muted-foreground">{{ t('seo_settings.keywords_hint') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="flex items-center gap-4">
                        <Button type="submit" :disabled="form.processing">
                            {{ t('common.save') }}
                        </Button>
                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">
                                {{ t('common.saved') }}
                            </p>
                        </Transition>
                    </div>
                </form>

                <Separator />

                <!-- ══════════════ Section 3 : Services ══════════════ -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center">
                                <Sparkles class="w-4 h-4 text-violet-600" />
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold">{{ t('seo_settings.services_title') }}</h3>
                                <p class="text-xs text-muted-foreground">{{ t('seo_settings.services_desc') }}</p>
                            </div>
                        </div>
                        <Button
                            v-if="!showServiceForm"
                            variant="outline"
                            size="sm"
                            class="gap-1.5"
                            @click="showServiceForm = true"
                        >
                            <Plus class="w-3.5 h-3.5" />
                            {{ t('seo_settings.add') }}
                        </Button>
                    </div>

                    <!-- Add Service Form (Inline) -->
                    <Transition
                        enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="opacity-0 -translate-y-2"
                        leave-active-class="transition-all duration-150 ease-in"
                        leave-to-class="opacity-0 -translate-y-2"
                    >
                        <div v-if="showServiceForm" class="rounded-lg border border-dashed border-violet-300 bg-violet-50/30 p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-violet-800">{{ t('seo_settings.new_service') }}</p>
                                <button type="button" @click="cancelServiceForm" class="text-slate-400 hover:text-slate-600">
                                    <X class="w-4 h-4" />
                                </button>
                            </div>

                            <div class="grid gap-3">
                                <Input
                                    v-model="serviceForm.name"
                                    :placeholder="t('seo_settings.service_name')"
                                    class="bg-white"
                                />
                                <Textarea
                                    v-model="serviceForm.description"
                                    :placeholder="t('seo_settings.service_desc')"
                                    class="bg-white min-h-[70px]"
                                />

                                <div class="flex items-center gap-3">
                                    <div class="flex-1">
                                        <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-600 hover:text-slate-800 transition-colors">
                                            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-slate-200 bg-white hover:bg-slate-50 transition-colors">
                                                <UploadCloud class="w-3.5 h-3.5" />
                                                <span class="text-xs font-medium">{{ serviceImagePreview ? t('seo_settings.change_image') : t('seo_settings.image_optional') }}</span>
                                            </div>
                                            <input type="file" @change="handleServiceImage" accept="image/*" class="hidden" />
                                        </label>
                                    </div>
                                    <img v-if="serviceImagePreview" :src="serviceImagePreview" class="w-10 h-10 rounded-md object-cover border" />
                                </div>
                            </div>

                            <div class="flex justify-end gap-2 pt-1">
                                <Button variant="ghost" size="sm" @click="cancelServiceForm">{{ t('seo_settings.cancel') }}</Button>
                                <Button size="sm" @click="addService" :disabled="serviceForm.processing || !serviceForm.name" class="gap-1.5">
                                    <Plus class="w-3.5 h-3.5" />
                                    {{ t('seo_settings.add_service') }}
                                </Button>
                            </div>
                        </div>
                    </Transition>

                    <!-- Service List -->
                    <div v-if="services.length > 0" class="space-y-2">
                        <div
                            v-for="service in services"
                            :key="service.id"
                            class="flex items-center gap-3 p-3 rounded-lg border border-slate-100 bg-white group hover:border-slate-200 transition-colors"
                        >
                            <div
                                v-if="service.image_path"
                                class="w-10 h-10 rounded-lg bg-cover bg-center shrink-0 border border-slate-100"
                                :style="{ backgroundImage: `url(/storage/${service.image_path})` }"
                            ></div>
                            <div v-else class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                                <Sparkles class="w-4 h-4 text-slate-400" />
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium truncate">{{ service.name }}</p>
                                <p v-if="service.description" class="text-xs text-muted-foreground truncate">{{ service.description }}</p>
                            </div>

                            <Button
                                variant="ghost"
                                size="icon"
                                class="opacity-0 group-hover:opacity-100 transition-opacity text-red-500 hover:text-red-700 hover:bg-red-50 shrink-0"
                                @click="deleteService(service.id)"
                            >
                                <Trash2 class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>

                    <p v-else-if="!showServiceForm" class="text-sm text-muted-foreground text-center py-6 bg-slate-50 rounded-lg border border-dashed">
                        {{ t('seo_settings.no_services') }}
                    </p>
                </div>

                <Separator />

                <!-- ══════════════ Section 4 : Galerie ══════════════ -->
                <div class="space-y-4 pb-8">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center">
                            <ImageIcon class="w-4 h-4 text-rose-600" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold">{{ t('seo_settings.gallery_title') }}</h3>
                            <p class="text-xs text-muted-foreground">{{ t('seo_settings.gallery_desc') }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <!-- Existing photos -->
                        <div
                            v-for="photo in galleries"
                            :key="photo.id"
                            class="aspect-square relative group rounded-xl overflow-hidden border border-slate-100"
                        >
                            <img :src="`/storage/${photo.image_path}`" class="w-full h-full object-cover" />
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex flex-col justify-between p-2.5">
                                <Button
                                    variant="destructive"
                                    size="icon"
                                    class="ml-auto w-7 h-7"
                                    @click="deleteGallery(photo.id)"
                                >
                                    <Trash2 class="w-3.5 h-3.5" />
                                </Button>
                                <span v-if="photo.caption" class="text-white text-[10px] font-medium leading-tight">{{ photo.caption }}</span>
                            </div>
                        </div>

                        <!-- Upload new photo -->
                        <div class="aspect-square rounded-xl border-2 border-dashed border-slate-200 hover:border-slate-300 transition-colors bg-slate-50/50 flex flex-col items-center justify-center relative overflow-hidden">
                            <!-- Preview state -->
                            <div v-if="galleryImagePreview" class="absolute inset-0">
                                <img :src="galleryImagePreview" class="w-full h-full object-cover" />
                                <div class="absolute inset-0 bg-black/40 flex flex-col items-center justify-center p-2 gap-2">
                                    <Input
                                        v-model="galleryForm.caption"
                                        :placeholder="t('seo_settings.caption')"
                                        class="h-7 text-xs bg-white/90 max-w-[90%]"
                                    />
                                    <div class="flex gap-1.5">
                                        <Button size="sm" variant="secondary" class="h-7 text-xs" @click="cancelGalleryUpload">
                                            <X class="w-3 h-3" />
                                        </Button>
                                        <Button size="sm" class="h-7 text-xs gap-1" @click="addToGallery" :disabled="galleryForm.processing">
                                            <Plus class="w-3 h-3" />
                                            {{ t('seo_settings.add') }}
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <!-- Upload state -->
                            <label v-else class="cursor-pointer w-full h-full flex flex-col items-center justify-center gap-1.5 p-3">
                                <UploadCloud class="w-6 h-6 text-slate-400" />
                                <span class="text-[10px] text-slate-400 font-medium text-center leading-tight">{{ t('seo_settings.add_photo') }}</span>
                                <input type="file" @change="handleGalleryImage" accept="image/*" class="hidden" />
                            </label>
                        </div>
                    </div>

                    <p v-if="galleries.length === 0 && !galleryImagePreview" class="text-xs text-muted-foreground text-center">
                        {{ t('seo_settings.no_photos') }}
                    </p>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
