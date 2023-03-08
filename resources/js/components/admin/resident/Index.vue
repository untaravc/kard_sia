<template>
    <div class="content-wrapper">
        <div class="content">
            <div class="row">
                <div class="col-12">
                    <div class="card mt-2">
                        <div class="card-header">
                            <h1 class="card-title">Data Residen</h1>
                            <div class="card-tools">
                                <button class="btn btn-sm btn-th-primary" @click="addModal"><i class="fa fa-user-plus"></i> Tambah</button>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <vue-loader :active.sync="is_loading" :can-cancel="false" loader="dots" :is-full-page="false"></vue-loader>
                            <table class="table table-head-fixed">
                                <tr>
                                    <th style="width: 50px">No</th>
                                    <th style="width: 35%">Nama</th>
                                    <th >Data</th>
                                    <th >Presensi</th>
                                    <th >Penilaian</th>
                                    <th style="width: 70px"><span style="float: right">Aksi</span></th>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>
                                        <input type="text" v-model="filter.name" @keyup.enter="loadDataContent" class="form-control" placeholder="Cari nama..">
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tbody>
                                <tr role="row" class="odd" v-for="(data, i) in dataContent.data" :key="data.id">
                                    <td>
                                        <span :title="data.id">{{dataContent.per_page * (dataContent.current_page - 1) + i + 1}} </span>
                                    </td>
                                    <td>
                                        <i v-if="data.status === 'active'" class="fa fa-check-circle text-success"></i>
                                        <i v-if="data.status !== 'active'" class="fa fa-check-circle text-secondary"></i>
                                        <b>{{data.name}}</b><br>
                                        <a :href="`/sadmin/login-student/`+data.id" target="_blank" >
                                            {{data.email}}
                                        </a> <i class="text-black-50">{{data.last_act | formatDateTime}}</i><br>
                                    </td>
                                    <td>
                                        Angkatan :  {{data.year}} <br>
                                        <small v-if="data.stase_logs_active">
                                            <i>{{data.stase_logs_active.stase.desc}}</i> :{{data.stase_logs_active.stase.name}}
                                        </small>
                                    </td>
                                    <td>
                                        <small>Presensi : </small>
                                        <small v-if="data.today_presence && data.today_presence.status === 'on'">
                                            <i class="fa fa-check-circle text-success"></i> {{data.today_presence.checkin | formatTime}}</small>
                                        <small v-if="data.today_presence && data.today_presence.status === 'off'">izin</small>
                                        <small v-if="!data.today_presence"><i>belum absen</i></small> <br>
                                        <small>Presensi Agenda : {{data.today_activity_count}}</small>
                                    </td>
                                    <td>
                                        <small>Jadwal Penilaian : {{data.open_stase_task_count}}</small> <br>
                                        <small>Logbook Pending : {{data.student_log_pending_count}}</small>
                                    </td>
                                    <td class="action-button">
                                        <div class="dropdown show">
                                            <a class="btn btn-light" href="#" role="button" data-toggle="dropdown"
                                               aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-ellipsis-v"></i>
                                            </a>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                                <a class="dropdown-item" href="#" @click="editModal(data)">Edit Data</a>
                                                <router-link class="dropdown-item":to="`/cmss/resident/`+data.id">Stase</router-link>
                                                <router-link class="dropdown-item" :to="`/cmss/resident-score/`+data.id">Nilai</router-link>
                                                <router-link class="dropdown-item" :to="`/cmss/resume-presence/`+data.id">Presensi</router-link>
                                            </div>
                                        </div>
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
                                        <pagination :data="dataContent" :limit="2" @pagination-change-page="pagination"></pagination>
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
                                        <span v-show="editMode">Leave empty if you will not change the password</span>
                                        <has-error :form="form" field="password"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nama</label>
                                        <input v-model="form.name" type="text" name="name"
                                               class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                                        <has-error :form="form" field="name"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label>Status</label>
                                    <select v-model="form.status" class="form-control" :class="{ 'is-invalid': form.errors.has('status') }" name="">
                                        <option value="active" selected>Aktif</option>
                                        <option value="nonactive">Non Aktif</option>
                                    </select>
                                    <has-error :form="form" field="status"></has-error>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tahun Masuk</label>
                                        <input v-model="form.year" type="text" placeholder="ex: 2020-08"
                                               class="form-control" :class="{ 'is-invalid': form.errors.has('year') }">
                                        <has-error :form="form" field="year"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>NIK</label>
                                        <input v-model="form.code" type="text"
                                               class="form-control" :class="{ 'is-invalid': form.errors.has('code') }">
                                        <has-error :form="form" field="code"></has-error>
                                    </div>
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
    export default {
        data(){
            return {
                base_url: '/sadmin/students',
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
                    status:'',
                    year:'',
                    code:'',
                }),
                filter: new form({
                    name: '',
                    page: ''
                })
            }
        },
        methods:{
            loadDataContent(page = 1){
                this.filter.page = page;
                this.is_loading = true;
                axios.get(this.base_url, {params: this.filter})
                    .then( ({data}) => {
                        this.dataContent = data;
                        this.is_loading = false;
                    })
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
                    toast: true,
                    showConfirmButton: false,
                    timer: 1000,
                })
            },
        },
        created(){
            this.loadDataContent();
            Fire.$on('ModalSuccess', () => {
                this.loadDataContent();
                this.popSuccessSwal();
                $('#editAddModal').modal('hide');
                this.$Progress.finish();
            })
        },
    }
</script>
