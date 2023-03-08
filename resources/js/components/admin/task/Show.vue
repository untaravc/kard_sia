<template>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark"><i class="fa fa-chalkboard-teacher"></i> Lembar Penilaian {{dataDetail.name}}
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
                            <h1 class="card-title">Poin Penilaian</h1>
                            <div class="card-tools">
                                <button class="btn btn-sm btn-success" @click="addModal">Add New</button>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-head-fixed">
                                <tr>
                                    <th>Urutan</th>
                                    <th>Nama</th>
<!--                                    <th>Tipe</th>-->
                                    <th><span style="float: right">Aksi</span></th>
                                </tr>
                                <tbody>
                                <tr role="row" class="odd" v-for="(data, i) in dataContent.data" :key="data.id">
                                    <td>{{data.order}}</td>
                                    <td>{{data.name}}</td>
                                    <td class="action-button">
                                        <button class="btn btn-sm btn-danger" @click="deleteData(data.id)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                        <button class="btn btn-sm btn-success" @click="editModal(data)">
                                            <i class="fa fa-edit" ></i>
                                        </button>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-sm-12 col-md-5">
                                    <div class="dataTables_info" role="status" aria-live="polite">
                                        Menampilkan data {{dataContent.from}} hingga {{dataContent.to}} dari {{dataContent.total}} data.
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-7">
                                    <div class="dataTables_paginate paging_simple_numbers" style="float: right">
                                        <pagination :data="dataContent" @pagination-change-page="pagination"></pagination>
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
                            <div class="col-md-10">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input v-model="form.name" type="text"
                                           class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                                    <has-error :form="form" field="name"></has-error>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label>Urutan</label>
                                <input v-model="form.order" type="text"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('order') }">
                                <has-error :form="form" field="order"></has-error>
                            </div>
<!--                            <div class="col-md-6">-->
<!--                                <label>Tipe</label>-->
<!--                                <div class="form-check">-->
<!--                                    <input class="form-check-input" v-model="form.type" type="radio" name="type" id="option" value="option">-->
<!--                                    <label class="form-check-label" for="option">-->
<!--                                        Pilihan-->
<!--                                    </label>-->
<!--                                </div>-->
<!--                                <div class="form-check">-->
<!--                                    <input class="form-check-input" v-model="form.type" type="radio" name="type" id="text" value="text">-->
<!--                                    <label class="form-check-label" for="text">-->
<!--                                        Isian-->
<!--                                    </label>-->
<!--                                </div>-->
<!--                                <has-error :form="form" field="type"></has-error>-->
<!--                            </div>-->
                        </div>
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
                base_url: '/sadmin/task-details',
                editMode : false,
                dataContent: {},
                dataDetail: {},
                dataRaw: {
                    image_url: '',
                    stases: {},
                },
                form: new form({
                    id:'',
                    task_id: this.$route.params.id,
                    name:'',
                    type:'',
                    order:'',
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
            loadDataContent(){
                axios.get('/sadmin/task-detail/'+this.$route.params.id)
                    .then( ({data}) => ( this.dataContent = data))
            },
            loadData(){
                axios.get('/sadmin/tasks/'+this.$route.params.id)
                    .then( ({data}) => ( this.dataDetail = data))
            },
            getImage(e){
                let file = e.target.files[0];

                let reader = new FileReader();
                if (file['size'] < 2200000){
                    reader.onloadend = (file)=>{
                        this.form.image = reader.result;
                    };
                    this.dataRaw.image_url = URL.createObjectURL(file);
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
            resetData(){
                this.filter.reset();
                this.loadDataContent()
            },
            addModal(){
                this.form.reset();
                this.editMode = false;
                $('#editAddModal').modal({backdrop: 'static', keyboard: false})
            },
            createData(){
                this.$Progress.start();
                this.form.post(this.base_url)
                    .then((response) => {
                        Fire.$emit('ModalSuccess');
                    }).catch((error) => {
                    console.log(error)
                });
            },
            editModal(data){
                this.editMode = true;
                this.dataRaw.image_url = '';
                $('#editAddModal').modal({backdrop: 'static', keyboard: false});
                this.form.fill(data);
            },
            updateData(){
                this.$Progress.start();
                this.form.patch(this.base_url+'/'+this.form.id)
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
                        this.form.delete(this.base_url+'/' + id).then((response) => {
                            this.loadDataContent();
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
            this.loadDataContent();
            this.loadData();
            Fire.$on('ModalSuccess', () => {
                this.loadDataContent();
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
