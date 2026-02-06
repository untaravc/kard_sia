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
                    <p class="mt-2 text-sm text-muted">Choose a sign-in method.</p>
                    <div class="mt-5 border-b border-border">
                        <div class="flex flex-wrap gap-2 text-xs font-medium">
                            <button type="button"
                                class="relative -mb-px px-3 py-2 transition"
                                :class="loginMethod === 'password' ? 'border-b-2 border-primary text-primary' : 'border-b-2 border-transparent text-muted hover:text-ink'"
                                @click="setLoginMethod('password')">
                                Email &amp; Password
                            </button>
                            <button type="button"
                                class="relative -mb-px px-3 py-2 transition"
                                :class="loginMethod === 'email' ? 'border-b-2 border-primary text-primary' : 'border-b-2 border-transparent text-muted hover:text-ink'"
                                @click="setLoginMethod('email')">
                                Email Link
                            </button>
                            <button type="button"
                                class="relative -mb-px px-3 py-2 transition"
                                :class="loginMethod === 'phone' ? 'border-b-2 border-primary text-primary' : 'border-b-2 border-transparent text-muted hover:text-ink'"
                                @click="setLoginMethod('phone')">
                                Phone
                            </button>
                            <button type="button"
                                class="relative -mb-px px-3 py-2 transition"
                                :class="loginMethod === 'sso' ? 'border-b-2 border-primary text-primary' : 'border-b-2 border-transparent text-muted hover:text-ink'"
                                @click="setLoginMethod('sso')">
                                Single Sign On
                            </button>
                        </div>
                    </div>
                    <form class="mt-6 grid min-h-[260px] content-start gap-4">
                        <template v-if="loginMethod === 'password'">
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
                        </template>
                        <template v-else-if="loginMethod === 'email'">
                            <label class="grid gap-2 text-sm">
                                <span class="text-muted">Email</span>
                                <input type="email" placeholder="admin@kardio.id" v-model.trim="emailForm.email"
                                    class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
                            </label>
                        </template>
                        <template v-else-if="loginMethod === 'phone'">
                            <label class="grid gap-2 text-sm">
                                <span class="text-muted">Phone</span>
                                <input type="tel" placeholder="+62 812 3456 7890" v-model.trim="phoneForm.phone"
                                    class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
                            </label>
                        </template>
                        <template v-else>
                            
                        </template>
                        <p v-if="errorMessage"
                            class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-600">
                            {{ errorMessage }}
                        </p>
                        <p v-if="successMessage"
                            class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
                            {{ successMessage }}
                        </p>
                        <button v-if="loginMethod === 'password'" type="button"
                            class="mt-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-white"
                            :disabled="loading" @click="login">
                            {{ loading ? 'Signing in...' : 'Sign in' }}
                        </button>
                        <button v-else-if="loginMethod === 'email'" type="button"
                            class="mt-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-white"
                            :disabled="loading" @click="requestLoginEmail">
                            {{ loading ? 'Sending...' : 'Send login link' }}
                        </button>
                        <button v-else-if="loginMethod === 'phone'" type="button"
                            class="mt-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-white"
                            :disabled="loading" @click="requestLoginPhone">
                            {{ loading ? 'Sending...' : 'Send login code' }}
                        </button>
                        <button v-else type="button"
                            class="mt-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-white"
                            @click="requestLoginSso">
                            Continue with SSO
                        </button>
                        <div class="text-center text-xs text-muted" v-if="loginMethod === 'password'">
                            <router-link class="text-primary" to="/blu/forgot-password">Forgot your
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
import { initWebFcm } from '../../firebase/messaging';
import Repository from '../../repository';

export default {
    name: 'Login',
    data() {
        return {
            loginMethod: 'password',
            form: {
                email: '',
                password: '',
            },
            emailForm: {
                email: '',
            },
            phoneForm: {
                phone: '',
            },
            loading: false,
            errorMessage: '',
            successMessage: '',
        };
    },
    methods: {
        setLoginMethod(method) {
            this.loginMethod = method;
            this.errorMessage = '';
            this.successMessage = '';
        },
        login() {
            this.loading = true;
            this.errorMessage = '';
            this.successMessage = '';

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
                        initWebFcm().catch(() => {
                            // Ignore FCM init errors after login.
                        });
                    }

                    this.$router.push('/blu/dashboard');
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
        requestLoginEmail() {
            this.loading = true;
            this.errorMessage = '';
            this.successMessage = '';

            return Repository.post('/api/login-email', this.emailForm)
                .then((response) => {
                    const data = response && response.data ? response.data : {};
                    if (!data.success) {
                        this.errorMessage = data.text || 'Request failed';
                        return;
                    }

                    this.successMessage = data.text || 'Login link sent';
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
        requestLoginPhone() {
            this.loading = true;
            this.errorMessage = '';
            this.successMessage = '';

            return Repository.post('/api/login-phone', this.phoneForm)
                .then((response) => {
                    const data = response && response.data ? response.data : {};
                    if (!data.success) {
                        this.errorMessage = data.text || 'Request failed';
                        return;
                    }

                    this.successMessage = data.text || 'Login link sent';
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
        requestLoginSso() {
            this.errorMessage = '';
            this.successMessage = '';
            window.location.href = '/api/login-google/redirect';
        },
    },
    mounted() {
        const token = this.$route && this.$route.query ? this.$route.query.token : null;
        const error = this.$route && this.$route.query ? this.$route.query.error : null;

        if (error) {
            this.errorMessage = Array.isArray(error) ? error[0] : error;
        }

        if (token) {
            localStorage.setItem('token', token);
            this.$router.replace('/blu/dashboard');
        }
    },
};
</script>
