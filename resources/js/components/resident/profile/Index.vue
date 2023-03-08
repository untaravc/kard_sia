<template>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark"><i class="fa fa-user-cog"></i> Profile
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="row">
                <div class="col-md-3">

                    <!-- Profile Image -->
                    <div class="card card-info card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                            </div>

                            <h3 class="profile-username text-center">{{user.name}}</h3>

                            <p class="text-muted text-center">Sub Spesialis</p>

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Email</b> :<a class="float-right">{{user.email}}</a>
                                </li>
                            </ul>

                            <button class="btn btn-info btn-block" @click="passwordModal"><b>Ubah Password</b></button>
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
                                    <a class="nav-item nav-link" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="false">Home</a>
                                    <a class="nav-item nav-link" id="nav-contact-tab" data-toggle="tab" href="#nav-contact" role="tab" aria-controls="nav-contact" aria-selected="false">Contact</a>
                                </div>
                            </nav>
                            <div class="tab-content" id="nav-tabContent">
                                <div class="tab-pane fade show active" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                                    <button class="mt-3 btn btn-info btn-sm float-right" v-if="!user.student_profile" @click="addModal">Tambah Profil</button>
                                    <button class="mt-3 btn btn-info btn-sm float-right" v-if="user.student_profile" @click="editModal">Ubah Profil</button>

                                    <div class="row" v-if="user.student_profile">
                                        <div class="col-md-12">
                                            <table class="table table-responsive">
                                                <tbody>
                                                <tr>
                                                    <td>Inisial</td><td>:</td><td>{{user.student_profile.code}}</td>
                                                </tr>
                                                <tr>
                                                    <td>Degree</td><td>:</td><td>{{user.student_profile.degree}}</td>
                                                </tr>
                                                <tr>
                                                    <td>Alamat</td><td>:</td><td>{{user.student_profile.address}}</td>
                                                </tr>
                                                <tr>
                                                    <td>Tempat Tgl Lahir</td><td>:</td><td>{{user.student_profile.pob}}, {{user.student_profile.dob}}</td>
                                                </tr>
                                                <tr>
                                                    <td>No Telepon</td><td>:</td><td>{{user.student_profile.phone}}</td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade " id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">...</div>
                                <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">...</div>
                            </div>
                        </div><!-- /.card-body -->
                    </div>
                    <!-- /.nav-tabs-custom -->
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
                                        <div class="form-group">
                                            <label>Inisial</label>
                                            <input v-model="profile.code" type="text"
                                                   class="form-control" :class="{ 'is-invalid': profile.errors.has('code') }">
                                            <has-error :form="profile" field="code"></has-error>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Degree</label>
                                            <input v-model="profile.degree" type="text"
                                                   class="form-control" :class="{ 'is-invalid': profile.errors.has('degree') }">
                                            <has-error :form="profile" field="degree"></has-error>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tempat Lahir</label>
                                            <input v-model="profile.pob" type="text"
                                                   class="form-control" :class="{ 'is-invalid': profile.errors.has('pob') }">
                                            <has-error :form="profile" field="pob"></has-error>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tanggal Lahir</label>
                                            <input v-model="profile.dob" type="date"
                                                   class="form-control" :class="{ 'is-invalid': profile.errors.has('pob') }">
                                            <has-error :form="profile" field="dob"></has-error>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>No Telepon</label>
                                            <input v-model="profile.phone" type="text"
                                                   class="form-control" :class="{ 'is-invalid': profile.errors.has('phone') }">
                                            <has-error :form="profile" field="phone"></has-error>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Alamat</label>
                                            <input v-model="profile.address" type="text"
                                                   class="form-control" :class="{ 'is-invalid': profile.errors.has('address') }">
                                            <has-error :form="profile" field="address"></has-error>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                <button v-show="!editMode" type="submit" class="btn btn-primary" @click="createData">Simpan</button>
                                <button v-show="editMode" type="submit" class="btn btn-primary" @click="updateData">Ubah</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade bd-example-modal-lg" id="passwordModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Ubah Password dan Email</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input v-model="reset.email" type="email"
                                                   class="form-control" :class="{ 'is-invalid': reset.errors.has('email') }">
                                            <has-error :form="reset" field="email"></has-error>
                                        </div>
                                    </div>
                                    <div class="col-md-6"></div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Password Baru</label>
                                            <input v-model="reset.password" type="password" name="password"
                                                   class="form-control" autocomplete="new-password" :class="{ 'is-invalid': reset.errors.has('password') }">
                                            <has-error :form="reset" field="password"></has-error>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Konfirmasi Password</label>
                                            <input v-model="reset.password_confirmation" type="password"
                                                   class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                <button type="submit" class="btn btn-primary" @click="updatePassword">Ubah</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.col -->
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
                    student_profile:{},
                }),
                profile: new form({
                    id:'',
                    student_id:'',
                    code:'',
                    degree:'',
                    pob:'',
                    dob:'',
                    phone:'',
                    address:'',
                }),
            }
        },
        methods:{
            loadData(){
                axios.get('/cmsr/user')
                    .then( ({data}) => {
                        this.user.fill(data);
                        this.reset.id = data.id;
                        this.reset.email = data.email;

                        if(data.student_profile !== null){
                            this.editMode = true;
                            this.profile.fill(data.student_profile);
                        }
                    })
            },
            passwordModal(){
                $('#passwordModal').modal({backdrop: 'static', keyboard: false})
            },
            updatePassword(){
                this.reset.post('/cmsr/update_password')
                .then((response)=>{
                    Fire.$emit('ModalSuccess');
                }).catch((error) => {
                    console.log(error)
                });
            },
            addModal(){
                this.editMode = false;
                $('#editAddModal').modal({backdrop: 'static', keyboard: false})
            },
            createData(){
                this.$Progress.start();
                this.profile.student_id = this.user.id;
                this.profile.post('/cmsr/profiles')
                    .then((response) => {
                        Fire.$emit('ModalSuccess');
                    }).catch((error) => {
                        console.log(error)
                    });
            },
            editModal(){
                this.editMode = true;
                $('#editAddModal').modal({backdrop: 'static', keyboard: false});
            },
            updateData(){
                this.$Progress.start();
                this.profile.patch('/cmsr/profiles/'+this.profile.id)
                    .then((response) => {
                        Fire.$emit('ModalSuccess');
                    }).catch((error) => {
                    console.log(error)
                });
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

            Fire.$on('ModalSuccess', () => {
                this.loadData();
                this.popSuccessSwal();
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
