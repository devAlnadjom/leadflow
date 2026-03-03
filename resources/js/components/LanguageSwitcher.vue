<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { useI18n } from '@/composables/useI18n';
import type { SupportedLocale } from '@/i18n/messages';

const { locale, t, setLocale } = useI18n();

const applyLocale = (nextLocale: SupportedLocale): void => {
    setLocale(nextLocale);
};
</script>

<template>
    <div class="flex items-center gap-1">
        <Button
            type="button"
            size="sm"
            :variant="locale === 'fr' ? 'default' : 'ghost'"
            @click="applyLocale('fr')"
        >
            {{ t('language.fr') }}
        </Button>
        <Button
            type="button"
            size="sm"
            :variant="locale === 'en' ? 'default' : 'ghost'"
            @click="applyLocale('en')"
        >
            {{ t('language.en') }}
        </Button>
    </div>
</template>
