<template>
    <div class="min-h-screen bg-surface text-ink">
        <div class="mx-auto flex min-h-screen max-w-6xl items-center px-6 py-12">
            <div class="grid w-full gap-10 lg:grid-cols-[1.1fr_0.9fr]">
                <div class="rounded-3xl bg-gradient-to-br from-surface to-ext p-10">
                    <h1 class="mt-3 text-5xl font-semibold tracking-tight text-sky-500 sm:text-6xl">
                        {{ appName }}.
                    </h1>
                    <p class="mt-4 text-sm leading-relaxed text-muted">
                        <span class="block">Code Blue is a call to act without delay.</span>
                        <span class="block">{{ appName }} prepares cardiology residents for decisive moments.</span>
                        <span class="block">Because every heartbeat matters.</span>
                    </p>
                </div>

                <div class="rounded-3xl border border-border bg-panel p-8">
                    <div class="text-lg font-semibold">Create account</div>
                    <p class="mt-2 text-sm text-muted">Register to continue.</p>

                    <div v-if="errorMessage" class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ errorMessage }}
                    </div>
                    <div v-if="successMessage" class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ successMessage }}
                    </div>

                    <form class="mt-6 grid content-start gap-4" @submit.prevent="register">
                        <label class="grid gap-2 text-sm">
                            <span class="text-muted">Name</span>
                            <input type="text" placeholder="Your name" v-model.trim="form.name"
                                class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                                :class="fieldErrors.name ? 'border-red-300 focus:ring-red-500/20' : ''" />
                            <span v-if="fieldErrors.name" class="text-xs text-red-600">{{ fieldErrors.name }}</span>
                        </label>

                        <label class="grid gap-2 text-sm">
                            <span class="text-muted">Email</span>
                            <input type="email" placeholder="admin@kardio.id" v-model.trim="form.email"
                                class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                                :class="fieldErrors.email ? 'border-red-300 focus:ring-red-500/20' : ''" />
                            <span v-if="fieldErrors.email" class="text-xs text-red-600">{{ fieldErrors.email }}</span>
                        </label>

                        <label class="grid gap-2 text-sm">
                            <span class="text-muted">Password</span>
                            <input type="password" placeholder="Create a password" v-model="form.password"
                                class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                                :class="fieldErrors.password ? 'border-red-300 focus:ring-red-500/20' : ''" />
                            <span v-if="fieldErrors.password" class="text-xs text-red-600">{{ fieldErrors.password }}</span>
                        </label>

                        <label class="grid gap-2 text-sm">
                            <span class="text-muted">Confirm password</span>
                            <input type="password" placeholder="Repeat your password" v-model="form.password_confirmation"
                                class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                                :class="fieldErrors.password_confirmation ? 'border-red-300 focus:ring-red-500/20' : ''" />
                            <span v-if="fieldErrors.password_confirmation" class="text-xs text-red-600">{{ fieldErrors.password_confirmation }}</span>
                        </label>

                        <button type="submit"
                            class="mt-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-white disabled:opacity-60"
                            :disabled="loading">
                            {{ loading ? 'Registering...' : 'Register' }}
                        </button>

                        <div class="text-center text-xs text-muted">
                            Already have an account?
                            <router-link class="text-primary" to="/blu/login">Sign in</router-link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Repository from '../repository';
import { initWebFcm } from '../firebase/messaging';
import { useAppSettingsStore } from '../stores/appSettings';

export default {
    name: 'Register',
    data() {
        return {
            form: {
                name: '',
                email: '',
                password: '',
                password_confirmation: '',
            },
            loading: false,
            errorMessage: '',
            successMessage: '',
            fieldErrors: {},
            appName: 'BLU',
        };
    },
    created() {
        useAppSettingsStore().fetchSetting('app.name').then((value) => {
            if (value) {
                this.appName = value;
            }
        });
    },
    methods: {
        normalizeErrors(error) {
            const responseData = error && error.response && error.response.data ? error.response.data : null;
            const laravelErrors = responseData && responseData.errors ? responseData.errors : null;

            if (laravelErrors && typeof laravelErrors === 'object') {
                const mapped = {};
                Object.keys(laravelErrors).forEach((key) => {
                    const messages = laravelErrors[key];
                    mapped[key] = Array.isArray(messages) ? messages[0] : String(messages);
                });
                return mapped;
            }

            return {};
        },
        register() {
            this.loading = true;
            this.errorMessage = '';
            this.successMessage = '';
            this.fieldErrors = {};

            return Repository.post('/api/register', this.form)
                .then((response) => {
                    const data = response && response.data ? response.data : {};
                    if (!data.success) {
                        this.errorMessage = data.text || 'Register failed';
                        this.fieldErrors = data.errors || {};
                        return;
                    }

                    const token = data.result ? data.result.token : null;
                    if (token) {
                        localStorage.setItem('token', token);
                        initWebFcm().catch(() => {
                            // Ignore FCM init errors after register.
                        });
                    }

                    this.successMessage = data.text || 'Register success';
                    this.$router.push('/reg/index');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Register failed';
                    this.errorMessage = message;
                    this.fieldErrors = this.normalizeErrors(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
};
</script>
