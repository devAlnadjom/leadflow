import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { messages, type SupportedLocale } from '@/i18n/messages';

type SharedPageProps = {
    locale: SupportedLocale;
};

export function useI18n() {
    const page = usePage<SharedPageProps>();

    const locale = computed<SupportedLocale>(() => {
        const current = page.props.locale;

        return current === 'fr' || current === 'en' ? current : 'en';
    });

    const t = (key: string): string => {
        return messages[locale.value][key] ?? messages.en[key] ?? key;
    };

    const setLocale = (nextLocale: SupportedLocale): void => {
        if (nextLocale === locale.value) {
            return;
        }

        router.post(
            '/locale',
            { locale: nextLocale },
            {
                preserveScroll: true,
                preserveState: true,
            },
        );
    };

    return {
        locale,
        t,
        setLocale,
    };
}
