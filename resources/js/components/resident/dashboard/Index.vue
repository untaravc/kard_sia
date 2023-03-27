<template>
    <div class="content-wrapper">
        <div class="content mt-2">
            <div class="row">
                <div class="col-md-4">
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
                            <h6 class="text-center mt-2" v-if="dataDetail.name">{{ dataDetail.name }}</h6>
                            <p class="text-muted text-center" v-if="dataDetail.email">{{ dataDetail.email }}</p>
                            <div class="row">
                                <div class="col text-center py-3" style="border-right: 1px solid #d6d6d6">
                                    <b class="text-primary"
                                       style="font-size: larger">{{ dataRaw.info_cards.avg_point }}</b> <br>
                                    <small>Nilai rata-rata</small>
                                </div>
                                <div class="col text-center py-3">
                                    <b class="text-primary"
                                       style="font-size: larger">{{ dataRaw.info_cards.stases }}</b> <br>
                                    <small>Stase</small>
                                </div>
                                <div class="col text-center py-3" style="border-left: 1px solid #d6d6d6">
                                    <b class="text-primary"
                                       style="font-size: larger">{{ dataRaw.info_cards.presences }}</b> <br>
                                    <small>Presensi Agenda</small>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col text-center pointer py-3" style="border-right: 1px solid #d6d6d6">
                                    <div @click="passwordModal">
                                        <i class="fa fa-user-edit text-primary" style="font-size: 30px"></i>
                                        <br>
                                        <small>Edit Profil</small>
                                    </div>
                                </div>
                                <div class="col text-center pointer py-3">
                                    <div @click="switchStaseModal">
                                        <i class="fa fa-sync text-secondary" style="font-size: 30px"></i>
                                        <br>
                                        <small>Ganti Stase Aktif</small>
                                    </div>
                                </div>
                                <div class="col text-center pointer py-3" style="border-left: 1px solid #d6d6d6">
                                    <div @click="addStaseModal">
                                        <i class="fa fa-plus text-success" style="font-size: 30px"></i>
                                        <br>
                                        <small>Tambah Stase Baru</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col text-center py-3" style="border-right: 1px solid #d6d6d6">
                                    <router-link to="/resident/score" class="pointer">
                                        <i class="fa fa-clipboard-list text-purple" style="font-size: 30px"></i>
                                        <br>
                                        <small>Riwayat Nilai</small>
                                    </router-link>
                                </div>
                                <div class="col text-center py-3">
                                    <router-link to="/resident/resume-presences" class="pointer">
                                        <i class="fa fa-file-archive text-info" style="font-size: 30px"></i>
                                        <br>
                                        <small>Riwayat Presensi</small>
                                    </router-link>
                                </div>
                                <div class="col text-center py-3" style="border-left: 1px solid #d6d6d6">
                                    <router-link to="/resident/logbooks" class="pointer">
                                        <i class="fa fa-book-medical text-warning" style="font-size: 30px"></i>
                                        <br>
                                        <small>Log Book</small>
                                    </router-link>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12 mt-4 text-center">
                                    <small class="mb-4" v-if="dataDetail.today_presence">
                                        <i class="fa fa-check-circle text-success"></i>
                                        Masuk pada {{ dataDetail.today_presence.checkin | formatTime }}
                                        <span v-if="dataDetail.today_presence.checkout">- {{
                                                dataDetail.today_presence.checkout | formatTime
                                            }}</span>
                                    </small>
                                    <a target="_blank" href="/daily"
                                       v-if="dataDetail.today_presence && dataDetail.today_presence.checkout == null"
                                       style="width: 100%" class="btn btn-warning">Pulang</a>
                                    <a target="_blank" href="/daily" style="width: 100%"
                                       v-if="!dataDetail.today_presence"
                                       class="btn btn-th-dark">Presensi Masuk</a>
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

                    <div class="card d-none d-md-block card-outline card-primary">
                        <div class="card-body table-responsive p-0">
                            <table class="table table-head-fixed">
                                <tr>
                                    <th>
                                        Stase Terambil
                                    </th>
                                </tr>
                                <tbody>
                                <tr role="row" class="odd" v-for="(data, i) in dataDetail.stase_logs" :key="data.id">
                                    <td>
                                        <span class="badge badge-success"
                                              v-if="data.status === 'ongoing'">{{ data.status }}</span>
                                        <span class="badge badge-secondary"
                                              v-if="data.status !== 'ongoing'">{{ data.status }}</span>
                                        <b v-if="data.stase">{{ data.stase.name }}</b>
                                        <div class="d-flex justify-content-between">
                                            <small>
                                                {{ data.start_date | formatDate }} -
                                                {{ data.end_date | formatDate }}
                                            </small>
                                            <small v-if="data.stase && data.status === 'finish'">
                                                <a target="_blank" :href="'/cmsr/take-evaluation-stase/' + data.id" v-if="data.stase.evaluation_link">
                                                    <i class="fa fa-check-circle" v-if="data.evaluated_at"></i>
                                                    Form Evaluasi
                                                </a>
                                            </small>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
                <div class="col-md-8">
                    <!--                    Agenda-->
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
                    <!--                    Ujian Online-->
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Ujian Online</h3>
                        </div>
                        <div class="card-body p-0">
                            <table v-if="exams && exams[0]" class="table">
                                <tr v-for="(ex, e) in exams" :key="e">
                                    <td>
                                        <span v-if="ex.exam">{{ ex.exam.name }}</span>
                                        <span v-if="ex.exam" class="badge badge-info">{{ ex.exam.duration }} min</span>
                                    </td>
                                    <td class="text-right">
                                        <a v-if="ex.exam" :href="ex.exam.direct_link"
                                           class="btn btn-wd btn-sm btn-success">Mulai</a>
                                    </td>
                                </tr>
                            </table>

                            <div v-if="exams && !exams[0]" class="p-3">
                                <i>Tidak ada jadwal</i>
                            </div>
                        </div>
                    </div>
                    <!--                    Penilaian-->
                    <div class="card card-outline card-primary" v-if="dataDetail.stase_logs_active ">
                        <div class="card-body table-responsive p-0">
                            <table class="table table-head-fixed">
                                <tr>
                                    <th colspan="2">
                                        <span>{{ dataDetail.stase_logs_active.stase.name }}</span>
                                    </th>
                                </tr>
                                <tbody>
                                <tr role="row" class="odd"
                                    v-for="(data, i) in dataDetail.stase_logs_active.stase.stase_tasks" :key="data.id">
                                    <td>
                                        <div v-if="data.open_stase_task" style="font-style: italic">Rencana: {{data.open_stase_task.plan | formatDate}}</div>
                                        <span v-if="data.open_stase_task" class="badge badge-success">open</span>
                                        <span v-if="!data.open_stase_task" class="badge badge-secondary">close</span>
                                        <b class="mb-0">{{ data.name }}</b>
                                        <div v-if="!data.open_stase_task && data.stase_task_logs">
                                            <small v-for="record in data.stase_task_logs"
                                                   v-if="record.lecture && record.point_average > 0">
                                                <i class="fa fa-marker"></i> {{ record.lecture.name }}
                                                <span class="badge-success badge" style="font-size: small">
                                                    {{ record.point_average }}
                                                </span>
                                                <br>
                                            </small>
                                        </div>
                                        <i v-if="data.open_stase_task">{{ data.open_stase_task.title }}</i>
                                        <div v-if="data.open_stase_tasks">
                                            <small v-for="open in data.open_stase_tasks" :key="open.id"
                                                   v-if="open.lecture">
                                                <i class="fa fa-marker"></i> {{ open.lecture.name }}
                                                <span class="badge-success badge" style="font-size: small"
                                                      v-if="open.score > 0"> {{ open.score }}</span>
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
                                            Buka
                                        </button>
                                        <button @click="closeExam(data)" v-if="data.open_stase_task"
                                                class="btn btn-wd btn-sm btn-danger">
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
                                    <textarea class="form-control" v-model="form.title" :class="{ 'is-invalid': form.errors.has('title') }">
                                        {{ form.title }}
                                    </textarea>
                                    <has-error :form="form" field="title"></has-error>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Rencana Tanggal Ujian</label>
                                    <input class="form-control" type="date" v-model="form.plan"
                                           :class="{ 'is-invalid': form.errors.has('plan') }">
                                    <has-error :form="form" field="plan"></has-error>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group" :class="{ 'is-invalid': form.errors.has('lecture_ids') }">
                                    <label>Dosen Penguji</label>
                                    <div class="form-check" v-for="(data, i) in dataRaw.lectures" :key="i"
                                         :class="{ 'is-invalid': form.errors.has('lecture_id') }">
                                        <input class="form-check-input" :id="'dosen'+data.id" type="checkbox"
                                               v-model="form.lecture_ids[data.id]">
                                        <label class="form-check-label" :for="'dosen'+data.id">{{ data.name }}</label>
                                    </div>
                                </div>
                                <has-error :form="form" field="lecture_ids"></has-error>
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
                                <input type="text" class="form-control"
                                       :class="{ 'is-invalid': uploadData.errors.has('title') }"
                                       v-model="uploadData.title">
                                <has-error :form="uploadData" field="title"></has-error>
                            </div>
                            <div class="col-md-12">
                                <label>Keterangan</label>
                                <textarea class="form-control"
                                          :class="{ 'is-invalid': uploadData.errors.has('desc') }"
                                          v-model="uploadData.desc"></textarea>
                                <has-error :form="uploadData" field="desc"></has-error>
                            </div>
                            <div class="col-md-12">
                                <label>File (format pdf/gambar)</label>
                                <br>
                                <input type="file" @change="getFile"
                                       :class="{ 'is-invalid': uploadData.errors.has('file') }">
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
        <!--    View uploader file-->
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
                                   :href="`/storage/`+detailFile.link" download>
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
        <!--    Edit Profile-->
        <div class="modal fade bd-example-modal-lg" id="passwordModal" tabindex="-1" role="dialog"
             aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Ubah Profil</h5>
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
                                <img :src="'/storage/' + user.image" v-if="!dataRaw.image_url" alt="" class="m-2"
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
                                    <small><i>Kosongkan bila tidak akan mengubah password</i></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Konfirmasi Password</label>
                                    <input v-model="user.password_confirmation" type="password"
                                           class="form-control">
                                </div>
                            </div>
                            <div class="col-12">
                                <hr>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Inisial</label>
                                    <input v-model="user.initial" type="text"
                                           class="form-control"
                                           :class="{ 'is-invalid': user.errors.has('initial') }">
                                    <has-error :form="user" field="initial"></has-error>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>No Mahasiswa</label>
                                    <input v-model="user.code" type="text"
                                           class="form-control"
                                           :class="{ 'is-invalid': user.errors.has('code') }">
                                    <has-error :form="user" field="code"></has-error>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kota Asal</label>
                                    <input v-model="user.city" type="text"
                                           class="form-control"
                                           :class="{ 'is-invalid': user.errors.has('city') }">
                                    <has-error :form="user" field="city"></has-error>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kode Pos</label>
                                    <input v-model="user.postal_code" type="number"
                                           class="form-control"
                                           :class="{ 'is-invalid': user.errors.has('postal_code') }">
                                    <has-error :form="user" field="postal_code"></has-error>
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

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>No Telepon</label>
                                    <input v-model="user.phone" type="text"
                                           class="form-control"
                                           :class="{ 'is-invalid': user.errors.has('phone') }">
                                    <has-error :form="user" field="phone"></has-error>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tempat Lahir</label>
                                    <input v-model="user.pob" type="text"
                                           class="form-control"
                                           :class="{ 'is-invalid': user.errors.has('pob') }">
                                    <has-error :form="user" field="pob"></has-error>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Lahir</label>
                                    <input v-model="user.dob" type="date"
                                           class="form-control"
                                           :class="{ 'is-invalid': user.errors.has('dob') }">
                                    <has-error :form="user" field="dob"></has-error>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Pendidikan S1</label>
                                    <input v-model="user.undergraduate" type="text"
                                           class="form-control"
                                           :class="{ 'is-invalid': user.errors.has('undergraduate') }">
                                    <has-error :form="user" field="undergraduate"></has-error>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Lulus S1</label>
                                    <input v-model="user.graduated_at" type="date"
                                           class="form-control"
                                           :class="{ 'is-invalid': user.errors.has('graduated_at') }">
                                    <has-error :form="user" field="graduated_at"></has-error>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select v-model="user.degree"
                                           class="form-control"
                                           :class="{ 'is-invalid': user.errors.has('degree') }">
                                        <option value="Pegawai Negri Sipil">Pegawai Negri Sipil</option>
                                        <option value="Pegawai Swasta">Pegawai Swasta</option>
                                        <option value="TNI/Polri">TNI/Polri</option>
                                        <option value="Tugas Belajar">Tugas Belajar</option>
                                        <option value="Perorangan">Perorangan</option>
                                        <option value="Lain-lain">Lain-lain</option>
                                    </select>
                                    <has-error :form="user" field="degree"></has-error>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Pembimbing</label>
                                    <select v-model="user.lecture_id"
                                            class="form-control"
                                            :class="{ 'is-invalid': user.errors.has('lecture_id') }">
                                        <option v-for="lecture in dataRaw.lectures" :value="lecture.id">{{ lecture.name }}</option>
                                    </select>
                                    <has-error :form="user" field="lecture_id"></has-error>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <a :href="'/cmsr/identity-pdf'" target="_blank" class="btn btn-info">Cetak Identitas</a>
                        <button class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button :disabled="disable" class="btn btn-primary" @click="updatePassword">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status"></span>
                            Ubah
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!--    Ganti Stase-->
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
                                    <select class="form-control" v-model="stase.stase_id">
                                        <option v-for="stase in dataDetail.stase_logs" :value="stase.id"><span
                                            v-if="stase.stase">{{ stase.stase.name }}</span></option>
                                    </select>
                                </div>
                                <div class="form-group" v-if="!editStaseMode">
                                    <label>Pilih Stase</label>
                                    <select class="form-control" v-model="stase.stase_id">
                                        <option v-for="stase in dataRaw.stase_option" :value="stase.id">{{
                                                stase.name
                                            }}
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group" v-if="stase.stase_id">
                                    <label>Tanggal Mulai</label>
                                    <input class="form-control" type="date" v-model="stase.start_date">
                                </div>
                                <div class="form-group" v-if="stase.stase_id">
                                    <label>Tanggal Selesai</label>
                                    <input class="form-control" type="date" v-model="stase.end_date">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" v-if="editStaseMode" :disabled="disable" class="btn btn-primary"
                                @click="switchStase">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status"></span>
                            Pilih Stase Aktif
                        </button>
                        <button type="submit" v-if="!editStaseMode" :disabled="disable" class="btn btn-success"
                                @click="addStase">
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
export default {
    data() {
        return {
            base_url: '/cmsr/stases',
            editMode: false,
            editStaseMode: false,
            disable: false,
            dataDetail: {},
            schedules: {},
            exams: {},
            scheduleDetail: {},
            stase: {
                stase_id: null,
                start_date: '',
                end_date: '',
            },
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
                plan: '',
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
            user: new form({
                id: '',
                image: '',
                email: '',
                name: '',
                password: '',
                password_confirmation: '',
                initial: '',
                code: '',
                city: '',
                postal_code: '',
                address: '',
                phone: '',
                pob: '',
                dob: '',
                undergraduate: '',
                graduated_at: '',
                degree: '',
                lecture_id: '',
            }),
        }
    },
    methods: {
        loadData() {
            axios.get('/cmsr/residents')
                .then(({data}) => {
                    this.dataDetail = data;
                    this.user.fill(data);
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
            axios.get('/cmsr/get-stases/' + this.user.id)
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
        loadExam() {
            axios.get('/cmsr/exams')
                .then(({data}) => {
                    if (data.status) {
                        this.exams = data.data
                    }
                })
        },
        loadStaseLog(stase_id) {
            axios.get('/cmsr/get-stase-log', {params: {stase_id: stase_id}})
                .then(({data}) => {
                    this.stase.start_date = data.data.start_date;
                    this.stase.end_date = data.data.end_date;
                })
        },

        // Form Ujian
        openModal(data) {
            this.form.reset();
            $('#openModal').modal({backdrop: 'static', keyboard: false});
            this.form.stase_task_id = data.id;
        },
        createData() {
            this.disable = true;
            this.form.post('/cmsr/open-stase-task')
                .then((response) => {
                    Fire.$emit('ModalSuccess');
                    this.disable = false;
                }).catch((error) => {
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
        uploadFile() {
            this.disable = true;
            this.uploadData.post('/cmsr/upload-file')
                .then((response) => {
                    Fire.$emit('ModalSuccess');
                    this.disable = false;
                }).catch(() => {
                this.disable = false;
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
            this.user.post('/cmsr/update_profile')
                .then((response) => {
                    this.disable = false;
                    $('#passwordModal').modal('hide')
                    Fire.$emit('ModalSuccess');
                    this.loadData()
                }).catch((error) => {
                this.disable = false;
            });
        },

        switchStaseModal() {
            this.editStaseMode = true;
            $('#switchStase').modal('show')
        },
        switchStase() {
            this.disable = true;
            axios.get('/cmsr/select-active-stase', {params: this.stase})
                .then((response) => {
                    this.disable = false;
                    this.loadData()
                    $('#switchStase').modal('hide')
                    this.popGlobalSuccess({})
                }).catch((error) => {
                this.disable = false;
            });
        },
        addStaseModal() {
            this.editStaseMode = false;
            this.loadStase();
            $('#switchStase').modal('show')
        },
        addStase() {
            this.disable = true;
            axios.get('/cmsr/add-stase', {params: this.stase})
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
        this.loadExam();

        Fire.$on('ModalSuccess', () => {
            this.loadData();
            this.loadSchedule();
            this.popGlobalSuccess({});
            $('#openModal').modal('hide');
            $('#uploadModal').modal('hide');
            $('#scheduleModal').modal('hide');
        })
    },
    watch: {
        'stase.stase_id': function (val) {
            if (this.editStaseMode) {
                if (val) {
                    this.loadStaseLog(val)
                }
            }
        }
    }
}
</script>

<style>
.badge-cs {
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

.pointer:hover {
    cursor: pointer;
}
</style>
