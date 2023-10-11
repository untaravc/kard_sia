<template>
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Ujian Online</h3>
        </div>
        <div class="card-body p-0">
            <table v-if="exams && exams[0]" class="table">
                <tr v-for="(ex, e) in exams" :key="e">
                    <td>
                        <span v-if="ex.exam">{{ ex.exam.name }}</span>
                        <span v-if="ex.exam" class="badge badge-info">{{ ex.exam.duration }} min</span>
                    </td>
                    <td class="text-right">
                        <a v-if="ex.exam" :href="ex.exam.direct_link"
                           class="btn btn-wd btn-sm btn-success">Mulai</a>
                    </td>
                </tr>
            </table>

            <div v-if="exams && !exams[0]" class="p-3">
                <i>Tidak ada jadwal</i>
            </div>
        </div>
    </div>
</template>


<script>
export default {
    data(){
        return {
            exams:[],
        }
    },
    methods:{
        loadExam() {
            axios.get('/cmsr/exams')
                .then(({data}) => {
                    if (data.status) {
                        this.exams = data.data
                    }
                })
        },
    },
    created() {
        this.loadExam()
    }
}
</script>
