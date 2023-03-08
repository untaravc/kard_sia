<template>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark"><i class="fa fa-chalkboard-teacher"></i> Detail: {{dataDetail.name}}
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="row">
                <div class="col-12">
                    <div class="card" v-if="dataDetail.stase_logs_active ">
                        <div class="card-header">
                            <h1 class="card-title">Penugasan Stase : {{dataDetail.stase_logs_active.stase.name}}</h1>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-head-fixed">
                                <tr>
                                    <th>No</th>
                                    <th>Kategori</th>
                                    <th>Nama</th>
                                    <th>Dosen</th>
                                    <th>Status</th>
                                    <th><span style="float: right">Aksi</span></th>
                                </tr>
                                <tbody>
                                <tr role="row" class="odd" v-for="(data, i) in dataDetail.stase_logs_active.stase.stase_tasks" :key="data.id">
                                    <td>{{ i + 1}} </td>
                                    <td><span v-if="data.task"></span>{{data.task.name}}</td>
                                    <td>{{data.name}}</td>
                                    <td><span v-if="data.lecture">{{data.lecture.name}}</span></td>
                                    <td>
                                        <span v-if="data.open_stase_task && data.open_stase_task[0]" class="badge badge-success">open</span>
                                        <span v-if="data.open_stase_task && !data.open_stase_task[0]" class="badge badge-secondary">close</span>
                                    </td>
                                    <td class="action-button">
                                        <button @click="openModal(data)" v-if="data.open_stase_task && !data.open_stase_task[0]" class="btn btn-sm btn-info">
                                            <i class="fa fa-door-open"></i>
                                        </button>
                                        <button @click="closeExam(data)" v-if="data.open_stase_task[0]" class="btn btn-sm btn-danger">
                                            <i class="fa fa-door-closed"></i>
                                        </button>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h1 class="card-title">STASE</h1>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-head-fixed">
                                <tr>
                                    <th>No</th>
                                    <th>Stase</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Durasi</th>
                                    <th>Catatan</th>
                                    <th>Status</th>
                                    <th><span style="float: right">Aksi</span></th>
                                </tr>
                                <tbody>
                                <tr role="row" class="odd" v-for="(data, i) in dataDetail.stase_logs" :key="data.id">
                                    <td>{{ i + 1}} </td>
                                    <td>{{data.stase.name}}</td>
                                    <td>{{data.start_date | formatDate}}</td>
                                    <td>{{data.end_date | formatDate}}</td>
                                    <td>{{data.duration}}</td>
                                    <td>{{data.note}}</td>
                                    <td>{{data.status}}</td>
                                    <td class="action-button">

                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
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
                        <button v-show="!editMode" class="btn btn-primary" @click="createData">Simpan</button>
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
                base_url: '/cmsr/stases',
                editMode : false,
                dataDetail: {},
                dataRaw: {
                    image_url: '',
                    lectures:[],
                    stases: {},
                },
                form: new form({
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
                axios.get('/cmsr/residents')
                    .then( ({data}) => {
                        this.dataDetail = data;
                    })
            },
            loadLectures(){
                axios.get('/get-lectures')
                    .then( ({data}) => {
                        this.dataRaw.lectures = data;
                    })
            },
            addModal(){
                this.form.reset();
                this.editMode = false;
                $('#editAddModal').modal({backdrop: 'static', keyboard: false})
            },
            openModal(data){
                $('#openModal').modal({backdrop: 'static', keyboard: false});
                this.form.stase_task_id = data.id;
            },
            closeExam(data){
                if(confirm('Tutup sesi Ujian?')){
                    axios.get('/cmsr/close-stase-task', {params: data.open_stase_task[0]})
                        .then( ({response}) => {
                            Fire.$emit('ModalSuccess');
                        })
                }
            },
            createData(){
                this.$Progress.start();
                this.form.post('/cmsr/open-stase-task')
                    .then((response) => {
                        Fire.$emit('ModalSuccess');
                    }).catch((error) => {
                    console.log(error)
                });
            },
            editModal(data){
                this.editMode = true;
                $('#editAddModal').modal({backdrop: 'static', keyboard: false});
                this.form.fill(data);
            },
            updateData(){
                this.$Progress.start();
                this.form.patch("/sadmin/stase-logs/"+this.form.id)
                    .then((response) => {
                        Fire.$emit('ModalSuccess');
                    }).catch((error) => {
                    console.log(error)
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
                        this.form.delete('/sadmin/stase-logs/' + id).then((response) => {
                            this.loadData();
                            Swal.fire(
                                'Deleted!',
                                'Data has been deleted.',
                                'success'
                            )
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
                    showConfirmButton: false,
                    timer: 1000,
                })
            },
        },
        created(){
            this.$Progress.start();
            this.loadData();
            this.loadLectures();

            Fire.$on('ModalSuccess', () => {
                this.loadData();
                this.popSuccessSwal();
                $('#openModal').modal('hide');
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
