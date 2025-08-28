
<template>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark"><i class="fa fa-chalkboard-teacher"></i> Nilai {{dataDetail.name}}
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="row">
                <div class="col-12">
                    <div class="card" v-if="!dataDetail.data">
                        <div class="card-body">
                            <p>Belum ada data</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Cari Stase</label>
                                        <input type="text" class="form-control" v-model="filter.keyword" @keyup="withFilter">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body table-responsive p-0">
                            <table class="table">
                            <thead>
                            <tr>
                                <th>No</th>
                                <th>Stase</th>
                                <th>Penilaian</th>
                                <th>Skor</th>
                                <th>File</th>
                                <th>Aksi</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(data, i) in dataDetail.data" :key="i">
                                <td>{{dataDetail.per_page * (dataDetail.current_page - 1) + i + 1}} </td>

                                <td>
                                    <span v-if="data.stase">{{data.stase.name}}</span><br>
                                </td>
                                <td>
                                    <span v-if="data.point_average" class="badge badge-success">selesai</span>
                                    <span v-if="!data.point_average" class="badge badge-secondary">tunda </span><br>
                                    <span v-if="data.stase_task">{{data.stase_task.name}}</span><br>
                                </td>
                                <td>
                                    <span class="badge badge-success" v-if="data.date">{{data.point_average}}</span><br>
                                    <span v-if="data.lecture">{{data.lecture.name}}</span><br>
                                    <small v-if="data.date">Open form : {{data.date | formatDateTime}}</small><br>
                                </td>
                                <td>
                                    <ol class="pl-3" v-if="data.files">
                                        <li v-for="(file, f) in data.files" :key="f">
                                            <span style="text-decoration: underline; cursor:pointer" @click="fileModal(file)">{{file.title}}</span>
                                        </li>
                                    </ol>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-success" @click="addModal(data)" v-if="data.admin">Ubah</button>
                                    <button class="btn btn-sm btn-danger" @click="deleteData(data)" v-if="data.admin">Hapus</button>
                                    <button class="btn btn-sm btn-success" @click="addModal(data)" v-if="!data.point_average">Tambah</button>
                                    <button class="btn btn-sm btn-info" v-if="!data.admin && data.point_average" @click="detailModal(data)">Detail</button>
                                </td>
                            </tr>
                            </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-sm-12 col-md-5">
                                    <div class="dataTables_info" role="status" aria-live="polite">
                                        Menampilkan data {{dataDetail.from}} hingga {{dataDetail.to}} dari {{dataDetail.total}} data.
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-7">
                                    <div class="dataTables_paginate paging_simple_numbers" style="float: right">
                                        <pagination :data="dataDetail" @pagination-change-page="pagination"></pagination>
                                    </div>
                                </div>
                            </div>
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
                        <div class="row">
                            <div class="col-md-6">
                                <label>Dosen</label>
                                <select v-model="form.lecture_id" class="form-control" :class="{ 'is-invalid': form.errors.has('lecture_id') }">
                                    <option v-for="(data, i) in dataRaw.lectures" :key="i" :value="data.id" selected>{{data.name}}</option>
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
                        <button v-show="!editMode" type="submit" class="btn btn-primary" @click="createData">Simpan</button>
                        <button v-show="editMode" type="submit" class="btn btn-primary" @click="createData">Ubah</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="pointModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Penilaian</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>No</th>
                                <th>Poin Penilaian</th>
                                <th>Nilai</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(data,d) in dataPoints" :key="d">
                                <td>{{d + 1}}</td>
                                <td><span v-if="data.task_detail">{{data.task_detail.name}}</span></td>
                                <td>{{data.score}}</td>
                            </tr>
                            </tbody>
                        </table>
                        <label>Catatan</label>
                        <p>{{dataPointNote}}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
                                        <a target="_blank" :href="detailFile.link" download>download</a>
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
        data(){
            return {
                base_url: '/sadmin/student-score/'+this.$route.params.id,
                editMode : false,
                dataDetail: {},
                dataPoints:{},
                dataPointNote:'',
                detailFile: {},
                dataRaw: {
                    lectures: {},
                },
                form: new form({
                    id:'',
                    lecture_id:'',
                    date:'',
                    point_average:'',
                }),
                score: new form({
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
                axios.get('/sadmin/student-score/'+this.$route.params.id)
                    .then( ({data}) => {
                        this.dataDetail = data;
                    })
            },
            loadLecture(){
                axios.get('/sadmin/get-lectures')
                    .then( ({data}) => ( this.dataRaw.lectures = data))
            },
            withFilter(){
                axios.get('/sadmin/student-score/'+this.$route.params.id, {params:this.filter})
                    .then( ({data}) => {
                        this.dataDetail = data;
                    })
            },
            openModal(data){
                $('#openModal').modal({backdrop: 'static', keyboard: false});
                this.form.stase_task_id = data.id;
            },
            openStase(){
                this.$Progress.start();
                this.form.post('/cmsr/open-stase-task')
                    .then((response) => {
                        Fire.$emit('ModalSuccess');
                    }).catch((error) => {
                    console.log(error)
                });
            },
            pagination(page = 1) {
                this.filter.page = page;
                axios.get(this.base_url, {params: this.filter})
                    .then(response => {
                        this.dataDetail = response.data;
                        this.filter.page = response.data.current_page
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
            addModal(data){
                this.form.fill(data);
                if (this.form.date){
                    this.form.date = data.date.slice(0,10);
                }
                this.editMode = false;
                $('#editAddModal').modal({backdrop: 'static', keyboard: false})
            },
            createData(){
                this.$Progress.start();
                this.form.post('/sadmin/add-score')
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
            detailModal(data){
                this.dataPoints = data.stase_task_log_point;
                this.dataPointNote = data.note;
                $('#pointModal').modal('show');
            },
            fileModal(data){
                this.detailFile = data;
                $('#detailModal').modal({backdrop: 'static', keyboard: false})
            },
            deleteData(data){
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
                        axios.post('/sadmin/reset-score/' + data.id).then((response) => {
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
                    toast: true,
                    showConfirmButton: false,
                    timer: 1000,
                })
            },
        },
        created(){
            this.$Progress.start();
            this.loadData();
            this.loadLecture();
            this.loadStudent();

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
