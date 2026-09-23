<template>
    <div class="min-h-screen bg-surface text-ink">
        <div class="mx-auto flex min-h-screen max-w-4xl items-center justify-center px-6 py-12">
            <div class="w-full rounded-3xl border border-border bg-panel p-8">
                <div class="text-sm font-semibold text-primary">Set new password</div>
                <h1 class="mt-3 text-2xl font-semibold">Reset your password</h1>

                <template v-if="checkingToken">
                    <p class="mt-3 text-sm text-muted">Validating your reset link...</p>
                </template>

                <template v-else-if="!tokenValid">
                    <p class="mt-3 flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-600">
                        <Icon icon="mdi:alert-circle-outline" class="mt-px h-4 w-4 flex-none" />
                        <span>{{ tokenError || 'This reset link is invalid or has expired.' }}</span>
                    </p>
                    <div class="mt-6 text-xs text-muted">
                        <router-link class="text-primary" to="/blu/forgot-password">Request a new link</router-link>
                    </div>
                </template>

                <template v-else>
                    <p class="mt-3 text-sm text-muted">
                        Choose a strong password for <span class="font-medium text-ink">{{ email }}</span> and confirm it below.
                    </p>

                    <p v-if="errorMessage"
                        class="mt-4 flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-600">
                        <Icon icon="mdi:alert-circle-outline" class="mt-px h-4 w-4 flex-none" />
                        <span>{{ errorMessage }}</span>
                    </p>

                    <form class="mt-6 grid gap-4" @submit.prevent="submit">
                        <label class="grid gap-2 text-sm">
                            <span class="text-muted">New password</span>
                            <input
                                type="password"
                                placeholder="Enter new password"
                                v-model="form.password"
                                autocomplete="new-password"
                                class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                            />
                        </label>
                        <label class="grid gap-2 text-sm">
                            <span class="text-muted">Confirm new password</span>
                            <input
                                type="password"
                                placeholder="Re-enter new password"
                                v-model="form.password_confirmation"
                                autocomplete="new-password"
                                class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                            />
                        </label>
                        <button
                            type="submit"
                            class="flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-white disabled:opacity-60"
                            :disabled="loading"
                        >
                            <Icon v-if="loading" icon="mdi:loading" class="h-4 w-4 animate-spin" />
                            <span>{{ loading ? 'Updating...' : 'Update password' }}</span>
                        </button>
                    </form>
                </template>

                <div class="mt-6 text-xs text-muted">
                    Remembered your password?
                    <router-link class="text-primary" to="/blu/login">Back to login</router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Icon } from '../../icons';
import Repository from '../../repository';

export default {
    name: 'ResetPassword',
    components: { Icon },
    data() {
        return {
            token: '',
            email: '',
            checkingToken: true,
            tokenValid: false,
            tokenError: '',
            form: {
                password: '',
                password_confirmation: '',
            },
            loading: false,
            errorMessage: '',
        };
    },
    mounted() {
        const token = this.$route && this.$route.query ? this.$route.query.token : null;

        if (!token) {
            this.checkingToken = false;
            this.tokenValid = false;
            this.tokenError = 'Missing reset token.';
            return;
        }

        this.token = token;

        Repository.post('/api/check-reset-password-token', { token })
            .then((response) => {
                const data = response && response.data ? response.data : {};
                if (!data.success) {
                    this.tokenValid = false;
                    this.tokenError = data.text || 'This reset link is invalid or has expired.';
                    return;
                }

                this.tokenValid = true;
                this.email = data.result ? data.result.email : '';
            })
            .catch((error) => {
                this.tokenValid = false;
                this.tokenError = error && error.response && error.response.data
                    ? error.response.data.text
                    : 'This reset link is invalid or has expired.';
            })
            .finally(() => {
                this.checkingToken = false;
            });
    },
    methods: {
        submit() {
            if (this.loading) {
                return;
            }

            this.loading = true;
            this.errorMessage = '';

            return Repository.post('/api/reset-password-with-token', {
                token: this.token,
                password: this.form.password,
                password_confirmation: this.form.password_confirmation,
            })
                .then((response) => {
                    const data = response && response.data ? response.data : {};
                    if (!data.success) {
                        this.errorMessage = data.text || 'Failed to update password';
                        return;
                    }

                    this.$router.push('/blu/login');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to update password';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
};
</script>
