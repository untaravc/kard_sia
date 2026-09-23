<template>
    <div class="min-h-screen bg-surface text-ink">
        <div class="mx-auto flex min-h-screen max-w-4xl items-center justify-center px-6 py-12">
            <div class="w-full rounded-3xl border border-border bg-panel p-8">
                <div class="text-sm font-semibold text-primary">Reset Access</div>
                <h1 class="mt-3 text-2xl font-semibold">Forgot your password?</h1>
                <p class="mt-3 text-sm text-muted">
                    Enter your email address and we will send you a link to reset your password.
                </p>

                <p v-if="errorMessage"
                    class="mt-4 flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-600">
                    <Icon icon="mdi:alert-circle-outline" class="mt-px h-4 w-4 flex-none" />
                    <span>{{ errorMessage }}</span>
                </p>
                <p v-if="successMessage"
                    class="mt-4 flex items-start gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
                    <Icon icon="mdi:check-circle-outline" class="mt-px h-4 w-4 flex-none" />
                    <span>{{ successMessage }}</span>
                </p>

                <form class="mt-6 grid gap-4" @submit.prevent="submit">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Email</span>
                        <input
                            type="email"
                            v-model.trim="email"
                            placeholder="name@domain.com"
                            autocomplete="email"
                            class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <button
                        type="submit"
                        class="flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-white disabled:opacity-60"
                        :disabled="loading"
                    >
                        <Icon v-if="loading" icon="mdi:loading" class="h-4 w-4 animate-spin" />
                        <span>{{ loading ? 'Sending...' : 'Send reset link' }}</span>
                    </button>
                    <div class="text-xs text-muted">
                        Remembered your password?
                        <router-link class="text-primary" to="/blu/login">Back to login</router-link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { Icon } from '../../icons';
import Repository from '../../repository';

export default {
    name: 'ForgotPassword',
    components: { Icon },
    data() {
        return {
            email: '',
            loading: false,
            errorMessage: '',
            successMessage: '',
        };
    },
    methods: {
        submit() {
            if (this.loading) {
                return;
            }

            this.loading = true;
            this.errorMessage = '';
            this.successMessage = '';

            return Repository.post('/api/forgot-password', { email: this.email })
                .then((response) => {
                    const data = response && response.data ? response.data : {};
                    if (!data.success) {
                        this.errorMessage = data.text || 'Request failed';
                        return;
                    }

                    this.successMessage = 'A password reset link has been sent to your email.';
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Request failed';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
};
</script>
