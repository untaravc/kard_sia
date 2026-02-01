<template>
    <div class="min-h-screen bg-surface text-ink">
        <div class="mx-auto flex min-h-screen max-w-4xl items-center justify-center px-6 py-12">
            <div class="w-full rounded-3xl border border-border bg-panel p-10 text-center">
                <div class="text-sm font-semibold text-primary">404</div>
                <h1 class="mt-3 text-3xl font-semibold">Page not found</h1>
                <p class="mt-3 text-muted">
                    The page you are looking for does not exist or has been moved.
                </p>
                <div class="mt-6 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                    <router-link
                        class="rounded-xl bg-primary px-5 py-2.5 text-sm font-medium text-white"
                        to="/blu"
                    >
                        Back to dashboard
                    </router-link>
                    <router-link
                        class="rounded-xl border border-primary px-5 py-2.5 text-sm font-medium text-primary"
                        to="/blu-login"
                    >
                        Go to login
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'NotFound',
};
</script>
