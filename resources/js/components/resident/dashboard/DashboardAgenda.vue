<template>
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Agenda Hari Ini</h3>
            <div class="card-tools">
                <router-link to="/resident/activity" class="btn btn-sm btn-th-dark">
                    <i class="fa fa-calendar-alt"></i>
                    Semua agenda
                </router-link>
            </div>
        </div>
        <div class="card-body p-0">
            <table v-if="schedules && schedules[0]" class="table">
                <tr v-for="(sch, s) in schedules" :key="s">
                    <td>
                        {{ sch.name }}<br>
                        <small>
                            <b v-if="sch.speaker">{{ sch.speaker }}:</b> {{ sch.title }} <br>
                            <i v-if="sch.absence">
                                Anda telah presensi pada
                                {{ sch.absence.created_at | formatDateTime }}
                            </i>
                        </small>
                    </td>
                    <td class="text-right">
                        <a :href="'/presensi/' + sch.id" v-if="!sch.absence"
                           class="btn btn-wd btn-sm btn-success">Presensi</a>
                    </td>
                </tr>
            </table>

            <div v-if="schedules && !schedules[0]" class="p-3">
                <i>Tidak ada agenda</i>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data(){
        return {
            schedules:[],
        }
    },
    methods:{
        loadSchedule() {
            axios.get('/cmsr/get-schedule')
                .then((response) => {
                    this.schedules = response.data
                })
        },
    },
    created() {
        this.loadSchedule()
    }
}
</script>
