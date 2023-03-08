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
                                <div class="col-md-6">
                                    <h5 class="font-weight-bold">Judul, latar belakang dan masalah penelitian</h5>
                                    <div class="card" v-if="data.order < 8"
                                         v-for="(data, i) in dataDetail.stase_task_log_point" :key="data.order">
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

                                    <h5 class="font-weight-bold">Pertanyaan penelitian, hipotesis dan tujuan</h5>
                                    <div class="card" v-if="data.order > 7 && data.order < 10"
                                         v-for="(data, i) in dataDetail.stase_task_log_point" :key="data.order">
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

                                    <h5 class="font-weight-bold">Rancangan penelitian dan sampel</h5>
                                    <div class="card" v-if="data.order > 9 && data.order < 15"
                                         v-for="(data, i) in dataDetail.stase_task_log_point" :key="data.order">
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

                                <div class="col-md-6">
                                    <h5 class="font-weight-bold">Cara penelitian dan analisis data</h5>
                                    <div class="card" v-if="data.order > 14 && data.order < 22"
                                         v-for="(data, i) in dataDetail.stase_task_log_point" :key="data.order">
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

                                    <h5 class="font-weight-bold">Penulisan dan pembuatan proposal penelitian</h5>
                                    <div class="card" v-if="data.order > 21 && data.order < 26"
                                         v-for="(data, i) in dataDetail.stase_task_log_point" :key="data.order">
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

                                    <h5 class="font-weight-bold">Manfaat dan pemakaian</h5>
                                    <div class="card" v-if="data.order > 25"
                                         v-for="(data, i) in dataDetail.stase_task_log_point" :key="data.order">
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

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Kesimpulan</label><br>
                                        <div class="form-check">
                                            <input class="form-check-input" checked="checked" type="radio" name="simpulan"
                                                   id="simpulan-lulus" v-model="form.conclusion" value="lulus">
                                            <label class="form-check-label" for="simpulan-lulus">
                                                Lulus tanpa perbaikan
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" checked="checked" type="radio" name="simpulan"
                                                   id="simpulan-perbaikan" v-model="form.conclusion" value="perbaikan">
                                            <label class="form-check-label" for="simpulan-perbaikan">
                                                Lulus dengan perbaikan
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" checked="checked" type="radio" name="simpulan"
                                                   id="simpulan-presentasi" v-model="form.conclusion" value="ulang presentasi">
                                            <label class="form-check-label" for="simpulan-presentasi">
                                                Tidak lulus, ulang presentasi
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" checked="checked" type="radio" name="simpulan"
                                                   id="simpulan-penelitian" v-model="form.conclusion" value="ulang penelitian">
                                            <label class="form-check-label" for="simpulan-penelitian">
                                                Tidak lulus, ulang penelitian baru
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Catatan</label>
                                        <textarea class="form-control" v-model="form.note"></textarea>
                                    </div>
                                </div>
                            </div>
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

.vue-slider-mark-label {
    color: #9c9c9c;
}
</style>

<script>
export default {
    data() {
        return {
            base_url: '/cmsd/generate-task-log-detail',
            dataDetail: '',
            disable: false,
            options: {
                dotSize: 25,
                min: 78,
                max: 100,
                interval: 2,
                marks: true
            },
            form: new form({
                stase_task_log_id: '',
                open_stase_task_id: this.$route.params.open_stase_task_id,
                no: [],
                note: '',
                conclusion: '',
            }),
            formRaw: new form({
                no: [],
            }),
            params: {
                open_stase_task_id: this.$route.params.open_stase_task_id,
            }
        }
    },
    methods: {
        loadData() {
            axios.get(this.base_url, {params: this.params})
                .then(({data}) => {
                    this.form.note = data.note;
                    this.form.conclusion = data.conclusion;
                    this.dataDetail = data;
                    this.form.stase_task_log_id = data.id

                    var score = data.stase_task_log_point.map(function (item) {
                        return item.score;
                    });
                    this.formRaw.no = score
                })
        },
        processPoint(i, id, val) {
            this.form.no[i] = id + ',' + val
        },
        submitData() {
            this.disable = true;
            axios.post('/cmsd/stase-task-logs-update-score-proposal/' + this.form.stase_task_log_id, {
                params: this.form,
            })
                .then(({data}) => {
                    Fire.$emit('ModalSuccess');
                    this.$router.push('/dosen')
                }).catch(({response}) => {
                this.disable = false;
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
}
</script>
