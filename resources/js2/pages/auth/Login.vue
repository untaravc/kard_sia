<template>
    <div class="min-h-screen bg-surface text-ink">
        <div class="mx-auto flex min-h-screen max-w-6xl items-center px-6 py-12">
            <div class="grid w-full gap-10 lg:grid-cols-[1.1fr_0.9fr]">
                <div class="rounded-3xl bg-gradient-to-br from-surface to-ext p-10">
                    <h1 class="mt-3 text-5xl font-semibold tracking-tight text-sky-500 sm:text-6xl">
                        BLU.
                    </h1>
                    <p class="mt-4 text-sm text-muted leading-relaxed">
                        <span class="block">Code Blue is a call to act without delay.</span>
                        <span class="block">BLU prepares cardiology residents for decisive moments.</span>
                        <span class="block">Because every heartbeat matters.</span>
                    </p>
                </div>
                <div class="rounded-3xl border border-border bg-panel p-8">
                    <div class="text-lg font-semibold">Sign in</div>
                    <p class="mt-2 text-sm text-muted">Use your admin email and password.</p>
                    <form class="mt-6 grid gap-4">
                        <label class="grid gap-2 text-sm">
                            <span class="text-muted">Email</span>
                            <input type="email" placeholder="admin@kardio.id" v-model.trim="form.email"
                                class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
                        </label>
                        <label class="grid gap-2 text-sm">
                            <span class="text-muted">Password</span>
                            <input type="password" placeholder="Enter your password" v-model="form.password"
                                class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
                        </label>
                        <p v-if="errorMessage"
                            class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-600">
                            {{ errorMessage }}
                        </p>
                        <button type="button"
                            class="mt-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-white"
                            :disabled="loading" @click="login">
                            {{ loading ? 'Signing in...' : 'Sign in' }}
                        </button>
                        <div class="text-center text-xs text-muted">
                            <router-link class="text-primary" to="/cblu/forgot-password">Forgot your
                                password?</router-link>
                            Contact IT support if you need help.
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Repository from '../../repository';

export default {
    name: 'Login',
    data() {
        return {
            form: {
                email: '',
                password: '',
            },
            loading: false,
            errorMessage: '',
        };
    },
    methods: {
        login() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.post('/api/login', this.form)
                .then((response) => {
                    const data = response && response.data ? response.data : {};
                    console.log(data)
                    if (!data.success) {
                        this.errorMessage = data.text || 'Login failed';
                        return;
                    }

                    const token = data.result ? data.result.token : null;
                    if (token) {
                        localStorage.setItem('token', token);
                    }

                    this.$router.push('/cblu/dashboard');
                })
                .catch((error) => {
                    console.log("ERROR", error)
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Login failed';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
};
</script>
