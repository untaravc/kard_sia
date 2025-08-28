<template>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark"><i class="fa fa-chalkboard-teacher"></i> Profile
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="row">
                <div class="col-md-3">

                    <!-- Profile Image -->
                    <div class="card card-danger card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <img class="profile-user-img img-fluid img-circle" v-if="user.lecture_profile && user.lecture_profile.image " :src="user.lecture_profile.image" alt="User profile picture">
                            </div>

                            <h3 class="profile-username text-center">{{user.name}}</h3>

                            <!--                            <p class="text-muted text-center">Sub Spesialis</p>-->

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Email</b> :<a class="float-right">{{user.email}}</a>
                                </li>
                            </ul>

                            <button class="btn btn-danger btn-block" @click="passwordModal"><b>Ubah Password</b></button>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
                <!-- /.col -->
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-body">
                            <nav>
                                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                    <a class="nav-item nav-link active" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="true">Profile</a>
                                    <a class="nav-item nav-link" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="false">Riwayat Penilaian</a>
                                    <a class="nav-item nav-link" id="nav-contact-tab" data-toggle="tab" href="#nav-contact" role="tab" aria-controls="nav-contact" aria-selected="false">Contact</a>
                                </div>
                            </nav>
                            <div class="tab-content" id="nav-tabContent">
                                <div class="tab-pane fade show active" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                                    <button class="mt-3 btn btn-info btn-sm float-right" v-if="!user.lecture_profile" @click="addModal">Tambah Profil</button>
                                    <button class="mt-3 btn btn-info btn-sm float-right" v-if="user.lecture_profile" @click="editModal">Ubah Profil</button>

                                    <div class="row" v-if="user.lecture_profile">
                                        <div class="col-md-12">
                                            <table class="table table-responsive">
                                                <tbody>
                                                <tr>
                                                    <td>Inisial</td><td>:</td><td>{{user.lecture_profile.code}}</td>
                                                </tr>
                                                <tr>
                                                    <td>Degree</td><td>:</td><td>{{user.lecture_profile.degree}}</td>
                                                </tr>
                                                <tr>
                                                    <td>Alamat</td><td>:</td><td>{{user.lecture_profile.address}}</td>
                                                </tr>
                                                <tr>
                                                    <td>Tempat Tgl Lahir</td><td>:</td><td>{{user.lecture_profile.pob}}, {{user.lecture_profile.dob}}</td>
                                                </tr>
                                                <tr>
                                                    <td>No Telepon</td><td>:</td><td>{{user.lecture_profile.phone}}</td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade " id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                                    <div class="row">
                                        <div class="col-12 table-responsive">
                                            <table class="table">
                                                <tr v-for="(data,d) in scoring.data" :key="d">
                                                    <td>{{scoring.per_page * (scoring.current_page - 1) + d + 1}}</td>
                                                    <td>
                                                        <b v-if="data.student">{{data.student.name}}</b><br>
                                                        <span v-if="data.task">{{data.task.name}}</span>
                                                        <span v-if="data.title">: {{data.title}}</span><br>
                                                        <small>{{data.updated_at | formatDateTime}}</small>
                                                    </td>
                                                    <td>
                                                        Nilai : {{data.point_average}}
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-sm-12 col-md-5">
                                            <div class="dataTables_info" role="status" aria-live="polite">
                                                Menampilkan data {{scoring.from}} hingga {{scoring.to}} dari {{scoring.total}} data.
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-7">
                                            <div class="dataTables_paginate paging_simple_numbers" style="float: right">
                                                <pagination :data="scoring" @pagination-change-page="pagination"></pagination>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">...</div>
                            </div>
                        </div><!-- /.card-body -->
                    </div>
                    <!-- /.nav-tabs-custom -->
                </div>

                <div class="modal fade bd-example-modal-lg" id="scoreModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" v-show="!editMode">Tambah</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                <button :disabled="disable" v-show="!editMode" type="submit" class="btn btn-primary" @click="createData">
                                    <span v-if="disable" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    Simpan
                                </button>
                            </div>
                        </div>
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
                editMode: false,
                disable:false,
                taskLog:false,
                scoring: {},
                dataRaw: {
                    image_url: '',
                },
                options: {
                    format: 'YYYY-MM-DD',
                    useCurrent: false,
                },
                reset:new form({
                    id:'',
                    email:'',
                    password:'',
                    password_confirmation:'',
                }),
                user:new form({
                    id:'',
                    name:'',
                    email:'',
                    lecture_profile:{},
                }),
                profile: new form({
                    id:'',
                    lecture_id:'',
                    code:'',
                    degree:'',
                    image:'',
                    pob:'',
                    dob:'',
                    phone:'',
                    address:'',
                }),
                filter: {
                    page: 1
                }
            }
        },
        methods:{
            loadData(){
                axios.get('/sadmin/lectures/' + this.$route.params.id)
                    .then( ({data}) => {
                        this.user.fill(data);
                        this.taskLog = data.stase_task_logs;
                        this.reset.fill(data);
                        if(data.lecture_profile !== null){
                            this.editMode = true;
                            this.profile.fill(data.lecture_profile);
                        }
                    })
            },
            loadRiwayatPenilaian(){
                axios.get('/sadmin/lecture_scoring/'+this.$route.params.id, {params: this.filter})
                .then(({data})=>{
                    this.scoring = data;
                })
            },
            passwordModal(){
                $('#passwordModal').modal({backdrop: 'static', keyboard: false})
            },
            pagination(page = 1) {
                this.filter.page = page
                this.loadRiwayatPenilaian()
            },
            updatePassword(){
                this.disable = true;
                this.reset.post('/cmsd/update_password')
                    .then((response)=>{
                        Fire.$emit('ModalSuccess');
                        this.disable = false;
                    }).catch((error) => {
                    this.disable = false;
                });
            },
            addModal(){
                this.editMode = false;
                $('#editAddModal').modal({backdrop: 'static', keyboard: false})
            },
            openModal(data){
                $('#openModal').modal({backdrop: 'static', keyboard: false});
                this.form.stase_task_id = data.id;
            },
            getImage(e){
                let file = e.target.files[0];

                let reader = new FileReader();
                if (file['size'] < 2200000){
                    reader.onloadend = (file)=>{
                        this.profile.image = reader.result;
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
            createData(){
                this.$Progress.start();
                this.profile.lecture_id = this.user.id;
                this.disable = true;
                this.profile.post('/cmsd/profiles')
                    .then((response) => {
                        Fire.$emit('ModalSuccess');
                        this.disable = false;
                    }).catch((error) => {
                    this.disable = false;
                });
            },
            editModal(){
                this.editMode = true;
                this.dataRaw.image_url = '';
                $('#editAddModal').modal({backdrop: 'static', keyboard: false});
            },
            updateData(){
                this.$Progress.start();
                this.disable = true;
                this.profile.patch('/cmsd/profiles/'+this.profile.id)
                    .then((response) => {
                        Fire.$emit('ModalSuccess');
                        this.disable = false;
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
            this.loadRiwayatPenilaian();

            Fire.$on('ModalSuccess', () => {
                this.loadData();
                this.popSuccessSwal();
                $('#openModal').modal('hide');
                $('#editAddModal').modal('hide');
                $('#passwordModal').modal('hide');
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
