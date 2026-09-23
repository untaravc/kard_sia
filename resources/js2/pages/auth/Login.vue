<template>
    <div class="min-h-screen bg-surface text-ink lg:grid lg:grid-cols-[1.05fr_0.95fr]">
        <!-- Visual panel: shows app.login-bg when configured, gradient otherwise. -->
        <section
            class="relative isolate flex flex-col justify-between overflow-hidden bg-primary px-6 py-10 text-white sm:px-10 lg:min-h-screen lg:px-14 lg:py-14">
            <img v-if="loginBg" :src="loginBg" alt="" class="absolute inset-0 -z-20 h-full w-full object-cover" />
            <div class="absolute inset-0 -z-10"
                :class="loginBg
                    ? 'bg-gradient-to-br from-primary/90 via-secondary/85 to-primary/95'
                    : 'bg-gradient-to-br from-primary via-secondary to-primary'"></div>

            <div class="flex items-center gap-3">
                <img v-if="appLogo" :src="appLogo" :alt="appName" class="h-9 w-9 rounded-lg object-contain" />
                <span v-else
                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 text-sm font-semibold">
                    {{ appInitial }}
                </span>
                <span class="text-[11px] font-medium uppercase tracking-[0.32em] text-white/70">{{ appName }}</span>
            </div>

            <div class="my-12 max-w-lg lg:my-0">
                <h1 class="text-4xl font-semibold leading-[1.05] tracking-tight sm:text-5xl lg:text-6xl">
                    {{ appName }}.
                </h1>
                <div class="mt-6 space-y-1.5 text-sm leading-relaxed text-white/80 sm:text-base">
                    <p v-for="(line, index) in loginDescLines" :key="index">{{ line }}</p>
                </div>
            </div>

            <p class="text-xs text-white/50">&copy; {{ year }} {{ appName }}</p>
        </section>

        <!-- Form panel -->
        <section class="flex items-center justify-center px-6 py-12 sm:px-10 lg:px-14">
            <div class="w-full max-w-md">
                <h2 class="text-2xl font-semibold tracking-tight">Sign in</h2>
                <p class="mt-1.5 text-sm text-muted">Welcome back. Choose how you want to continue.</p>

                <div class="mt-7 grid grid-cols-3 gap-1 rounded-xl bg-surface-muted p-1 text-xs font-medium">
                    <button v-for="tab in loginTabs" :key="tab.value" type="button"
                        class="flex items-center justify-center gap-1.5 rounded-lg px-2 py-2 transition"
                        :class="loginMethod === tab.value
                            ? 'bg-panel text-primary shadow-sm'
                            : 'text-muted hover:text-ink'"
                        @click="setLoginMethod(tab.value)">
                        <Icon :icon="tab.icon" class="h-4 w-4" />
                        <span>{{ tab.label }}</span>
                    </button>
                </div>

                <form class="mt-6 grid min-h-[248px] content-start gap-4" @submit.prevent="submit">
                    <template v-if="loginMethod === 'password'">
                        <label class="grid gap-2 text-sm">
                            <span class="text-muted">Email</span>
                            <span class="relative block">
                                <Icon icon="mdi:email-outline"
                                    class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
                                <input type="email" placeholder="name@domain.com" autocomplete="username"
                                    v-model.trim="form.email"
                                    class="w-full rounded-xl border border-border bg-panel py-2.5 pl-10 pr-4 text-sm transition focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20" />
                            </span>
                        </label>
                        <label class="grid gap-2 text-sm">
                            <span class="text-muted">Password</span>
                            <span class="relative block">
                                <Icon icon="mdi:lock-outline"
                                    class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
                                <input :type="showPassword ? 'text' : 'password'" placeholder="Enter your password"
                                    autocomplete="current-password" v-model="form.password"
                                    class="w-full rounded-xl border border-border bg-panel py-2.5 pl-10 pr-11 text-sm transition focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20" />
                                <button type="button"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted transition hover:text-ink"
                                    :aria-label="showPassword ? 'Hide password' : 'Show password'"
                                    @click="showPassword = !showPassword">
                                    <Icon :icon="showPassword ? 'mdi:eye-off-outline' : 'mdi:eye-outline'"
                                        class="h-4 w-4" />
                                </button>
                            </span>
                        </label>
                        <div v-if="isDemo" class="rounded-xl border border-dashed border-border bg-surface-muted p-3">
                            <span class="text-xs text-muted">Demo accounts</span>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <button v-for="account in demoAccountList" :key="account.type" type="button"
                                    class="rounded-lg border border-border bg-panel px-3 py-1.5 text-xs text-muted transition hover:border-primary/40 hover:text-primary disabled:opacity-60"
                                    :disabled="loading" @click="loginAsDemo(account.type)">
                                    {{ account.label }}
                                </button>
                            </div>
                        </div>
                    </template>

                    <template v-else-if="loginMethod === 'email'">
                        <label class="grid gap-2 text-sm">
                            <span class="text-muted">Email</span>
                            <span class="relative block">
                                <Icon icon="mdi:email-outline"
                                    class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
                                <input type="email" placeholder="name@domain.com" autocomplete="username"
                                    v-model.trim="emailForm.email"
                                    class="w-full rounded-xl border border-border bg-panel py-2.5 pl-10 pr-4 text-sm transition focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20" />
                            </span>
                        </label>
                        <p class="text-xs leading-relaxed text-muted">
                            We will email you a one-time link that signs you in without a password.
                        </p>
                    </template>

                    <template v-else-if="loginMethod === 'phone'">
                        <label class="grid gap-2 text-sm">
                            <span class="text-muted">Phone</span>
                            <span class="relative block">
                                <Icon icon="mdi:phone-outline"
                                    class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
                                <input type="tel" placeholder="+62 812 3456 7890" v-model.trim="phoneForm.phone"
                                    class="w-full rounded-xl border border-border bg-panel py-2.5 pl-10 pr-4 text-sm transition focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20" />
                            </span>
                        </label>
                    </template>

                    <template v-else>
                        <p class="text-xs leading-relaxed text-muted">
                            Continue with your institutional Google account. You will come back here once approved.
                        </p>
                    </template>

                    <p v-if="errorMessage"
                        class="flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-600">
                        <Icon icon="mdi:alert-circle-outline" class="mt-px h-4 w-4 flex-none" />
                        <span>{{ errorMessage }}</span>
                    </p>
                    <p v-if="successMessage"
                        class="flex items-start gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
                        <Icon icon="mdi:check-circle-outline" class="mt-px h-4 w-4 flex-none" />
                        <span>{{ successMessage }}</span>
                    </p>

                    <button type="submit"
                        class="mt-1 flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-white transition hover:bg-secondary disabled:opacity-60"
                        :disabled="loading">
                        <Icon v-if="loading" icon="mdi:loading" class="h-4 w-4 animate-spin" />
                        <span>{{ submitLabel }}</span>
                    </button>

                    <div class="text-center text-xs leading-relaxed text-muted" v-if="loginMethod === 'password'">
                        <router-link class="font-medium text-primary hover:underline" to="/blu/forgot-password">
                            Forgot your password?
                        </router-link>
                        <span class="block">
                            Don't have an account?
                            <router-link class="font-medium text-primary hover:underline" to="/blu/register">
                                Register
                            </router-link>
                        </span>
                    </div>
                </form>
            </div>
        </section>
    </div>
</template>

<script>
import { initWebFcm } from '../../firebase/messaging';
import { Icon } from '../../icons';
import Repository from '../../repository';
import { useAppSettingsStore } from '../../stores/appSettings';

export default {
    name: 'Login',
    components: { Icon },
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
            showPassword: false,
            errorMessage: '',
            successMessage: '',
            isDemo: false,
            appName: 'BLU',
            appLogo: '',
            loginBg: '',
            loginDesc: 'Code Blue is a call to act without delay.\nBLU prepares cardiology residents for decisive moments.\nBecause every heartbeat matters.',
            loginTabs: [
                { value: 'password', label: 'Password', icon: 'mdi:lock-outline' },
                { value: 'email', label: 'Email link', icon: 'mdi:email-fast-outline' },
                { value: 'sso', label: 'SSO', icon: 'mdi:google' },
            ],
            // Matches the fixed demo accounts seeded by UsersTableSeeder.
            demoAccounts: {
                admin: { email: 'admin@blu.test', password: 'password' },
                student: { email: 'student@blu.test', password: 'password' },
                lecture: { email: 'lecture@blu.test', password: 'password' },
            },
            demoAccountList: [
                { type: 'admin', label: 'Admin' },
                { type: 'student', label: 'Student' },
                { type: 'lecture', label: 'Lecture' },
            ],
        };
    },
    computed: {
        loginDescLines() {
            return this.loginDesc.split('\n').filter((line) => line.trim() !== '');
        },
        appInitial() {
            return (this.appName || 'B').trim().charAt(0).toUpperCase();
        },
        year() {
            return new Date().getFullYear();
        },
        submitLabel() {
            if (this.loginMethod === 'password') {
                return this.loading ? 'Signing in...' : 'Sign in';
            }
            if (this.loginMethod === 'email') {
                return this.loading ? 'Sending...' : 'Send login link';
            }
            if (this.loginMethod === 'phone') {
                return this.loading ? 'Sending...' : 'Send login code';
            }

            return 'Continue with SSO';
        },
    },
    methods: {
        fetchAppSettings() {
            const appSettingsStore = useAppSettingsStore();
            return Promise.all([
                appSettingsStore.fetchSetting('app.name').then((value) => {
                    if (value) {
                        this.appName = value;
                    }
                }),
                appSettingsStore.fetchSetting('app.login-desc').then((value) => {
                    if (value) {
                        this.loginDesc = value;
                    }
                }),
                appSettingsStore.fetchSetting('app.login-bg').then((value) => {
                    if (value) {
                        this.loginBg = value;
                    }
                }),
                appSettingsStore.fetchSetting('app.logo').then((value) => {
                    if (value) {
                        this.appLogo = value;
                    }
                }),
            ]);
        },
        fetchAppConfig() {
            return Repository.get('/api/app-config')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.isDemo = !!result && result.env === 'demo';
                })
                .catch(() => {
                    this.isDemo = false;
                });
        },
        loginAsDemo(type) {
            const account = this.demoAccounts[type];
            if (!account) {
                return;
            }

            this.loginMethod = 'password';
            this.form.email = account.email;
            this.form.password = account.password;
            this.login();
        },
        setLoginMethod(method) {
            this.loginMethod = method;
            this.errorMessage = '';
            this.successMessage = '';
        },
        submit() {
            if (this.loading) {
                return;
            }

            if (this.loginMethod === 'password') {
                this.login();
                return;
            }

            if (this.loginMethod === 'email') {
                this.requestLoginEmail();
                return;
            }

            if (this.loginMethod === 'phone') {
                this.requestLoginPhone();
                return;
            }

            this.requestLoginSso();
        },
        login() {
            this.loading = true;
            this.errorMessage = '';
            this.successMessage = '';

            return Repository.post('/api/login', this.form)
                .then((response) => {
                    const data = response && response.data ? response.data : {};
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

                    const authType = data.result ? data.result.auth_type : null;
                    if (authType === 'registration') {
                        this.$router.push('/reg/index');
                        return;
                    }

                    this.$router.push('/blu/dashboard');
                })
                .catch((error) => {
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
        this.fetchAppConfig();
        this.fetchAppSettings();

        const token = this.$route && this.$route.query ? this.$route.query.token : null;
        const error = this.$route && this.$route.query ? this.$route.query.error : null;

        if (error) {
            this.errorMessage = Array.isArray(error) ? error[0] : error;
        }

        if (token) {
            localStorage.setItem('token', token);
            const authType = this.$route && this.$route.query ? this.$route.query.auth_type : null;
            if (authType === 'registration') {
                this.$router.replace('/reg/index');
                return;
            }

            this.$router.replace('/blu/dashboard');
        }
    },
};
</script>
