<template>
    <div class="min-h-screen bg-surface text-ink">
        <div class="mx-auto flex min-h-screen max-w-4xl items-center justify-center px-6 py-12">
            <div class="w-full rounded-3xl border border-border bg-panel p-8">
                <div class="text-sm font-semibold text-primary">Phone Login</div>
                <h1 class="mt-3 text-2xl font-semibold">Signing you in</h1>
                <p class="mt-3 text-sm text-muted">
                    {{ statusMessage }}
                </p>
                <div class="mt-6">
                    <div v-if="loading" class="text-xs text-muted">Please wait...</div>
                    <div v-else-if="errorMessage" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-600">
                        {{ errorMessage }}
                    </div>
                    <div v-else class="text-xs text-muted">
                        Redirecting to your dashboard.
                    </div>
                </div>
                <div class="mt-6 text-xs text-muted">
                    Need help?
                    <router-link class="text-primary" to="/blu/login">Back to login</router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Repository from '../../repository';

export default {
    name: 'LoginPhone',
    data() {
        return {
            loading: true,
            errorMessage: '',
            statusMessage: 'Validating your login link...'
        };
    },
    mounted() {
        const token = this.$route && this.$route.query ? this.$route.query.token : null;

        if (!token) {
            this.loading = false;
            this.errorMessage = 'Missing login token.';
            this.statusMessage = 'Login link is invalid.';
            return;
        }

        Repository.post('/api/check-login-phone-token', { token })
            .then((response) => {
                const data = response && response.data ? response.data : {};
                if (!data.success) {
                    this.errorMessage = data.text || 'Login failed.';
                    this.statusMessage = 'Login link is invalid.';
                    return;
                }

                const jwtToken = data.result ? data.result.token : null;
                if (jwtToken) {
                    localStorage.setItem('token', jwtToken);
                }

                this.statusMessage = 'Login success.';
                this.$router.push('/blu/dashboard');
            })
            .catch((error) => {
                const message = error && error.response && error.response.data
                    ? error.response.data.text
                    : 'Login failed.';
                this.errorMessage = message;
                this.statusMessage = 'Login link is invalid.';
            })
            .finally(() => {
                this.loading = false;
            });
    }
};
</script>
