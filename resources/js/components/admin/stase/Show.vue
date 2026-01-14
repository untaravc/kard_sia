<template>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark"><i class="fa fa-chalkboard-teacher"></i> Detail Stase {{dataDetail.name}}
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h1 class="card-title">Tugas Stase : {{dataDetail.name}}</h1>
                            <div class="card-tools">
                                <button class="btn btn-sm btn-success" @click="addModal">Tambah Tugas</button>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-head-fixed">
                                <tr>
                                    <th>No</th>
                                    <th>Kategori</th>
                                    <th>Nama</th>
                                    <th>Dosen Pembimbing</th>
                                    <th>Status</th>
                                    <th><span style="float: right">Aksi</span></th>
                                </tr>
                                <tbody>
                                <tr role="row" class="odd" v-for="(data, i) in dataDetail.stase_tasks" :key="data.id">
                                    <td>{{i+1}} </td>
                                    <td>{{data.task.name}}</td>
                                    <td>{{data.name}}</td>
                                    <td><span v-if="data.lecture">{{data.lecture.name}}</span></td>
                                    <td>{{data.status === 1 ? 'Aktif' : 'Non-Aktif'}}</td>
                                    <td class="action-button">
<!--                                        <button class="btn btn-sm btn-danger" @click="deleteData(data.id)">-->
<!--                                            <i class="fa fa-trash"></i>-->
<!--                                        </button>-->
                                        <button class="btn btn-sm btn-success" @click="editModal(data)">
                                            <i class="fa fa-edit" ></i>
                                        </button>
                                    </td>
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
                                <div class="col-md-6">
                                    <label>Jenis Tugas</label>
                                    <select @change="copyText" v-model="form.task_id" id="select-type" class="form-control" :class="{ 'is-invalid': form.errors.has('task_id') }">
                                        <option v-for="(data, i) in dataRaw.tasks" :key="i" :value="data.id" selected>{{data.name}}</option>
                                    </select>
                                    <has-error :form="form" field="task_id"></has-error>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nama Tugas</label>
                                        <input v-model="form.name" type="text"
                                               class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                                        <has-error :form="form" field="name"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label>Dosen Pembimbing</label>
                                    <select v-model="form.lecture_id" class="form-control" :class="{ 'is-invalid': form.errors.has('lecture_id') }">
                                        <<option :value="0" selected>Terbuka</option>
                                        <<option v-for="(data, i) in dataRaw.lectures" :key="i" :value="data.id" selected>{{data.name}}</option>
                                    </select>
                                    <has-error :form="form" field="lecture_id"></has-error>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select v-model="form.status"
                                                class="form-control" :class="{ 'is-invalid': form.errors.has('status') }">
                                            <option value="1">Aktif</option>
                                            <option value="0">Non-Aktif</option>
                                        </select>
                                        <has-error :form="form" field="status"></has-error>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button v-show="!editMode" type="submit" class="btn btn-primary" @click="createData">Simpan</button>
                        <button v-show="editMode" type="submit" class="btn btn-primary" @click="updateData">Ubah</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade bd-example-modal-lg" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table">
                                    <tr><td>Nama</td><td>:</td><td>{{dataDetail.name}}</td></tr>
                                    <tr><td>Deskripsi</td><td>:</td><td>{{dataDetail.desc}}</td></tr>
                                    <tr><td>Created At</td><td>:</td><td>{{dataDetail.created_at}}</td></tr>
                                </table>
                            </div>
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
                base_url: '/sadmin/stases-tasks',
                editMode : false,
                dataDetail: {},
                dataRaw: {
                    image_url: '',
                    lectures: {},
                    tasks: {},
                },
                form: new form({
                    id:'',
                    stase_id: this.$route.params.id,
                    lecture_id:'',
                    task_id:'',
                    name:'',
                    status: 1,
                }),
            }
        },
        methods:{
            loadData(){
                axios.get('/sadmin/stases/'+this.$route.params.id)
                    .then( ({data}) => ( this.dataDetail = data))
            },
            loadTasks(){
                axios.get('/sadmin/get-tasks')
                    .then( ({data}) => ( this.dataRaw.tasks = data))
            },
            copyText(){
                let text = $("#select-type option:selected").html()  ;
                this.form.name = text;
            },
            loadLectures(){
                axios.get('/sadmin/get-lectures')
                    .then( ({data}) => ( this.dataRaw.lectures = data))
            },
            pagination(page = 1) {
                this.$Progress.start();
                axios.get(this.base_url+'?page=' + page)
                    .then(response => {
                        this.dataContent = response.data;
                        this.filter.page = response.data.current_page
                    });
                this.$Progress.finish()
            },
            withFilter(){
                this.$Progress.start();
                axios.get(this.base_url, {
                    params: this.filter
                }).then((response) => {
                    this.dataContent = response.data
                    this.$Progress.finish();
                });
            },
            addModal(){
                this.form.reset();
                this.editMode = false;
                $('#editAddModal').modal({backdrop: 'static', keyboard: false})
            },
            createData(){
                this.$Progress.start();
                this.form.post('/sadmin/stase-tasks')
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
                this.form.patch("/sadmin/stase-tasks/"+this.form.id)
                    .then((response) => {
                        Fire.$emit('ModalSuccess');
                    }).catch((error) => {
                    console.log(error)
                });
            },
            detailModal(data){
                $('#detailModal').modal('show');
                this.dataDetail = data;
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
                        this.form.delete('/sadmin/stase-tasks/' + id).then((response) => {
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
            this.loadTasks();
            this.loadLectures();
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
