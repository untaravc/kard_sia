<template>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-6">
                        <h1 class="m-0 text-dark">
                            <!--                            <i class="fa fa-tachometer-alt"></i> -->
                            Dashboard
                        </h1>
                    </div>
                    <div class="col-6 text-right">
                        <button class="btn btn-th-dark btn-sm" @click="passwordModal">
                            <i class="fa fa-user-edit"></i> Edit Profile
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center"></div>
                            <h6 class="text-center">{{ dataDetail.name }}</h6>
                            <p class="text-muted text-center">{{ dataDetail.email }}</p>
                            <div class="row">
                                <div class="col text-center" style="border-right: 1px solid #d6d6d6">
                                    <b class="text-primary" style="font-size: larger">{{dataRaw.info_cards.avg_point}}</b> <br>
                                    <small>Nilai rata-rata</small>
                                </div>
                                <div class="col text-center" style="border-left: 1px solid #d6d6d6">
                                    <b class="text-primary" style="font-size: larger">{{dataRaw.info_cards.stases}}</b> <br>
                                    <small>Stase</small>
                                </div>
                                <div class="col text-center">
                                    <b class="text-primary" style="font-size: larger">{{dataRaw.info_cards.presences}}</b> <br>
                                    <small>Presensi Agenda</small>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col text-center">
                                    <span class="badge-primary badge-cs" @click="switchStaseModal"> <i class="fa fa-sync"></i> Ganti Stase Aktif</span>
                                    <span class="badge-success badge-cs" @click="addStaseModal"> <i class="fa fa-plus"></i> Tambah Stase</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card d-none d-md-block card-outline card-primary">
                        <div class="card-body table-responsive p-0">
                            <table class="table table-head-fixed">
                                <tr>
                                    <th>Stase</th>
                                </tr>
                                <tbody>
                                <tr role="row" class="odd" v-for="(data, i) in dataDetail.stase_logs" :key="data.id">
                                    <td>
                                        <span class="badge float-right"
                                              :class="data.status == 'finish' ? 'badge-secondary' : 'badge-success'">{{
                                                data.status
                                            }}</span>
                                        {{ data.stase.name }} <br>
                                        <small>
                                            {{ data.start_date | formatDate }} -
                                            <span
                                                v-if="data.status === 'finish'">{{
                                                    data.end_date | formatDate
                                                }}</span><span
                                            v-else>selesai</span>
                                            <i v-if="data.status === 'finish' && data.duration">({{ data.duration }})</i>
                                        </small>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
                <div class="col-md-8">

                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Agenda Hari Ini</h3>
                            <div class="card-tools">
                                <router-link to="/resident/activity" class="btn btn-sm btn-th-dark">
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
                                                Anda telah presensi pada
                                                {{ sch.absence.created_at | formatDateTime }}
                                            </i>
                                        </small>
                                    </td>
                                    <td class="text-right">
                                        <button v-if="!sch.absence" class="btn btn-wd btn-sm btn-success"
                                                @click="presensiModal(sch)">Presensi
                                        </button>
                                    </td>
                                </tr>
                            </table>

                            <div v-if="schedules && !schedules[0]" class="p-3">
                                <i>Tidak ada agenda</i>
                            </div>
                        </div>
                    </div>

                    <div class="card card-outline card-primary" v-if="dataDetail.stase_logs_active ">
                        <div class="card-body table-responsive p-0">
                            <table class="table table-head-fixed">
                                <tr>
                                    <th colspan="2">
                                        <span>{{ dataDetail.stase_logs_active.stase.name }}</span>
                                        <br>
                                    </th>
                                </tr>
                                <tbody>
                                <tr role="row" class="odd"
                                    v-for="(data, i) in dataDetail.stase_logs_active.stase.stase_tasks" :key="data.id">
                                    <td>
                                        <span v-if="data.open_stase_task" class="badge badge-success">open</span>
                                        <span v-if="!data.open_stase_task" class="badge badge-secondary">close</span>
                                        <br>
                                        <b class="mb-0">{{ data.name }}</b> <br>
                                        <i v-if="data.open_stase_task">{{ data.open_stase_task.title }}</i>
                                        <div v-if="data.open_stase_tasks">
                                            <small v-for="open in data.open_stase_tasks" :key="open.id"
                                                   v-if="open.lecture">
                                                <i class="fa fa-marker"></i> {{ open.lecture.name }}
                                                <span class="badge-success badge" style="font-size: small" v-if="open.score > 0"> {{open.score}}</span>
                                                <br>
                                            </small>
                                        </div>
                                        <!--                                        <span v-if="data.open_stase_task && data.open_stase_task.lecture_id == '0'"><i>banyak dosen</i></span><br>-->
                                        <div
                                            v-if="data.open_stase_task && data.open_stase_task.files && data.open_stase_task.files[0]">
                                            <b>Penugasan</b>
                                            <ol class="pl-3">
                                                <li v-for="(file, f) in data.open_stase_task.files" :key="f">
                                                    <small style="text-decoration: underline; cursor:pointer"
                                                           @click="detailModal(file)">{{ file.title }}</small>
                                                    <small
                                                        style="text-decoration: underline; cursor: pointer; color: red"
                                                        @click="deleteFile(file.id)">hapus</small>
                                                </li>
                                            </ol>
                                        </div>
                                        <button @click="uploadModal(data.open_stase_task)" v-if="data.open_stase_task"
                                                class="btn btn-sm btn-info mt-2">
                                            <i class="fa fa-upload"></i> Upload File Tugas
                                        </button>
                                    </td>
                                    <td class="text-right" width="120px">
                                        <button @click="openModal(data)" v-if="!data.open_stase_task"
                                                class="btn btn-wd btn-sm btn-success">
                                            <!--                                            <i class="fa fa-door-open"></i> -->
                                            Buka
                                        </button>
                                        <button @click="closeExam(data)" v-if="data.open_stase_task"
                                                class="btn btn-wd btn-sm btn-danger">
                                            <!--                                            <i class="fa fa-door-closed"></i> -->
                                            Tutup
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
        <!--        OPEN MODAL-->
        <div class="modal fade bd-example-modal-lg" id="openModal" tabindex="-1" role="dialog"
             aria-labelledby="myLargeModalLabel" aria-hidden="true">
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
                                <div class="form-group">
                                    <label>Judul</label>
                                    <textarea class="form-control" v-model="form.title">{{ form.title }}</textarea>
                                    <has-error :form="form" field="lecture_id"></has-error>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Dosen Penguji</label>
                                    <div class="form-check" v-for="(data, i) in dataRaw.lectures" :key="i"
                                         :class="{ 'is-invalid': form.errors.has('lecture_id') }">
                                        <input class="form-check-input" :id="'dosen'+data.id" type="checkbox"
                                               v-model="form.lecture_ids[data.id]">
                                        <label class="form-check-label" :for="'dosen'+data.id">{{ data.name }}</label>
                                    </div>
                                </div>
                                <has-error :form="form" field="lecture_id"></has-error>
                                <!--                                <select v-model="form.lecture_id" class="form-control" >-->
                                <!--                                    <option value="" selected>Banyak Dosen</option>-->
                                <!--                                    <option v-for="(data, i) in dataRaw.lectures" :key="i" :value="data.id">{{data.name}}</option>-->
                                <!--                                </select>-->

                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button :disabled="disable" v-show="!editMode" class="btn btn-success" @click="createData">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status"
                                  aria-hidden="true"></span>
                            Buka
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!--    Upload File-->
        <div class="modal fade bd-example-modal-lg" id="uploadModal" tabindex="-1" role="dialog"
             aria-labelledby="myLargeModalLabel" aria-hidden="true">
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
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status"
                                  aria-hidden="true"></span>
                            Upload
                        </button>
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
                            <table>
                                <tbody>
                                <tr>
                                    <td>Judul</td>
                                    <td>:</td>
                                    <td>{{ detailFile.title }}</td>
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

        <div class="modal fade bd-example-modal-lg" id="scheduleModal" tabindex="-1" role="dialog"
             aria-labelledby="myLargeModalLabel" aria-hidden="true">
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
                            <div class="col-12 text-center">
                                <h4>{{scheduleDetail.name}}</h4>
                                <p class="mb-0">{{scheduleDetail.title}}</p>
                                <i v-if="scheduleDetail.speaker">{{scheduleDetail.speaker}}</i> <br>
                                <small>{{scheduleDetail.start_date | formatDateTime}}</small>
                                <small v-if="scheduleDetail.end_date"> - {{scheduleDetail.end_date | formatDateTime}}</small>
                                <small v-if="!scheduleDetail.end_date"> - selesai</small>
                            </div>
                            <div class="col-12 mt-2">
                                <div class="form-group"  v-if="scheduleDetail.has_passcode">
                                    <input type="text" class="form-control"
                                           placeholder="Passcode"
                                           style="text-align: center" v-model="scheduleDetail.passcode">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button :disabled="disable" @click="present" class="btn btn-success">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status"
                                  aria-hidden="true"></span>
                            Presensi
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="passwordModal" tabindex="-1" role="dialog"
             aria-labelledby="myLargeModalLabel" aria-hidden="true">
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
                                    <input v-model="reset_data.email" type="email"
                                           class="form-control"
                                           :class="{ 'is-invalid': reset_data.errors.has('email') }">
                                    <has-error :form="reset_data" field="email"></has-error>
                                </div>
                            </div>
                            <div class="col-md-6"></div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Password Baru</label>
                                    <input v-model="reset_data.password" type="password" name="password"
                                           class="form-control" autocomplete="new-password"
                                           :class="{ 'is-invalid': reset_data.errors.has('password') }">
                                    <has-error :form="reset_data" field="password"></has-error>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Konfirmasi Password</label>
                                    <input v-model="reset_data.password_confirmation" type="password"
                                           class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" :disabled="disable" class="btn btn-primary" @click="updatePassword">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status"></span>
                            Ubah
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="switchStase" tabindex="-1" role="dialog"
             aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" v-if="editStaseMode">Ubah Stase Aktif</h5>
                        <h5 class="modal-title" v-if="!editStaseMode">Tambah Stase</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col">
                                <div class="form-group" v-if="editStaseMode">
                                    <label>Pilih Stase</label>
                                    <select class="form-control" v-model="active_stase_id">
                                        <option v-for="stase in dataDetail.stase_logs" :value="stase.id"><span v-if="stase.stase">{{ stase.stase.name }}</span></option>
                                    </select>
                                </div>
                                <div class="form-group" v-if="!editStaseMode">
                                    <label>Pilih Stase</label>
                                    <select class="form-control" v-model="active_stase_id">
                                        <option v-for="stase in dataRaw.stase_option" :value="stase.id">{{ stase.name }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" v-if="editStaseMode" :disabled="disable" class="btn btn-primary" @click="switchStase">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status"></span>
                            Simpan Perubahan
                        </button>
                        <button type="submit" v-if="!editStaseMode" :disabled="disable" class="btn btn-success" @click="addStase">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status"></span>
                            Tambah Stase
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
            editStaseMode: false,
            disable: false,
            dataDetail: {},
            schedules: {},
            scheduleDetail: {},
            active_stase_id: null,
            dataRaw: {
                image_url: '',
                lectures: [],
                stases: {},
                stase_option: {},
                info_cards: {},
            },
            detailFile: {},
            form: new form({
                lecture_id: '',
                lecture_ids: [],
                stase_task_id: '',
                title: '',
            }),
            uploadData: new form({
                open_stase_task_id: '',
                title: '',
                desc: '',
                file: '',
            }),
            filter: new form({
                keyword: '',
                type: '',
                status: '',
                start_date: '',
                end_date: '',
                page: ''
            }),
            profile: new form({
                id: '',
                student_id: '',
                code: '',
                degree: '',
                pob: '',
                dob: '',
                phone: '',
                address: '',
            }),
            reset_data: new form({
                id: '',
                email: '',
                password: '',
                password_confirmation: '',
            }),
        }
    },
    methods: {
        loadData() {
            axios.get('/cmsr/residents')
                .then(({data}) => {
                    this.dataDetail = data;
                    this.reset_data.fill(data);
                })
        },
        loadLectures() {
            axios.get('/get-lectures')
                .then(({data}) => {
                    this.dataRaw.lectures = data;
                })
        },
        loadSchedule() {
            axios.get('/cmsr/get-schedule')
                .then((response) => {
                    this.schedules = response.data
                })
        },
        loadStase() {
            axios.get('/cmsr/get-stases/' + this.reset_data.id)
                .then(({data}) => {
                    this.dataRaw.stase_option = data
                })
        },
        loadInfoCards() {
            axios.get('/cmsr/info-cards/')
                .then(({data}) => {
                    this.dataRaw.info_cards = data
                })
        },

        // Form Ujian
        openModal(data) {
            this.form.reset();
            $('#openModal').modal({backdrop: 'static', keyboard: false});
            this.form.stase_task_id = data.id;
        },
        createData() {
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
        closeExam(data) {
            if (confirm('Tutup sesi Ujian?')) {
                axios.get('/cmsr/close-stase-task', {params: data.open_stase_task})
                    .then(({response}) => {
                        Fire.$emit('ModalSuccess');
                    })
            }
        },
        uploadModal(data) {
            this.uploadData.reset();
            $('#uploadModal').modal({backdrop: 'static', keyboard: false});
            this.uploadData.open_stase_task_id = data.id;
        },
        getFile(e) {
            let file = e.target.files[0];

            let reader = new FileReader();
            if (file['size'] < 2200000) {
                reader.onloadend = (file) => {
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
        uploadFile() {
            this.uploadData.post('/cmsr/upload-file')
                .then((response) => {
                    Fire.$emit('ModalSuccess')
                })
        },
        deleteFile(id) {
            if (confirm('Hapus File?')) {
                axios.get('/cmsr/delete-file/' + id)
                    .then(() => {
                        Fire.$emit('ModalSuccess')
                    })
                    .catch(() => {
                        alert('Gagal.')
                    })
            }
        },
        detailModal(data) {
            this.detailFile = data;
            $('#detailModal').modal({backdrop: 'static', keyboard: false})
        },

        presensiModal(data) {
            this.scheduleDetail = data;
            $('#scheduleModal').modal({backdrop: 'static', keyboard: false})
        },
        present() {
            this.disable = true;
            axios.post('/cmsr/presence', this.scheduleDetail)
                .then(() => {
                    Fire.$emit('ModalSuccess');
                    this.disable = false;
                }).catch(() => {
                this.disable = false;
            })
        },

        passwordModal() {
            $('#passwordModal').modal({backdrop: 'static', keyboard: false})
        },
        updatePassword() {
            this.disable = true;
            this.reset_data.post('/cmsr/update_password')
                .then((response) => {
                    this.disable = false;
                    $('#passwordModal').modal('hide')
                    Fire.$emit('ModalSuccess');
                }).catch((error) => {
                this.disable = false;
            });
        },

        switchStaseModal(){
            this.editStaseMode = true;
            $('#switchStase').modal('show')
        },
        switchStase(){
            this.disable = true;
            axios.get('/cmsr/select-active-stase', {params: {active_stase_id: this.active_stase_id}})
                .then((response) => {
                    this.disable = false;
                    this.loadData()
                    $('#switchStase').modal('hide')
                    this.popGlobalSuccess({})
                }).catch((error) => {
                this.disable = false;
            });
        },
        addStaseModal(){
            this.editStaseMode = false;
            this.loadStase();
            $('#switchStase').modal('show')
        },
        addStase(){
            this.disable = true;
            axios.get('/cmsr/add-stase', {params: {stase_id: this.active_stase_id}})
                .then((response) => {
                    this.disable = false;
                    this.loadData()
                    $('#switchStase').modal('hide')
                    this.popGlobalSuccess({})
                }).catch((error) => {
                this.disable = false;
            });
        }
    },
    created() {
        this.loadData();
        this.loadLectures();
        this.loadSchedule();
        this.loadInfoCards();

        Fire.$on('ModalSuccess', () => {
            this.loadData();
            this.loadSchedule();
            this.popGlobalSuccess({});
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

<style>
    .badge-cs{
        display: inline-block;
        padding: 0.5em 1em;
        font-size: 75%;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 1rem;
        cursor: pointer;
    }
</style>
