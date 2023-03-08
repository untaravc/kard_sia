<template>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark"><i class="fa fa-user-graduate"></i> Detail: {{dataDetail.name}}
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">

            <div class="row">

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h1 class="card-title">STASE</h1>
                            <div class="card-tools">
                                <button v-if="!(dataDetail.stase_logs_active && !dataDetail.stase_logs_active.length)" class="btn btn-sm btn-success" @click="addModal">Tambah Stase</button>
                                <router-link :to="`/cmss/resident-score/`+$route.params.id" class="btn btn-sm btn-th-primary">
                                    Lihat Nilai
                                </router-link>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-head-fixed">
                                <tr>
                                    <th>No</th>
                                    <th>Stase</th>
                                    <th><span style="float: right">Aksi</span></th>
                                </tr>
                                <tbody>
                                <tr role="row" class="odd" v-for="(data, i) in dataDetail.stase_logs" :key="data.id">
                                    <td>{{ i + 1}} </td>
                                    <td>
                                        <span class="badge badge-secondary" v-if="data.status === 'finish'">{{data.status}}</span>
                                        <span class="badge badge-success" v-if="data.status !== 'finish'">{{data.status}}</span><br>
                                        <b v-if="data.stase">{{data.stase.name}}</b> <br>
                                        <small>{{data.start_date | formatDate}} - {{data.end_date | formatDate}} <span v-if="data.duration"> <br>({{data.duration}})</span></small>
                                        <small v-if="data.note"><br><i>Note : {{data.note}}</i></small>
                                    </td>
                                    <td class="action-button">
                                        <div class="dropdown show">
                                            <a class="btn btn-light" href="#" role="button" data-toggle="dropdown"
                                               aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-ellipsis-v"></i>
                                            </a>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                                <a class="dropdown-item" href="#" @click="editModal(data)">Edit Data</a>
                                                <a class="dropdown-item" target="_blank" :href="'/sadmin/pdf/permohonan-stase?stase_log='+data.id">
                                                    Surat Permohonan Stase
                                                </a>
                                                <a class="dropdown-item" target="_blank" :href="'/sadmin/pdf/pembimbing-stase?stase_log='+data.id">
                                                    Surat Tugas
                                                </a>
                                                <a class="dropdown-item" target="_blank" :href="'/sadmin/pdf/sk-stase?stase_log='+data.id">
                                                    Surat Keterangan
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card" v-if="dataDetail.stase_logs_active ">
                        <div class="card-header">
<!--                            <h1 class="card-title">Penugasan Stase : {{dataDetail.stase_logs_active.stase.name}}</h1>-->
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-head-fixed">
                                <tr>
                                    <th>No</th>
                                    <th>Kategori</th>
                                    <th>Nama</th>
                                    <th>Dosen</th>
                                </tr>
                                <tbody>
                                <tr role="row" class="odd" v-for="(data, i) in dataDetail.stase_logs_active.stase.stase_tasks" :key="data.id">
                                    <td>{{ i + 1}} </td>
                                    <td>
                                        <span v-if="data.task">{{data.task.name}}</span>
                                    </td>
                                    <td>{{data.name}}</td>
                                    <td><span v-if="data.lecture">{{data.lecture.name}}</span></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="editAddModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
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
                        <form @submit.prevent="createData">
                            <div class="row">
                                <div class="col-md-6" v-if="!editMode">
                                    <label>Stase</label>
                                    <select v-model="form.stase_id" class="form-control" :class="{ 'is-invalid': form.errors.has('stase_id') }">
                                        <option v-for="(data, i) in dataRaw.stases" :key="i" :value="data.id" selected>{{data.name}}</option>
                                    </select>
                                    <has-error :form="form" field="stase_id"></has-error>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tanggal Mulai</label>
                                        <input v-model="form.start_date" type="date"
                                               class="form-control" :class="{ 'is-invalid': form.errors.has('start_date') }">
                                        <has-error :form="form" field="start_date"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tanggal Selesai</label>
                                        <input v-model="form.end_date" type="date"
                                               class="form-control" :class="{ 'is-invalid': form.errors.has('end_date') }">
                                        <has-error :form="form" field="end_date"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label>Status</label>
                                    <select v-model="form.status" class="form-control" :class="{ 'is-invalid': form.errors.has('status') }">
                                        <option value="ongoing" selected>Sedang berlangsung</option>
                                        <option value="finish">Selesai</option>
                                        <option value="pending">Tunda</option>
                                    </select>
                                    <has-error :form="form" field="status"></has-error>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Catatan</label>
                                        <textarea v-model="form.note"
                                                  class="form-control" :class="{ 'is-invalid': form.errors.has('note') }"> </textarea>
                                        <has-error :form="form" field="note"></has-error>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <span v-if="disable" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        <button v-show="!editMode" :disabled="disable" type="submit" class="btn btn-primary" @click="createData">
                            Simpan</button>
                        <button v-show="editMode" :disabled="disable" type="submit" class="btn btn-primary" @click="updateData">
                            Ubah</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="openModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Buka Lembar Penilaian</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label>Dosen Penguji</label>
                                <select v-model="form.lecture_id" class="form-control" :class="{ 'is-invalid': form.errors.has('lecture_id') }">
                                    <option value="" selected>Banyak Dosen</option>
                                    <option v-for="(data, i) in dataRaw.lectures" :key="i" :value="data.id">{{data.name}}</option>
                                </select>
                                <has-error :form="form" field="lecture_id"></has-error>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button v-show="!editMode" :disabled="disable" class="btn btn-primary" @click="openStase">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Simpan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Datepicker from 'vuejs-datepicker';
    export default {
        data(){
            return {
                base_url: '/sadmin/task-details',
                editMode : false,
                disable: false,
                dataDetail: {},
                dataRaw: {
                    image_url: '',
                    stases: {},
                },
                form: new form({
                    id:'',
                    student_id: this.$route.params.id,
                    stase_id: '',
                    status:'',
                    start_date:'',
                    end_date:'',
                    note:'',
                }),
                stase:new form({
                    lecture_id: '',
                    stase_task_id:'',
                }),
                filter: new form({
                    keyword: '',
                    type: '',
                    status: '',
                    start_date: '',
                    end_date: '',
                    page: ''
                })
            }
        },
        methods:{
            loadData(){
                axios.get('/sadmin/students/'+this.$route.params.id)
                    .then( ({data}) => {
                        this.dataDetail = data;
                    })
            },
            loadStase(){
                axios.get('/sadmin/get-stases/'+this.$route.params.id)
                    .then( ({data}) => ( this.dataRaw.stases = data))
            },
            openModal(data){
                $('#openModal').modal({backdrop: 'static', keyboard: false});
                this.form.stase_task_id = data.id;
            },
            openStase(){
                this.disable = true;
                this.$Progress.start();
                this.form.post('/cmsr/open-stase-task')
                    .then((response) => {
                        this.disable = false;
                        Fire.$emit('ModalSuccess');
                    }).catch((error) => {
                    this.disable = false;
                });
            },
            closeExam(data){
                if(confirm('Tutup sesi Ujian?')){
                    axios.get('/cmsr/close-stase-task', {params: data.open_stase_task[0]})
                        .then( ({response}) => {
                            Fire.$emit('ModalSuccess');
                        })
                }
            },
            addModal(){
                this.form.reset();
                this.editMode = false;
                $('#editAddModal').modal({backdrop: 'static', keyboard: false})
            },
            createData(){
                this.disable = true;
                this.$Progress.start();
                this.form.post('/sadmin/stase-logs')
                    .then((response) => {
                        this.disable = false;
                        Fire.$emit('ModalSuccess');
                    }).catch((error) => {
                    this.disable = false;
                });
            },
            editModal(data){
                this.editMode = true;
                $('#editAddModal').modal({backdrop: 'static', keyboard: false});
                this.form.fill(data);
            },
            updateData(){
                this.disable = true;
                this.$Progress.start();
                this.form.patch("/sadmin/stase-logs/"+this.form.id)
                    .then((response) => {
                        this.disable = false;
                        Fire.$emit('ModalSuccess');
                    }).catch((error) => {
                    this.disable = false;
                });
            },
            deleteData(id){
                Swal.fire({
                    title: 'Are you sure to delete this data?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    this.$Progress.start();
                    if (result.value) {
                        axios.post('/sadmin/stase-logs/' + id, {_method:'delete'})
                            .then((response) => {
                                this.loadData();
                                Swal.fire(
                                    'Deleted!',
                                    'Data has been deleted.',
                                    'success'
                                );
                                this.$Progress.finish();
                            }).catch((errror) => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Unauthorized',
                                    text: 'You can not delete this data!',
                                })
                            });
                    }
                })
            },
            popSuccessSwal(){
                Swal.fire({
                    position: 'top-end',
                    icon: 'success',
                    title: 'Success',
                    toast: true,
                    showConfirmButton: false,
                    timer: 1000,
                })
            },
        },
        created(){
            this.$Progress.start();
            this.loadData();
            this.loadStase();
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
        }
    }
</script>
