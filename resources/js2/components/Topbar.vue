<template>
    <header class="topbar">
        <div class="topbar-left">
            <div class="page-title">{{ title }}</div>
            <div class="page-subtitle">{{ subtitle }}</div>
        </div>
        <div class="topbar-actions">
            <div class="search">
                <input type="text" placeholder="Search residents, stase, logs" />
            </div>
            <div class="user-chip">
                <div class="user-avatar">SA</div>
                <div class="user-meta">
                    <div class="user-name">Super Admin</div>
                    <div class="user-role">Cardio Admin</div>
                </div>
            </div>
        </div>
    </header>
</template>

<script>
export default {
    props: {
        title: {
            type: String,
            default: 'Dashboard',
        },
        subtitle: {
            type: String,
            default: 'Overview for this week',
        },
    },
};
</script>
