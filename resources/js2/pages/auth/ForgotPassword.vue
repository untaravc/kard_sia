<template>
    <div class="min-h-screen bg-surface text-ink">
        <div class="mx-auto flex min-h-screen max-w-4xl items-center justify-center px-6 py-12">
            <div class="w-full rounded-3xl border border-border bg-panel p-8">
                <div class="text-sm font-semibold text-primary">Reset Access</div>
                <h1 class="mt-3 text-2xl font-semibold">Forgot your password?</h1>
                <p class="mt-3 text-sm text-muted">
                    Enter your email address or phone number and we will send reset instructions.
                </p>
                <form class="mt-6 grid gap-4">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Email or phone</span>
                        <input
                            type="text"
                            placeholder="admin@kardio.id or +62..."
                            class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <button
                        type="submit"
                        class="rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-white"
                    >
                        Send reset link
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
export default {
    name: 'ForgotPassword',
};
</script>
