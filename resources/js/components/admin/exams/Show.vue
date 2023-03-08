<template>
    <div class="content-wrapper pt-3">
        <div class="content">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table>
                                        <tr>
                                            <td><b>{{dataDetail.name}}</b></td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <small>{{dataDetail.available_at | formatDateTime}}</small> -
                                                <small>{{dataDetail.expired_at | formatDateTime}}</small>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{dataDetail.desc}}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table>
                                        <tr>
                                            <td>
                                                <span v-if="dataDetail.status !== 1" class="badge badge-secondary">{{dataDetail.status_label}}</span>
                                                <span v-if="dataDetail.status === 1" class="badge badge-success">{{dataDetail.status_label}}</span>
                                                <b>{{dataDetail.duration}} menit </b>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span v-if="dataDetail.lecture">{{dataDetail.lecture.name}}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span v-if="dataDetail.stase">{{dataDetail.stase.name}}</span>
                                                <span v-if="dataDetail.stase_task">- {{dataDetail.stase_task.name}}</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-info elevation-1"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Tidak Aktif</span>
                            <span class="info-box-number">
                                  {{ count.available }}
                                </span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="info-box mb-3">
                                <span class="info-box-icon bg-success elevation-1"><i
                                    class="fas fa-sliders-h"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">Aktif</span>
                            <span class="info-box-number">{{count.active}}</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-check-double"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text d-none d-md-block">Selesai</span>
                            <span class="info-box-number">{{count.done}}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body p-0">
                          <vue-loader :active.sync="available_loader" :can-cancel="false" loader="dots"
                                      :is-full-page="false"></vue-loader>
                            <table class="table">
                                <tr>
                                    <td colspan="2">
                                        <input type="text" placeholder="Cari.."
                                               v-model="filter.available.name"
                                               @keyup.enter="loadAvailableStudent()" class="form-control">
                                    </td>
                                </tr>
                                <tr v-if="students.available.length === 0 ">
                                    <td colspan="2" class="text-center">
                                        <i>Tidak ada data</i>
                                    </td>
                                </tr>
                                <tr v-for="avail in students.available">
                                    <td><small>{{avail.name}}</small></td>
                                    <td style="width: 90px" class="text-right">
                                        <button class="btn btn-sm btn-primary" @click="openExam(avail.id)">
                                            Buka <i class="fa fa-chevron-right"></i>
                                        </button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body p-0">
                          <vue-loader :active.sync="active_loader" :can-cancel="false" loader="dots"
                                      :is-full-page="false"></vue-loader>
                            <table class="table">
                                <tr>
                                    <td colspan="2">
                                        <input type="text" placeholder="Cari.."
                                               v-model="filter.active.name"
                                               @keyup.enter="loadActiveStudent()" class="form-control">
                                    </td>
                                </tr>
                                <tr v-if="students.active.length === 0 ">
                                    <td colspan="2" class="text-center">
                                        <i>Tidak ada data</i>
                                    </td>
                                </tr>
                                <tr v-for="active in students.active">
                                    <td><small>{{active.name}}</small></td>
                                    <td style="max-width: 130px" class="text-right">
                                        <button class="btn btn-sm btn-danger" @click="cancelExam(active.id)">
                                            <i class="fa fa-times"></i>
                                        </button>
                                        <button class="btn btn-sm btn-success" @click="finishExam(active.id)">
                                            Selesai <i class="fa fa-chevron-right"></i>
                                        </button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body p-0">
                          <vue-loader :active.sync="done_loader" :can-cancel="false" loader="dots"
                                      :is-full-page="false"></vue-loader>
                            <table class="table">
                                <tr>
                                    <td colspan="2">
                                        <input type="text" placeholder="Cari.."
                                               v-model="filter.done.name"
                                               @keyup.enter="loadDoneStudent()" class="form-control">
                                    </td>
                                </tr>
                                <tr v-if="students.done.length === 0 ">
                                    <td colspan="2" class="text-center">
                                        <i>Tidak ada data</i>
                                    </td>
                                </tr>
                                <tr v-for="done in students.done">
                                    <td>
                                        <small>{{done.name}}</small>
                                        <span class="badge badge-success" v-if="done.score">{{done.score}}</span>
                                    </td>
                                    <td style="max-width: 100px" class="text-right">
                                        <button class="btn btn-sm btn-warning" @click="openExam(done.id)">
                                            <i class="fa fa-chevron-left"></i>
                                        </button>
                                        <button class="btn btn-sm btn-info" @click="scoreModal(done.id)">
                                            Nilai
                                        </button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="scoreModal" tabindex="-1" role="dialog"
             aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="text-center">Tambah Nilai</div>
                        <input type="number" class="form-control" v-model="score.score" style="text-align: center">
                        <button class="btn btn-sm btn-success mt-2" style="width: 100%" @click="addScore">
                            Simpan
                        </button>
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
            available_loader: false,
            active_loader: false,
            done_loader: false,
            dataDetail: '',
            students:{
                available: '',
                active: '',
                done: '',
            },
            count:{
                available: '',
                active: '',
                done: '',
            },
            filter:{
                available: {
                    name: null
                },
                active: {
                    name: null
                },
                done: {
                    name: null
                },
            },
            score:{
                id: '',
                score: '',
            }
        }
    },
    methods: {
        loadData() {
            axios.get('/sadmin/exams/' + this.$route.params.id)
                .then(({data}) => {
                    this.dataDetail = data.data
                })
        },
        loadAvailableStudent() {
            this.available_loader = true;
            axios.get('/sadmin/exam-available-students/' + this.$route.params.id, {
                params:{
                    name: this.filter.available.name
                }
            })
                .then(({data}) => {
                    this.students.available = data.data;
                    this.count.available = data.count;
                    this.available_loader = false;
                }).catch(()=>{
                this.available_loader = false;
            })
        },
        loadActiveStudent() {
            this.active_loader = true;
            axios.get('/sadmin/exam-active-students/' + this.$route.params.id, {
                params:{
                    name: this.filter.active.name
                }
            })
                .then(({data}) => {
                    this.students.active = data.data;
                    this.count.active = data.count;
                    this.active_loader = false;
                }).catch(()=>{
                this.active_loader = false;
            })
        },
        loadDoneStudent() {
            this.done_loader = true;
            axios.get('/sadmin/exam-done-students/' + this.$route.params.id, {
                params:{
                    name: this.filter.done.name
                }
            })
                .then(({data}) => {
                    this.students.done = data.data;
                    this.count.done = data.count;
                    this.done_loader = false;
                }).catch(()=>{
                this.done_loader = false;
            })
        },
        openExam(id){
            axios.get('/sadmin/exam-activate-students', {params:{
                    'student_id' : id,
                    'exam_id' : this.$route.params.id,
                }})
                .then(({data}) => {
                    if(data.status){
                        this.loadAvailableStudent();
                        this.loadActiveStudent();
                        this.loadDoneStudent();
                    }
                })
        },
        finishExam(id){
            axios.get('/sadmin/exam-finish-students', {params:{
                    'student_id' : id,
                    'exam_id' : this.$route.params.id,
                }})
                .then(({data}) => {
                    if(data.status){
                        this.loadAvailableStudent();
                        this.loadActiveStudent();
                        this.loadDoneStudent();
                    }
                })
        },
        cancelExam(id){
            axios.get('/sadmin/exam-cancel-score', {params:{
                    'student_id' : id,
                    'exam_id' : this.$route.params.id,
                }})
                .then(({data}) => {
                    if(data.status){
                        this.loadAvailableStudent();
                        this.loadActiveStudent();
                        this.loadDoneStudent();
                    }
                })
        },
        scoreModal(id){
            this.score.id = id;
            $('#scoreModal').modal('show');
        },
        addScore(){
          this.disable = true;
            axios.get('/sadmin/exam-add-score',{
              params:{
                'student_id' : this.score.id,
                'score' : this.score.score,
                'exam_id' : this.$route.params.id,
              }
            })
            .then(({data})=>{
                if(data.status){
                    $('#scoreModal').modal('hide');
                    this.loadDoneStudent();
                }
              this.disable = false;
            }).catch(()=>{
              this.disable = false;
            })
        }
    },
    created() {
        this.loadData();
        this.loadAvailableStudent();
        this.loadActiveStudent();
        this.loadDoneStudent();
    },
}
</script>
