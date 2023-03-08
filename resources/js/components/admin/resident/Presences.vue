<template>
    <div class="content-wrapper">
        <div class="content">
            <div class="row">
                <div class="col-12">
                    <div class="card mt-2">
                        <div class="card-header text-center">
                            <b>Resume Presensi</b>
                        </div>
                        <div class="card-body">
                            <div class="row my-2">
                                <div class="col">
                                    <span v-if="dataContent.resident">{{dataContent.resident.name}}</span>
                                </div>
                                <div class="col">
                                    <input type="month" v-model="filter.period" @change="loadDataContent" class="form-control">
                                </div>
                                <div class="col">

                                </div>
                            </div>
                            <vue-loader :active.sync="is_loading" :can-cancel="false" loader="dots" :is-full-page="false"></vue-loader>
                            <div class="row" v-for="(week, w) in dataContent.data">
                                <div class="col-md-2 px-1 fixed-col" v-for="(day, d) in week" v-if="d < 6">
                                    <div class="card" style="min-height: 150px">
                                        <div class="p-1">
                                            <span class="badge badge-light"> {{day.date | formatDay}} </span> <br>
                                            <small v-if="day.presence" :title="Checkin">
                                                <i class="fa fa-check-circle text-success"></i> {{day.presence.checkin | formatTime}}</small>
                                            <br>
                                            <small :title="act.activity.name" v-if="day.activities && act.activity" v-for="(act, a) in day.activities">
                                                <i class="fa fa-check-square text-primary"></i> {{act.activity.name | truncate(20)}}
                                                <br>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


<script>
export default {
    data() {
        return {
            base_url: '/sadmin/resume-presences',
            editMode: false,
            is_loading: false,
            dataContent: {},
            dataDetail: '',
            dataRaw: {
                image_url: ''
            },
            form: new form({
                id: '',
                email: '',
                password: '',
                name: '',
                status: '',
                year: '',
            }),
            filter: new form({
                period: '',
                student_id: this.$route.params.id
            })
        }
    },
    methods: {
        loadDataContent(page = 1) {
            this.filter.page = page;
            this.is_loading = true;
            axios.get(this.base_url, {params: this.filter})
                .then(({data}) => {
                    this.dataContent = data;
                    this.is_loading = false;
                })
        },
    },
    created() {
        this.loadDataContent();
    }
}
</script>
<style>
.fixed-col{
    min-width: 180px;
}
</style>
