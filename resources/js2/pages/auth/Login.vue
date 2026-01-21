<template>
    <div class="min-h-screen bg-surface text-ink">
        <div class="mx-auto flex min-h-screen max-w-6xl items-center px-6 py-12">
            <div class="grid w-full gap-10 lg:grid-cols-[1.1fr_0.9fr]">
                <div class="rounded-3xl bg-gradient-to-br from-surface to-ext p-10">
                    <div class="text-sm font-semibold text-primary">Kardio Admin</div>
                    <h1 class="mt-3 text-3xl font-semibold">Welcome back.</h1>
                    <p class="mt-4 text-muted">
                        Sign in to manage rotations, exams, and resident activities.
                    </p>
                    <div class="mt-8 grid gap-4 text-sm text-muted">
                        <div class="rounded-2xl bg-panel px-4 py-3">
                            Fast access to daily schedules and logbook reviews.
                        </div>
                        <div class="rounded-2xl bg-panel px-4 py-3">
                            Secure session with role-based navigation.
                        </div>
                    </div>
                </div>
                <div class="rounded-3xl border border-border bg-panel p-8">
                    <div class="text-lg font-semibold">Sign in</div>
                    <p class="mt-2 text-sm text-muted">Use your admin email and password.</p>
                    <form class="mt-6 grid gap-4">
                        <label class="grid gap-2 text-sm">
                            <span class="text-muted">Email</span>
                            <input
                                type="email"
                                placeholder="admin@kardio.id"
                                class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                            />
                        </label>
                        <label class="grid gap-2 text-sm">
                            <span class="text-muted">Password</span>
                            <input
                                type="password"
                                placeholder="Enter your password"
                                class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                            />
                        </label>
                        <button
                            type="submit"
                            class="mt-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-white"
                        >
                            Sign in
                        </button>
                        <div class="text-center text-xs text-muted">
                            <router-link class="text-primary" to="/cblu/forgot-password">Forgot your password?</router-link>
                            Contact IT support if you need help.
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Login',
};
</script>
