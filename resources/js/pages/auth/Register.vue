<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();
</script>

<template>
    <AuthBase
        :title="t('auth.register_title')"
        :description="t('auth.register_description')"
    >
        <Head :title="t('auth.register_button')" />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">{{ t('auth.name') }}</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        name="name"
                        :placeholder="t('auth.name')"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">{{ t('auth.email') }}</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        name="email"
                        :placeholder="t('auth.email')"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">{{ t('auth.password') }}</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="3"
                        autocomplete="new-password"
                        name="password"
                        :placeholder="t('auth.password')"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">{{ t('auth.confirm_password') }}</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        :tabindex="4"
                        autocomplete="new-password"
                        name="password_confirmation"
                        :placeholder="t('auth.confirm_password')"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full"
                    tabindex="5"
                    :disabled="processing"
                    data-test="register-user-button"
                >
                    <Spinner v-if="processing" />
                    {{ t('auth.register_button') }}
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                {{ t('auth.already_have_account') }}
                <TextLink
                    :href="login()"
                    class="underline underline-offset-4"
                    :tabindex="6"
                    >{{ t('auth.signin') }}</TextLink
                >
            </div>
        </Form>
    </AuthBase>
</template>

