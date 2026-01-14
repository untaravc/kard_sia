<template>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark"><i class="fa fa-chalkboard-teacher"></i> Dashboard
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="row">
                <div class="col-md-5">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="d-flex justify-content-center" v-if="user.image">
                                <div class="photo-profile"
                                     :style="'background-image: url(/storage/' + user.image + ')'">
                                </div>
                            </div>
                            <div class="d-flex justify-content-center" v-if="!user.image">
                                <div class="photo-profile"
                                     :style="'background-image: url(/assets/images/dr_default.jpeg)'">
                                </div>
                            </div>
                            <h6 class="text-center mt-1 mb-0">{{ user.name }}</h6>
                            <p class="text-muted text-center">{{ user.email }}</p>
                            <div class="row">
                                <div class="col-12 text-center mb-3">
                                    <span class="badge-success badge-cs" @click="profileModal">
                                        <i class="fa fa-user-edit"></i> Perbarui Profil
                                    </span>

                                    <router-link to="/dosen/ksm-schedules" class="badge-primary badge-cs">
                                        <i class="fa fa-table"></i> Jadwal Tindakan
                                    </router-link>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col text-center" style="border-right: 1px solid #d6d6d6">
                                    <b class="text-primary"
                                       style="font-size: larger">{{ dataRaw.info_cards.avg_scoring }}</b> <br>
                                    <small>Jumlah Menilai</small>
                                </div>
                                <div class="col text-center">
                                    <b class="text-primary"
                                       style="font-size: larger">{{ dataRaw.info_cards.scoring }}</b> <br>
                                    <small>Rata-rata Menilai</small>
                                </div>
                                <div class="col text-center" style="border-left: 1px solid #d6d6d6">
                                    <b class="text-primary"
                                       style="font-size: larger">{{ dataRaw.info_cards.act_lect }}</b> <br>
                                    <small>Presensi Agenda</small>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col text-center pointer py-3" style="border-right: 1px solid #d6d6d6">
                                    <div>
                                        <span class="position-relative">
                                            <i class="fa fa-calendar-check text-primary" style="font-size: 30px"></i>
                                            <span class="badge badge-secondary position-absolute"
                                                  style="bottom: -5px; left: 15px">{{schedules.length}}</span>
                                        </span>
                                        <br>
                                        <small>Agenda Hari Ini</small>
                                    </div>
                                </div>
                                <div class="col text-center pointer py-3">
                                    <div>
                                        <span class="position-relative">
                                            <i class="fa fa-edit text-secondary" style="font-size: 30px"></i>
                                            <span class="badge badge-danger position-absolute"
                                                  style="bottom: -5px; left: 15px">{{score_pending}}</span>
                                        </span>
                                        <br>
                                        <small>Penilaian</small>
                                    </div>
                                </div>
                                <div class="col text-center pointer py-3" style="border-left: 1px solid #d6d6d6">
                                    <router-link to="/dosen/resident-logs">
                                        <span class="position-relative">
                                            <i class="fa fa-book-medical text-warning" style="font-size: 30px"></i>
                                            <span class="badge badge-danger position-absolute"
                                                  style="bottom: -5px; left: 15px">{{ dataRaw.info_cards.log_pending }}</span>
                                        </span>
                                        <br>
                                        <small>Logbook Approval</small>
                                    </router-link>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="card card-outline card-primary">
                        <div class="card-body table-responsive p-0">
                            <table class="table table-head-fixed">
                                <tr>
                                    <td>
                                        <a target="_blank" style="text-decoration: underline"
                                           href="https://drive.google.com/drive/folders/1LoCN_taJgB37eHhdc9HY1IplL8N5jjBV?usp=sharing">
                                            Kurikulum dan Standar Operasional
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Agenda Hari Ini</h3>
                            <div class="card-tools">
                                <router-link to="/dosen/activities" class="btn btn-sm btn-th-dark">
                                    <i class="fa fa-calendar-alt"></i>
                                    Semua agenda
                                </router-link>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table v-if="schedules && schedules[0]" class="table">
                                <tr v-for="(sch, s) in schedules" :key="s">
                                    <td>
                                        {{ sch.name }}<br>
                                        <small>
                                            <b v-if="sch.speaker">{{ sch.speaker }}:</b> {{ sch.title }} <br>
                                            <i v-if="sch.absence">
                                                <i class="fa fa-check-circle text-success"></i> Anda telah presensi pada
                                                {{ sch.absence.created_at | formatDateTime }}
                                            </i>
                                        </small>
                                    </td>
                                    <td class="text-right">
                                        <a :href="'/presensi/' + sch.id" v-if="!sch.absence"
                                           class="btn btn-wd btn-sm btn-success">Presensi</a>
                                    </td>
                                </tr>
                            </table>

                            <div v-if="schedules && !schedules[0]" class="p-3">
                                <i>Tidak ada agenda</i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="card card-primary card-outline">
                        <div class="card-body p-0 table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Penilaian</th>
                                    <th class="text-right">Proses</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-if="dataContent && dataContent[0]" v-for="(data, i) in dataContent" :key="i">
                                    <td>
                                        <span class="badge badge-primary"
                                              v-if="data.stase_task && data.stase_task.stase">#{{ data.stase_task.stase.name }}</span><br>
                                        <b v-if="data.student">{{ data.student.name }}</b><br>
                                        <small v-if="data.stase_task">{{ data.stase_task.name }}</small> :
                                        <small>{{ data.title }}</small>
                                        <div v-if="data.files && data.files[0]">
                                            <button v-for="(file, f) in data.files" :key="f"
                                                    style="padding: 0.15rem 0.3rem;"
                                                    class="btn btn-outline-secondary m-1 btn-sm btn-rounded"
                                                    @click="detailModal(file)">
                                                <small>{{ file.title | truncate(12) }} </small>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="text-right" style="width:120px">
                                        <div class="d-block text-center">
                                            <span style="color: green; font-size: 26px; font-weight: 700;"
                                                  v-if="data.data">
                                                {{ data.data.point_average }}
                                            </span>
                                        </div>

                                        <router-link
                                            v-if="!data.data && (data.stase_task.task.desc != 'nilai-tesis' && data.stase_task.task.desc != 'nilai-proposal')"
                                            :to="`/dosen/task/scoring/`+data.id"
                                            class="btn btn-wd btn-th-primary btn-sm">
                                            <span> Nilai</span>
                                        </router-link>
                                        <router-link
                                            v-if="data.data && (data.stase_task.task.desc != 'nilai-tesis' && data.stase_task.task.desc != 'nilai-proposal')"
                                            :to="`/dosen/task/scoring/`+data.id" class="btn btn-wd btn-info btn-sm">
                                            <span> Perbarui</span>
                                        </router-link>

                                        <!--                                        NILAI TESIS-->
                                        <router-link v-if="!data.data && data.stase_task.task.desc === 'nilai-tesis'"
                                                     :to="`/dosen/task/nilai-tesis/`+data.id"
                                                     class="btn btn-wd btn-th-primary btn-sm">
                                            <span> Nilai</span>
                                        </router-link>
                                        <router-link v-if="data.data && data.stase_task.task.desc == 'nilai-tesis'"
                                                     :to="`/dosen/task/nilai-tesis/`+data.id"
                                                     class="btn btn-wd btn-info btn-sm">
                                            <span> Perbarui</span>
                                        </router-link>

                                        <!--                                        Proposal-->
                                        <router-link v-if="!data.data && data.stase_task.task.desc === 'nilai-proposal'"
                                                     :to="`/dosen/task/nilai-proposal/`+data.id"
                                                     class="btn btn-wd btn-th-primary btn-sm">
                                            <span> Nilai</span>
                                        </router-link>
                                        <router-link v-if="data.data && data.stase_task.task.desc == 'nilai-proposal'"
                                                     :to="`/dosen/task/nilai-proposal/`+data.id"
                                                     class="btn btn-wd btn-info btn-sm">
                                            <span> Perbarui</span>
                                        </router-link>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4" v-if="dataContent && !dataContent[0]"><i>Tidak ada jadwal penilaian
                                        ujian</i></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer text-center">
                            <router-link to="/dosen/history"><u>Riwayat penilaian</u></router-link>
                        </div>
                    </div>
                    <div class="card" v-if="dataContentAll[0]">
                        <div class="card-header">
                            <h3 class="card-title">Penilaian Ujian Umum <i>(Data muncul di semua dashboard dosen)</i>
                            </h3>
                            <div class="card-tools">

                            </div>
                        </div>
                        <div class="card-body p-0 table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Ujian (judul)</th>
                                    <th>File</th>
                                    <th>Aksi</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(data, i) in dataContentAll" :key="i"
                                    v-if="dataContentAll && dataContentAll[0]">
                                    <td>{{ i + 1 }}</td>
                                    <td>
                                        <b v-if="data.student">{{ data.student.name }}</b><br>
                                        <span v-if="data.data">Nilai: </span><span class="badge-success badge"
                                                                                   v-if="data.data">{{
                                            data.data.point_average
                                        }}</span><br
                                        v-if="data.data">
                                        <i v-if="data.stase_task">#{{ data.stase_task.stase.name }}</i><br>
                                        <small v-if="data.stase_task">{{ data.stase_task.name }}</small> :
                                        <small>{{ data.title }}</small>
                                    </td>
                                    <td>
                                        <ol class="pl-3" v-if="data.files && data.files[0]">
                                            <li v-for="(file, f) in data.files" :key="f">
                                                <span style="text-decoration: underline; cursor:pointer"
                                                      @click="detailModal(file)">lampiran</span>
                                            </li>
                                        </ol>
                                        <i v-if="data.files && !data.files[0]">no files</i>
                                    </td>
                                    <td>
                                        <router-link
                                            v-if="!data.data && (data.stase_task.task.desc != 'nilai-tesis' && data.stase_task.task.desc != 'nilai-proposal')"
                                            :to="`/dosen/task/scoring/`+data.id"
                                            class="btn btn-wd btn-secondary btn-sm">
                                            <span> Nilai</span>
                                        </router-link>
                                        <router-link
                                            v-if="data.data && (data.stase_task.task.desc != 'nilai-tesis' && data.stase_task.task.desc != 'nilai-proposal')"
                                            :to="`/dosen/task/scoring/`+data.id" class="btn btn-wd btn-info btn-sm">
                                            <span> Perbarui</span>
                                        </router-link>

                                        <!--                                        NILAI TESIS-->
                                        <router-link v-if="!data.data && data.stase_task.task.desc === 'nilai-tesis'"
                                                     :to="`/dosen/task/nilai-tesis/`+data.id"
                                                     class="btn btn-wd btn-secondary btn-sm">
                                            <span> Nilai</span>
                                        </router-link>
                                        <router-link v-if="data.data && data.stase_task.task.desc == 'nilai-tesis'"
                                                     :to="`/dosen/task/nilai-tesis/`+data.id"
                                                     class="btn btn-wd btn-info btn-sm">
                                            <span> Perbarui</span>
                                        </router-link>
                                        <!--                                        Proposal-->
                                        <router-link v-if="!data.data && data.stase_task.task.desc === 'nilai-proposal'"
                                                     :to="`/dosen/task/nilai-proposal/`+data.id"
                                                     class="btn btn-wd btn-th-primary btn-sm">
                                            <span> Nilai</span>
                                        </router-link>
                                        <router-link v-if="data.data && data.stase_task.task.desc == 'nilai-proposal'"
                                                     :to="`/dosen/task/nilai-proposal/`+data.id"
                                                     class="btn btn-wd btn-info btn-sm">
                                            <span> Perbarui</span>
                                        </router-link>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="card-tools float-right">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="detailModal" tabindex="-1" role="dialog"
             aria-labelledby="myLargeModalLabel" aria-hidden="true">
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
                            <div class="col-12 text-center">
                                <h5 class="mb-1">{{ detailFile.title }}</h5>
                                <span>{{ detailFile.title }}</span>
                                <br>
                                <br>
                                <a class="btn btn-sm btn-primary btn-wd" target="_blank"
                                   :href="detailFile.link" download>
                                    <i class="fa fa-download"></i> Download
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="profileModal" tabindex="-1" role="dialog"
             aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Perbarui Profil</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Foto</label>
                                <input type="file" id="photo" @change="getImage"
                                       :class="{ 'is-invalid': user.errors.has('image') }">
                                <has-error :form="user" field="image"></has-error>
                            </div>
                            <div class="col-md-6">
                                <img :src="dataRaw.image_url" v-if="dataRaw.image_url" alt="" class="m-2"
                                     style="max-width: 100px">
                                <img :src="user.image" v-if="!dataRaw.image_url" alt="" class="m-2"
                                     style="max-width: 100px">
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input v-model="user.email" type="email"
                                           class="form-control"
                                           :class="{ 'is-invalid': user.errors.has('email') }">
                                    <has-error :form="user" field="email"></has-error>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama</label>
                                    <input v-model="user.name" type="email"
                                           class="form-control"
                                           :class="{ 'is-invalid': user.errors.has('name') }">
                                    <has-error :form="user" field="name"></has-error>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Password Baru</label>
                                    <input v-model="user.password" type="password" name="password"
                                           class="form-control" autocomplete="new-password"
                                           :class="{ 'is-invalid': user.errors.has('password') }">
                                    <has-error :form="user" field="password"></has-error>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Konfirmasi Password Baru</label>
                                    <input v-model="user.password_confirmation" type="password"
                                           class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>No Telepon</label>
                                    <input v-model="user.phone" type="email"
                                           class="form-control"
                                           :class="{ 'is-invalid': user.errors.has('phone') }">
                                    <has-error :form="user" field="phone"></has-error>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Alamat</label>
                                    <textarea v-model="user.address"
                                              class="form-control"
                                              :class="{ 'is-invalid': user.errors.has('address') }"></textarea>
                                    <has-error :form="user" field="address"></has-error>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" :disabled="disable" class="btn btn-success" @click="updateProfile"
                                data-dismiss="modal">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status"></span>
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Datepicker from 'vuejs-datepicker';

export default {
    data() {
        return {
            base_url: '/cmsr/stases',
            editMode: false,
            disable: false,
            dataContent: {},
            dataContentAll: {},
            dataDashboard: {},
            detailFile: {},
            schedules: {},
            scheduleDetail: {},
            dataRaw: {
                image_url: '',
                lectures: [],
                stases: {},
                info_cards: {},
            },
            user: new form({
                id: '',
                name: '',
                email: '',
                password: '',
                password_confirmation: '',
                image: '',
                phone: '',
                address: '',
            })
        }
    },
    methods: {
        loadData() {
            axios.get('/cmsd/get-open-stase-task')
                .then(({data}) => {
                    this.dataContent = data;
                })
        },
        loadDataAll() {
            axios.get('/cmsd/get-open-stase-task-all')
                .then(({data}) => {
                    this.dataContentAll = data;
                })
        },
        loadSchedule() {
            axios.get('/cmsd/get-schedule')
                .then((response) => {
                    this.schedules = response.data
                })
        },
        loadUser() {
            axios.get('/cmsd/user')
                .then(({data}) => {
                    this.user.fill(data.lecture);
                    this.dataRaw.info_cards = data;
                })
        },
        detailModal(data) {
            this.detailFile = data;
            $('#detailModal').modal({backdrop: 'static', keyboard: false})
        },
        profileModal() {
            $('#profileModal').modal('show')
        },
        updateProfile() {
            this.disable = true;
            this.user.post('/cmsd/update_profile')
                .then(({data}) => {
                    this.disable = false;
                    $('#profileModal').modal('hide');
                    this.loadUser()
                }).catch(() => {
                this.disable = false;
            })
        },
        getImage(e) {
            let file = e.target.files[0];

            let reader = new FileReader();
            if (file['size'] < 4200000) {
                reader.onloadend = (file) => {
                    this.user.image = reader.result;
                };
                this.dataRaw.image_url = URL.createObjectURL(file);
                reader.readAsDataURL(file)
            } else {
                this.user.image = '';
                $('#form-image').val('');
                Swal.fire({
                    icon: 'error',
                    title: 'File terlalu besar',
                    text: 'Upload file kurang dari 2 MB',
                })
            }
        },
    },
    created() {
        this.$Progress.start();
        this.loadData();
        this.loadDataAll();
        this.loadSchedule();
        this.loadUser();

        Fire.$on('ModalSuccess', () => {
            this.loadData();
            this.loadSchedule();
            this.popGlobalSuccess({});
            $('#openModal').modal('hide');
            $('#scheduleModal').modal('hide');
            this.$Progress.finish();
        })
    },
    mounted() {
        this.$Progress.finish()
    },
    components: {
        Datepicker,
    },
    computed:{
        score_pending(){
            if(this.dataContent.length > 0){
                let pending = this.dataContent.filter((data)=>{
                    return data.score == null;
                });

                return pending.length ?? 0;
            }
            return 0;
        }
    }
}
</script>
<style>
.btn-wd {
    width: 100px;
}

.btn-rounded {
    border-radius: 20px;
}

.photo-profile {
    box-shadow: 1px 1px 3px #bbbbbb;
    border-radius: 10px;
    height: 100px;
    width: 100px;
    background-position: center;
    background-size: cover;
}
</style>
