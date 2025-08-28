<template>
    <div class="content-wrapper">
        <div class="content mt-3">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary card-outline" v-if="!dataDetail">
                        <div class="card-body">
                            <p>Belum ada data</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="card card-primary card-outline">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <table class="text-center" width="100%">
                                                <tr>
                                                    <td colspan="3"><b>{{ dataProfile.name }}</b></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3">{{ dataProfile.email }}</td>
                                                </tr>
                                                <tr>
                                                    <td><a target="_blank"
                                                            :href="'/sadmin/print/report-score?student_id=' + dataProfile.id + '&tahap=1'">Tahap
                                                            1</a></td>
                                                    <td><a target="_blank"
                                                            :href="'/sadmin/print/report-score?student_id=' + dataProfile.id + '&tahap=2'">Tahap
                                                            2</a></td>
                                                    <td><a target="_blank"
                                                            :href="'/sadmin/print/report-score?student_id=' + dataProfile.id + '&tahap=3'">Tahap
                                                            3</a></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3">
                                                        <a target="_blank"
                                                            :href="'/sadmin/print/report-student?student_id=' + dataProfile.id">Report
                                                            Nilai</a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card card-primary card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">Stase</h3>
                                </div>
                                <div class="card-body p-0">
                                    <ul class="nav nav-pills flex-column">
                                        <li class="nav-item active">
                                            <table width="100%">
                                                <tr v-for="stase in dataProfile.stase_logs"
                                                    style="border-top: 1px solid lightblue">
                                                    <td style="width: 38px;">
                                                        <input type="radio" name="stase" v-model="filter.stase_log_id"
                                                            :value="stase.id" class="m-2" :id="'stase_' + stase.id">
                                                    </td>
                                                    <td>
                                                        <label :for="'stase_' + stase.id" v-if="stase.stase">{{
                                                            stase.stase.name }}</label>
                                                    </td>
                                                </tr>
                                            </table>
                                        </li>
                                    </ul>
                                </div>
                                <!-- /.card-body -->
                            </div>
                        </div>
                        <!-- /.col -->
                        <div class="col-md-8">
                            <div class="card card-primary card-outline">
                                <div class="p-2 d-flex justify-content-between">
                                    <h3 class="card-title"><b>STASE </b>
                                        <span v-if="dataDetail[0] && dataDetail[0].stase">: {{ dataDetail[0].stase.name
                                            }}</span>
                                    </h3>
                                    <div v-if="dataDetail[0] && dataDetail[0].stase">
                                        <a :href="'/sadmin/print/report-student-stase?student_id=' + dataProfile.id + '&stase_id=' + dataDetail[0].stase.id"
                                            target="_blank">Download</a>
                                    </div>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <tbody>
                                                <tr v-for="data in dataDetail">
                                                    <td>
                                                        <b v-if="data.stase_task">{{ data.stase_task.name }}</b> <br>
                                                        <small>{{ data.title }}</small>
                                                        <div v-if="data.scores">
                                                            <small v-for="score in data.scores" :key="score.id"
                                                                v-if="score.lecture" @click="detailModal(score)">
                                                                <i class="fa fa-marker text-primary"></i>
                                                                <span class="score-list">{{ score.lecture.name }}</span>
                                                                <span class="badge-success badge"
                                                                    style="font-size: smaller"
                                                                    v-if="score.point_average > 0">
                                                                    {{ score.point_average }}
                                                                </span>
                                                                <i class="text-black-50"
                                                                    v-if="score.point_average > 0">on: {{ score.date |
                                                                        formatDate }}</i>
                                                                <br>
                                                            </small>
                                                        </div>
                                                    </td>
                                                    <td class="text-right">
                                                        <button v-if="data.status === 'pending'"
                                                            class="btn btn-wd btn-sm btn-th-dark"
                                                            @click="addModal(data)">Tambah</button>
                                                        <button v-if="data.admin === 1"
                                                            class="btn btn-wd btn-sm btn-th-secondary"
                                                            @click="editModal(data)">Ubah</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <!-- /.table -->
                                    </div>
                                    <!-- /.mail-box-messages -->
                                </div>
                                <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                        </div>
                        <!-- /.col -->
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="editAddModal" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" v-show="!editMode">Tambah</h5>
                        <h5 class="modal-title" v-show="editMode">Ubah</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Dosen</label>
                                <select v-model="form.lecture_id" class="form-control"
                                    :class="{ 'is-invalid': form.errors.has('lecture_id') }">
                                    <option v-for="(data, i) in dataRaw.lectures" :key="i" :value="data.id" selected>
                                        {{ data.name }}</option>
                                </select>
                                <has-error :form="form" field="lecture_id"></has-error>
                            </div>
                            <div class="col-md-6">
                                <label>Tanggal</label>
                                <input type="date" v-model="form.date" class="form-control">
                                <has-error :form="form" field="date"></has-error>
                            </div>
                            <div class="col-md-6">
                                <label>Nilai</label>
                                <input type="number" v-model="form.point_average" class="form-control">
                                <has-error :form="form" field="point_average"></has-error>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button v-show="!editMode" type="submit" class="btn btn-primary"
                            @click="createData">Simpan</button>
                        <button v-show="editMode" type="submit" class="btn btn-primary"
                            @click="createData">Ubah</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="pointModal" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-body table-responsive p-0">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Poin Penilaian</th>
                                    <th>Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(data, d) in dataPoints" :key="d">
                                    <td><span v-if="data.task_detail">{{ data.task_detail.name }}</span></td>
                                    <td>{{ data.score }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="px-3">
                            <label>Catatan</label>
                            <p>{{ dataPointNote }}</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="detailModal" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Uploaded File</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <table>
                                <tbody>
                                    <tr>
                                        <td>Judul</td>
                                        <td>:</td>
                                        <td>{{ detailFile.title }}</td>
                                    </tr>
                                    <tr>
                                        <td>Keterangan</td>
                                        <td>:</td>
                                        <td>{{ detailFile.desc }}</td>
                                    </tr>
                                    <tr v-if="detailFile.link">
                                        <td>File</td>
                                        <td>:</td>
                                        <td>
                                            <a target="_blank" :href="detailFile.link"
                                                download>download</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Datepicker from 'vuejs-datepicker';
export default {
    data() {
        return {
            base_url: '/sadmin/student-score/' + this.$route.params.id,
            editMode: false,
            dataDetail: {},
            dataPoints: {},
            dataPointNote: '',
            detailFile: {},
            dataProfile: {},
            dataRaw: {
                lectures: {},
            },
            form: new form({
                id: '',
                lecture_id: '',
                date: '',
                point_average: '',
            }),
            score: new form({
                lecture_id: '',
                stase_task_id: '',
            }),
            filter: new form({
                stase_log_id: null,
            })
        }
    },
    methods: {
        loadData() {
            axios.get('/sadmin/student-score/' + this.$route.params.id, { params: this.filter })
                .then(({ data }) => {
                    this.dataDetail = data;
                })
        },
        loadLecture() {
            axios.get('/sadmin/get-lectures')
                .then(({ data }) => (this.dataRaw.lectures = data))
        },
        loadResident() {
            axios.get('/sadmin/students/' + this.$route.params.id)
                .then(({ data }) => (this.dataProfile = data))
        },

        addModal(data) {
            this.form.fill(data);
            if (this.form.date) {
                this.form.date = data.date.slice(0, 10);
            }
            this.editMode = false;
            $('#editAddModal').modal({ backdrop: 'static', keyboard: false })
        },
        createData() {
            this.form.post('/sadmin/add-score')
                .then((response) => {
                    $('#editAddModal').modal('hide')
                    this.popGlobalSuccess({});
                    this.loadData();
                }).catch((error) => {

                });
        },
        editModal(data) {
            this.editMode = true;
            this.form.fill(data);
            if (this.form.date) {
                this.form.date = data.date.slice(0, 10);
            }
            $('#editAddModal').modal({ backdrop: 'static', keyboard: false });
        },
        updateData() {
            this.$Progress.start();
            this.form.patch("/sadmin/stase-logs/" + this.form.id)
                .then((response) => {
                    Fire.$emit('ModalSuccess');
                }).catch((error) => {
                    console.log(error)
                });
        },
        detailModal(data) {
            if (data.admin === 0) {
                this.dataPoints = data.stase_task_log_point;
                this.dataPointNote = data.note;
                $('#pointModal').modal('show');
            }
        },
        fileModal(data) {
            this.detailFile = data;
            $('#detailModal').modal({ backdrop: 'static', keyboard: false })
        },
    },
    created() {
        this.$Progress.start();
        this.loadData();
        this.loadLecture();
        this.loadResident();

        Fire.$on('ModalSuccess', () => {
            this.loadData();
            this.popSuccessSwal();
            $('#editAddModal').modal('hide');
            this.$Progress.finish();
        })
    },
    mounted() {
        this.$Progress.finish()
    },
    components: {
        Datepicker,
    },
    watch: {
        'filter.stase_log_id': function () {
            this.loadData()
        }
    }
}
</script>

<style>
label {
    margin-top: 0.5rem;
}

.score-list {
    text-decoration: underline;
    cursor: pointer;
}
</style>
