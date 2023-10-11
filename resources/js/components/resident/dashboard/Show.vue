<template>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark"><i class="fa fa-tachometer-alt"></i> {{dataDetail.name}}
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="row">
                <div class="col-md-3">
                    <div class="card" >
                        <div class="card-header">
                            <h3 class="card-title">Agenda Hari Ini</h3>
                            <div class="card-tools">

                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <div v-if="schedules && schedules[0]" class="callout callout-danger" v-for="(sch, s) in schedules" :key="s">
                                        <h5>{{sch.name}}</h5>

                                        <p>{{sch.title}}</p>
                                        <button v-if="!sch.absence" class="btn btn-sm btn-success" @click="presensiModal(sch)">Presensi</button>
                                        <small v-if="sch.absence"><i>Anda telah presensi pada {{sch.absence.created_at | formatDateTime}}</i></small>
                                    </div>
                                    <div v-if="schedules && !schedules[0]">
                                        <i>Tidak ada agenda</i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="card" v-if="dataDetail.stase_logs_active ">
                        <div class="card-header">
                            <h1 class="card-title">Penugasan Stase : {{dataDetail.stase_logs_active.stase.name}}</h1>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-head-fixed">
                                <tr>
                                    <th>No</th>
                                    <th>Status</th>
                                    <th>Penugasan</th>
                                    <th>File Tugas</th>
                                    <th><span style="float: right">Aksi</span></th>
                                </tr>
                                <tbody>
                                <tr role="row" class="odd" v-for="(data, i) in dataDetail.stase_logs_active.stase.stase_tasks" :key="data.id">
                                    <td>{{ i + 1}} </td>
                                    <td>
                                        <span v-if="data.open_stase_task" class="badge badge-success">open</span>
                                        <span v-if="!data.open_stase_task" class="badge badge-secondary">close</span>
                                    </td>
                                    <td>
                                        <h6 class="mb-0">{{data.name}}</h6>
                                        <span v-if="data.open_stase_task && data.open_stase_task.lecture">{{data.open_stase_task.lecture.name}}</span>
                                        <span v-if="data.open_stase_task && data.open_stase_task.lecture_id == '0'"><i>banyak dosen</i></span><br>
                                        <span v-if="data.open_stase_task">Judul : {{data.open_stase_task.title}}</span>
                                    </td>
                                    <td>
                                        <ol class="pl-3" v-if="data.open_stase_task && data.open_stase_task.files">
                                            <li v-for="(file, f) in data.open_stase_task.files" :key="f">
                                                <span style="text-decoration: underline; cursor:pointer" @click="detailModal(file)">{{file.title}}</span>
                                                <small style="text-decoration: underline; cursor: pointer; color: red" @click="deleteFile(file.id)">hapus</small>
                                            </li>
                                        </ol>
                                    </td>
                                    <td class="action-button">
                                        <button @click="openModal(data)" v-if="!data.open_stase_task" class="btn btn-sm btn-success">
<!--                                            <i class="fa fa-door-open"></i> -->
                                            Buka
                                        </button>
                                        <button @click="closeExam(data)" v-if="data.open_stase_task" class="btn btn-sm btn-danger">
<!--                                            <i class="fa fa-door-closed"></i> -->
                                            Tutup
                                        </button>
                                        <button @click="uploadModal(data.open_stase_task)" v-if="data.open_stase_task" class="btn btn-sm btn-info">
<!--                                            <i class="fa fa-upload"></i> -->
                                            Upload
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
                                    <th width="">No</th>
                                    <th>Stase</th>
                                    <th>Tanggal</th>
                                </tr>
                                <tbody>
                                <tr role="row" class="odd" v-for="(data, i) in dataDetail.stase_logs" :key="data.id">
                                    <td>{{ i + 1}} </td>
                                    <td>
                                        <span class="badge" :class="data.status == 'finish' ? 'badge-secondary' : 'badge-success'">{{data.status}}</span><br>
                                        {{data.stase.name}}
                                        <br><small>Note : {{data.note}}</small>
                                    </td>
                                    <td>
                                        {{data.start_date | formatDate}} -
                                        <span v-if="data.status === 'finish'">{{data.end_date | formatDate}}</span><span v-else>selesai</span>
                                        <i v-if="data.status === 'finish' && data.duration"><br>{{data.duration}}</i>
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<!--        OPEN MODAL-->
        <div class="modal fade bd-example-modal-lg" id="openModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
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
                                <label>Judul</label>
                                <input type="text" class="form-control" v-model="form.title">
                                <has-error :form="form" field="lecture_id"></has-error>
                            </div>
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
                        <button :disabled="disable" v-show="!editMode" class="btn btn-primary" @click="createData">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Simpan</button>
                    </div>
                </div>
            </div>
        </div>
<!--    Upload File-->
        <div class="modal fade bd-example-modal-lg" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload File</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label>Judul</label>
                                <input type="text" class="form-control" v-model="uploadData.title">
                                <has-error :form="uploadData" field="title"></has-error>
                            </div>
                            <div class="col-md-12">
                                <label>Keterangan</label>
                                <textarea class="form-control" v-model="uploadData.desc"></textarea>
                                <has-error :form="uploadData" field="desc"></has-error>
                            </div>
                            <div class="col-md-12">
                                <label>File</label><br>
                                <input type="file" @change="getFile" :class="{ 'is-invalid': form.errors.has('file') }">
                                <has-error :form="uploadData" field="file"></has-error>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button :disabled="disable" v-show="!editMode" class="btn btn-primary" @click="uploadFile">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Upload</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
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
                                    <td>{{detailFile.title}}</td>
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
                                        <a target="_blank" :href="`/storage/`+detailFile.link" download>download</a>
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

        <div class="modal fade bd-example-modal-lg" id="scheduleModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Presensi Agenda</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <table>
                                <tr><td width="150px">Nama</td><td>:</td><td>{{scheduleDetail.name}}</td></tr>
                                <tr><td width="150px">Judul</td><td>:</td><td>{{scheduleDetail.title}}</td></tr>
                                <tr><td width="150px">Tempat</td><td>:</td><td>{{scheduleDetail.place}}</td></tr>
                                <tr><td width="150px">Mulai</td><td>:</td><td>{{scheduleDetail.start_date | formatDateTime}}</td></tr>
                                <tr><td width="150px">Selesai</td><td>:</td><td>{{scheduleDetail.end_date | formatDateTime}}</td></tr>
                                <tr valign="top"><td width="150px">Deskripsi</td><td>:</td><td><span v-html="scheduleDetail.desc"></span></td></tr>
                                <tr><td width="150px">Catatan</td><td>:</td><td>{{scheduleDetail.note}}</td></tr>
                            </table>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Keterangan</label>
                                    <input type="text" class="form-control" v-model="scheduleDetail.presensi_note">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button :disabled="disable" @click="present" class="btn btn-success">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Presensi</button>
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
                disable : false,
                dataDetail: {},
                schedules:{},
                scheduleDetail:{},
                dataRaw: {
                    image_url: '',
                    lectures:[],
                    stases: {},
                },
                detailFile:{

                },
                form: new form({
                    lecture_id: '',
                    stase_task_id:'',
                    title:'',
                }),
                uploadData: new form({
                    open_stase_task_id :'',
                    title:'',
                    desc:'',
                    file:'',
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
                this.form.reset();
                $('#openModal').modal({backdrop: 'static', keyboard: false});
                this.form.stase_task_id = data.id;
            },
            closeExam(data){
                if(confirm('Tutup sesi Ujian?')){
                    axios.get('/cmsr/close-stase-task', {params: data.open_stase_task})
                        .then( ({response}) => {
                            Fire.$emit('ModalSuccess');
                        })
                }
            },
            createData(){
                this.$Progress.start();
                this.disable = true;
                this.form.post('/cmsr/open-stase-task')
                    .then((response) => {
                        Fire.$emit('ModalSuccess');
                        this.disable = false;
                    }).catch((error) => {
                        alert(error)
                        this.disable = false;
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
            uploadModal(data){
                this.uploadData.reset();
                $('#uploadModal').modal({backdrop: 'static', keyboard: false});
                this.uploadData.open_stase_task_id = data.id;
            },
            uploadFile(){
                this.uploadData.post('/cmsr/upload-file')
                .then((response)=>{
                    Fire.$emit('ModalSuccess')
                })
            },
            getFile(e){
                let file = e.target.files[0];

                let reader = new FileReader();
                if (file['size'] < 2200000){
                    reader.onloadend = (file)=>{
                        this.uploadData.file = reader.result;
                    };
                    reader.readAsDataURL(file)
                } else {
                    this.form.image = '';
                    $('#form-image').val('');
                    Swal.fire({
                        icon: 'error',
                        title: 'You upload large file',
                        text: 'Please upload not more than 2MB file',
                    })
                }
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
            deleteFile(id){
                if (confirm('Hapus File?')){
                    axios.get('/cmsr/delete-file/'+id)
                    .then(()=>{
                        Fire.$emit('ModalSuccess')
                    })
                    .catch(()=>{
                        alert('Gagal.')
                    })
                }
            },
            detailModal(data){
                this.detailFile = data;
                $('#detailModal').modal({backdrop: 'static', keyboard: false})
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
            presensiModal(data){
                this.scheduleDetail = data;
                $('#scheduleModal').modal({backdrop: 'static', keyboard: false})
            },
            present(){
                this.disable = true;
                axios.post('/cmsr/presence', this.scheduleDetail)
                    .then(()=>{
                        Fire.$emit('ModalSuccess');
                        this.disable = false;
                    }).catch(()=>{
                    this.disable = false;
                })
            },
            loadSchedule(){
                axios.get('/cmsr/get-schedule')
                    .then((response)=>{
                        this.schedules = response.data
                    })
            },
        },
        created(){
            this.$Progress.start();
            this.loadData();
            this.loadLectures();
            this.loadSchedule();

            Fire.$on('ModalSuccess', () => {
                this.loadData();
                this.loadSchedule();
                this.popSuccessSwal();
                $('#openModal').modal('hide');
                $('#uploadModal').modal('hide');
                $('#scheduleModal').modal('hide');
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
