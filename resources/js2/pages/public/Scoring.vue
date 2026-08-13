<template>
    <div class="min-h-screen bg-surface text-ink">
        <div class="mx-auto flex min-h-screen max-w-4xl items-center justify-center px-6 py-12">
            <div class="w-full rounded-3xl border border-border bg-panel p-8">
                <div class="text-sm font-semibold text-primary">Scoring Link</div>
                <h1 class="mt-3 text-2xl font-semibold">Opening scoring form</h1>
                <p class="mt-3 text-sm text-muted">
                    {{ statusMessage }}
                </p>
                <div class="mt-6">
                    <div v-if="loading" class="text-xs text-muted">Please wait...</div>
                    <div v-else-if="errorMessage" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-600">
                        {{ errorMessage }}
                    </div>
                    <div v-else class="text-xs text-muted">
                        Redirecting to the scoring form.
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
    name: 'PubScoring',
    data() {
        return {
            loading: true,
            errorMessage: '',
            statusMessage: 'Validating your scoring link...',
        };
    },
    mounted() {
        const query = this.$route && this.$route.query ? this.$route.query : {};
        const llt = query.llt;
        const ostt = query.ostt;

        if (!llt || !ostt) {
            this.loading = false;
            this.errorMessage = 'Missing scoring link parameters.';
            this.statusMessage = 'Scoring link is invalid.';
            return;
        }

        Repository.post('/api/pub-scoring-auth', { llt, ostt })
            .then((response) => {
                const data = response && response.data ? response.data : {};
                if (!data.success) {
                    this.errorMessage = data.text || 'Failed to open scoring link.';
                    this.statusMessage = 'Scoring link is invalid.';
                    return;
                }

                const result = data.result || {};
                if (result.token) {
                    localStorage.setItem('token', result.token);
                }

                this.statusMessage = 'Link valid.';
                this.$router.replace(`/blu/task-scoring/${result.open_stase_task_id}`);
            })
            .catch((error) => {
                const message = error && error.response && error.response.data
                    ? error.response.data.text
                    : 'Failed to open scoring link.';
                this.errorMessage = message;
                this.statusMessage = 'Scoring link is invalid.';
            })
            .finally(() => {
                this.loading = false;
            });
    },
};
</script>
