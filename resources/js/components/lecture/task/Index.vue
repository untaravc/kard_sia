<template>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark"><i class="fa fa-chalkboard-teacher"></i> Lembar Penilaian
                            <span v-if="dataDetail.stase_task">{{ dataDetail.stase_task.name }}</span>
                        </h1>
                        <i class="ml-5" v-if="dataDetail.title">Judul : {{ dataDetail.title }}</i>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h1 class="card-title">Poin Penilaian</h1>
                        </div>
                        <div class="card-body table-responsive">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group"
                                         v-if="dataDetail.task
                                            && dataDetail.task.desc === 'chief'">
                                        <label>Pertanyaan</label>
                                        <textarea rows="10" class="form-control" v-model="form.note"></textarea>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group" v-if="i < (dataDetail.stase_task_log_point.length / 2) "
                                         v-for="(data, i) in dataDetail.stase_task_log_point" :key="i">
                                        <div class="card-body">
                                            <p class="mb-0">{{ data.order }}. {{ data.task_detail.name }}</p>
                                            <vue-slider
                                                @change="processPoint(data.order, data.id, formRaw.no[data.order-1])"
                                                v-model="formRaw.no[data.order-1]"
                                                ref="slider"
                                                value="50"
                                                v-bind="options"
                                                class="ml-4"
                                            ></vue-slider>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group" v-if="(i + 0.1) > (dataDetail.stase_task_log_point.length / 2) "
                                         v-for="(data, i) in dataDetail.stase_task_log_point" :key="i">
                                        <div class="card-body">
                                            <p class="mb-0">{{ data.order }}. {{ data.task_detail.name }}</p>
                                            <vue-slider
                                                @change="processPoint(data.order, data.id, formRaw.no[data.order-1])"
                                                v-model="formRaw.no[data.order-1]"
                                                ref="slider"
                                                value="50"
                                                v-bind="options"
                                                class="ml-4"
                                            ></vue-slider>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group"
                                 v-if="dataDetail.task
                                 && dataDetail.task.desc !== 'chief'">
                                <label>Catatan</label>
                                <textarea class="form-control" v-model="form.note"></textarea>
                            </div>
                        </div>

                        <div class="card-footer">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status"
                                  aria-hidden="true"></span>
                            <button :disabled="disable" class="btn btn-success" @click="submitData()">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
input[type=radio] {
    /*border: 0px;*/
    width: 1em;
    height: 1em;
}
</style>

<script>
export default {
    data() {
        return {
            base_url: '/cmsd/generate-task-log-detail',
            dataDetail: '',
            disable: false,
            form: new form({
                stase_task_log_id: '',
                open_stase_task_id: this.$route.params.open_stase_task_id,
                no: [],
                note: '',
            }),
            params: {
                open_stase_task_id: this.$route.params.open_stase_task_id,
            },
            formRaw: new form({
                no: [],
            }),
            options: {
                dotSize: 25,
                min: 78,
                max: 100,
                interval: 2,
                marks: true
            },
        }
    },
    methods: {
        loadData() {
            axios.get(this.base_url, {params: this.params})
                .then(({data}) => {
                    this.form.note = data.note;
                    this.dataDetail = data;
                    this.form.stase_task_log_id = data.id

                    let score = data.stase_task_log_point.map(function (item) {
                        return item.score;
                    });
                    this.formRaw.no = score
                })
        },
        submitData() {
            this.disable = true;
            axios.post('/cmsd/stase-task-logs-update-score/' + this.form.stase_task_log_id, {
                params: this.form,
            })
                .then(({data}) => {
                    Fire.$emit('ModalSuccess');
                    this.$router.push('/dosen')
                }).catch(({response}) => {
                console.log(response)
            })
        },
        popSuccessSwal() {
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: 'Success',
                showConfirmButton: false,
                timer: 1000,
            })
        },
        processPoint(i, id, val) {
            this.form.no[i] = id + ',' + val
        },
    },
    created() {
        this.$Progress.start();
        this.loadData();
        Fire.$on('ModalSuccess', () => {
            this.popSuccessSwal();
            this.$Progress.finish();
        })
    },
    mounted() {
        this.$Progress.finish()
    },
    components: {}
}
</script>
