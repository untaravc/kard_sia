<template>
    <div class="content-wrapper">
        <div class="content-header">
            <h1 class="m-0 text-dark">Dashboard Analytics</h1>
        </div>
        <div class="content">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <presence-chart></presence-chart>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import PresenceChart from './RegistrationChart'

export default {
    data() {
        return {}
    },
    components: {
        PresenceChart
    }
}
</script>