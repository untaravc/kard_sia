<template>
    <div class="content-wrapper">
        <div class="content">
            <div class="row">
                <div class="col-12">
                    <div class="card mt-2">
                        <div class="card-header">
                            <h1 class="card-title">Data Dosen</h1>
                            <div class="card-tools">
                                <button class="btn btn-sm btn-th-primary" @click="addModal">
                                    <i class="fa fa-user-plus"></i> Tambah
                                </button>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <vue-loader :active.sync="is_loading" :can-cancel="false" loader="dots" :is-full-page="false"></vue-loader>
                            <table class="table table-head-fixed">
                                <tr>
                                    <th width="50px">No</th>
                                    <th width="45%">Nama</th>
                                    <th width="30%">Data</th>
                                    <th><span style="float: right">Aksi</span></th>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>
                                        <input type="text" v-model="filter.name" @keyup.enter="loadDataContent" class="form-control" placeholder="Cari nama..">
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tbody>
                                <tr role="row" class="odd" v-for="(data, i) in dataContent.data" :key="data.id">
                                    <td><span :title="data.id">{{dataContent.per_page * (dataContent.current_page - 1) + i + 1}} </span></td>
                                    <td>
                                        <span v-if="data.status === 'active'">
                                            <i class="fa fa-check-circle text-success"></i></span>
                                        <span v-if="data.status !== 'active'">
                                            <i class="fa fa-minus-circle text-secondary"></i>
                                        </span>
                                        <b>{{data.name}}</b>
                                        <br>
                                        <a :href="`/sadmin/login-lecture/`+data.id" target="_blank" >
                                            {{data.email}}
                                        </a>
                                        <small class="text-black-50">{{data.last_act | formatDateTime}}</small>
                                    </td>
                                    <td>
                                        <small>Penilaian : {{data.stase_task_logs_count}} </small>
                                        <small v-if="data.open_stase_task_count > 0">({{data.open_stase_task_count}})</small>
                                        <br>
                                        <small>Presensi : {{data.activity_lectures_count}}</small>
                                    </td>
                                    <td class="action-button">
<!--                                        <button class="btn btn-sm btn-danger" @click="deleteData(data.id)">-->
<!--                                            <i class="fa fa-trash"></i>-->
<!--                                        </button>-->
                                        <button class="btn btn-wd btn-sm btn-th-primary" @click="editModal(data)">
                                            Ubah
                                        </button>
                                        <router-link :to="`/cmss/lecture/`+data.id" class="btn btn-wd btn-sm btn-th-secondary" >
                                            Detail
                                        </router-link>
<!--                                        <a :href="`/sadmin/login-lecture/`+data.id" target="_blank" class="btn btn-sm btn-info" >-->
<!--                                            <i class="fa fa-upload"></i>-->
<!--                                        </a>-->
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
                                        <pagination :data="dataContent" @pagination-change-page="loadDataContent"></pagination>
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
                        <form @submit.prevent="createData">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input v-model="form.email" type="email"
                                               class="form-control" :class="{ 'is-invalid': form.errors.has('email') }">
                                        <has-error :form="form" field="email"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Password</label>
                                        <input v-model="form.password" type="password"
                                               class="form-control" :class="{ 'is-invalid': form.errors.has('password') }">
                                        <i v-show="editMode">Biarkan kosong bila tidak akan mengganti password</i>
                                        <has-error :form="form" field="password"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nama</label>
                                        <input v-model="form.name" type="text"
                                               class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                                        <has-error :form="form" field="name"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label>Nama & Gelar</label>
                                    <input v-model="form.name_alt" type="text"
                                           class="form-control" :class="{ 'is-invalid': form.errors.has('name_alt') }">
                                    <has-error :form="form" field="name_alt"></has-error>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Tutup</button>
                        <button v-show="!editMode" type="submit" class="btn btn-sm btn-th-primary" @click="createData">Simpan</button>
                        <button v-show="editMode" type="submit" class="btn btn-sm btn-th-primary" @click="updateData">Ubah</button>
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
                base_url: '/sadmin/lectures',
                editMode : false,
                is_loading : false,
                dataContent: {},
                dataDetail: '',
                dataRaw: {
                    image_url: ''
                },
                form: new form({
                    id:'',
                    email:'',
                    password:'',
                    name:'',
                    name_alt:'',
                    role:'',
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
            loadDataContent(page = 1 ){
                this.filter.page = page
                this.is_loading = true
                axios.get(this.base_url, {params: this.filter})
                    .then( ({data}) => {
                        this.dataContent = data;
                        this.is_loading = false
                    })
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
        watch:{
            'filter.keyword': function () {
                this.loadDataContent()
            }
        },
        components: {
            Datepicker,
        }
    }
</script>
