<template>
    <div class="content-wrapper dashboard2">
        <div class="content py-3">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-4 order-lg-1">
                        <div class="card profile-card shadow-sm mb-3">
                            <div class="profile-cover"></div>
                            <div class="card-body pt-0">
                                <div class="d-flex align-items-center">
                                    <div class="profile-photo"
                                         v-if="user.image"
                                         :style="'background-image: url(/storage/' + user.image + ')'">
                                    </div>
                                    <div class="profile-photo"
                                         v-else
                                         :style="'background-image: url(/assets/images/dr_default.jpeg)'">
                                    </div>
                                    <div class="ml-3 flex-grow-1">
                                        <h5 class="mb-0" v-if="dataDetail.name">{{ dataDetail.name }}</h5>
                                        <small class="text-muted" v-if="dataDetail.email">{{ dataDetail.email }}</small>
                                    </div>
                                    <button class="btn btn-outline-primary btn-sm ml-auto"
                                            @click="passwordModal">
                                        Edit Profil
                                    </button>
                                </div>

                                <div class="row text-center mt-3">
                                    <div class="col-4">
                                        <div class="stat-card">
                                            <div class="stat-value text-primary">
                                                {{ dataRaw.info_cards.avg_point }}
                                            </div>
                                            <div class="stat-label">Nilai Rata-rata</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-card">
                                            <div class="stat-value text-info">
                                                {{ dataRaw.info_cards.stases }}
                                            </div>
                                            <div class="stat-label">Stase</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-card">
                                            <div class="stat-value text-success">
                                                {{ dataRaw.info_cards.presences }}
                                            </div>
                                            <div class="stat-label">Presensi</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3 text-center">
                                    <div class="col-4">
                                        <button class="btn btn-light w-100 action-tile" @click="switchStaseModal">
                                            <i class="fa fa-sync text-secondary"></i>
                                            <div class="action-text">Ganti Stase</div>
                                        </button>
                                    </div>
                                    <div class="col-4">
                                        <button class="btn btn-light w-100 action-tile" @click="addStaseModal">
                                            <i class="fa fa-plus text-success"></i>
                                            <div class="action-text">Tambah Stase</div>
                                        </button>
                                    </div>
                                    <div class="col-4">
                                        <router-link to="/resident/logbooks" class="btn btn-light w-100 action-tile">
                                            <i class="fa fa-book-medical text-warning"></i>
                                            <div class="action-text">Log Book</div>
                                        </router-link>
                                    </div>
                                </div>

                                <div class="card mt-3 mb-0 presence-card border-0">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-start">
                                            <div class="presence-icon">
                                                <i class="fa fa-calendar-check"></i>
                                            </div>
                                            <div class="ml-3 flex-grow-1">
                                                <div class="mb-1 font-weight-bold">Presensi Hari Ini</div>
                                                <small class="text-muted" v-if="dataDetail.today_presence">
                                                    Masuk pada {{ dataDetail.today_presence.checkin | formatTime }}
                                                    <span v-if="dataDetail.today_presence.checkout">
                                                        - {{ dataDetail.today_presence.checkout | formatTime }}
                                                    </span>
                                                </small>
                                                <small class="text-muted" v-else>Belum ada presensi hari ini.</small>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <a target="_blank" href="/daily"
                                               v-if="dataDetail.today_presence && dataDetail.today_presence.checkout == null"
                                               class="btn btn-warning btn-block">
                                                Pulang
                                            </a>
                                            <a target="_blank" href="/daily"
                                               v-if="!dataDetail.today_presence"
                                               class="btn btn-primary btn-block">
                                                Presensi Masuk
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm mb-3">
                            <div class="card-body py-2">
                                <a target="_blank"
                                   href="https://drive.google.com/drive/folders/1LoCN_taJgB37eHhdc9HY1IplL8N5jjBV?usp=sharing"
                                   class="d-flex align-items-center text-decoration-none">
                                    <div class="quick-link-icon text-info">
                                        <i class="fa fa-folder-open"></i>
                                    </div>
                                    <div class="ml-3">
                                        <div class="font-weight-bold">Kurikulum & SOP</div>
                                        <small class="text-muted">Akses dokumen resmi</small>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <div class="card shadow-sm mb-3">
                            <div class="card-header bg-white border-0 pb-0">
                                <h6 class="mb-0">Akses Cepat</h6>
                            </div>
                            <div class="list-group list-group-flush">
                                <router-link to="/resident/score" class="list-group-item list-group-item-action">
                                    <i class="fa fa-clipboard-list text-primary mr-2"></i> Riwayat Nilai
                                </router-link>
                                <router-link to="/resident/resume-presences"
                                             class="list-group-item list-group-item-action">
                                    <i class="fa fa-file-archive text-info mr-2"></i> Riwayat Presensi
                                </router-link>
                            </div>
                        </div>

                        <div class="card shadow-sm d-none d-lg-block">
                            <div class="card-header bg-white border-0 pb-0">
                                <h6 class="mb-0">Stase Terambil</h6>
                            </div>
                            <div class="list-group list-group-flush">
                                <div class="list-group-item" v-for="data in dataDetail.stase_logs" :key="data.id">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge badge-success mr-2"
                                                  v-if="data.status === 'ongoing'">{{ data.status }}</span>
                                            <span class="badge badge-secondary mr-2"
                                                  v-else>{{ data.status }}</span>
                                            <b v-if="data.stase">{{ data.stase.name }}</b>
                                        </div>
                                        <small class="text-muted">
                                            {{ data.start_date | formatDate }} - {{ data.end_date | formatDate }}
                                        </small>
                                    </div>
                                    <div class="mt-1" v-if="data.stase && data.status === 'finish'">
                                        <a target="_blank" :href="'/cmsr/take-evaluation-stase/' + data.id"
                                           v-if="data.stase.evaluation_link">
                                            <i class="fa fa-check-circle" v-if="data.evaluated_at"></i>
                                            Form Evaluasi
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8 order-lg-2">
                        <DashboardAgenda></DashboardAgenda>

                        <div class="card shadow-sm mt-3" v-if="dataDetail.stase_logs_active">
                            <div class="card-header bg-white border-0 pb-0">
                                <h6 class="mb-0">
                                    <span v-if="dataDetail.stase_logs_active && dataDetail.stase_logs_active.stase">
                                        {{ dataDetail.stase_logs_active.stase.name }}
                                    </span>
                                </h6>
                                <small class="text-muted">Penugasan dan penilaian aktif</small>
                            </div>
                            <div class="list-group list-group-flush"
                                 v-if="dataDetail.stase_logs_active && dataDetail.stase_logs_active.stase">
                                <div class="list-group-item"
                                     v-if="data.status == 1 || (data.stase_task_logs[0].point_average > 0)"
                                     v-for="data in dataDetail.stase_logs_active.stase.stase_tasks"
                                     :key="data.id">
                                    <div class="d-flex flex-column flex-md-row justify-content-between">
                                        <div class="flex-grow-1">
                                            <div v-if="data.open_stase_task" class="text-muted text-sm mb-1">
                                                Rencana: {{ data.open_stase_task.plan | formatDate }}
                                            </div>
                                            <div class="d-flex align-items-center mb-1">
                                                <span v-if="data.open_stase_task"
                                                      class="badge badge-success mr-2">open</span>
                                                <span v-else class="badge badge-secondary mr-2">close</span>
                                                <b class="mb-0">{{ data.name }}</b>
                                            </div>

                                            <div v-if="!data.open_stase_task && data.stase_task_logs">
                                                <small v-for="record in data.stase_task_logs"
                                                       v-if="record.lecture && record.point_average > 0">
                                                    <i class="fa fa-marker"></i> {{ record.lecture.name }}
                                                    <span class="badge badge-success badge-pill ml-1">
                                                        {{ record.point_average }}
                                                    </span>
                                                    <br>
                                                </small>
                                            </div>

                                            <div v-if="data.open_stase_task" class="text-muted">
                                                <i>{{ data.open_stase_task.title }}</i>
                                            </div>

                                            <div v-if="data.open_stase_tasks">
                                                <small v-for="open in data.open_stase_tasks" :key="open.id"
                                                       v-if="open.lecture">
                                                    <i class="fa fa-marker"></i> {{ open.lecture.name }}
                                                    <span class="badge badge-success badge-pill ml-1"
                                                          v-if="open.score > 0">{{ open.score }}</span>
                                                    <br>
                                                </small>
                                            </div>

                                            <div v-if="data.open_stase_task && data.open_stase_task.files &&
                                                data.open_stase_task.files[0]">
                                                <div class="font-weight-bold mt-2">Penugasan</div>
                                                <ul class="pl-3 mb-2">
                                                    <li v-for="(file, f) in data.open_stase_task.files" :key="f">
                                                        <small class="text-primary pointer"
                                                               @click="detailModal(file)">{{ file.title }}</small>
                                                        <small class="text-danger ml-2 pointer"
                                                               @click="deleteFile(file.id)">hapus</small>
                                                    </li>
                                                </ul>
                                            </div>

                                            <button @click="uploadModal(data.open_stase_task)"
                                                    v-if="data.open_stase_task"
                                                    class="btn btn-sm btn-outline-info mt-2">
                                                <i class="fa fa-upload"></i> Upload File Tugas
                                            </button>
                                        </div>
                                        <div class="text-md-right mt-3 mt-md-0">
                                            <button @click="openModal(data)" v-if="!data.open_stase_task"
                                                    class="btn btn-sm btn-success">
                                                Buka
                                            </button>
                                            <button @click="closeExam(data)" v-if="data.open_stase_task"
                                                    class="btn btn-sm btn-danger">
                                                Tutup
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- OPEN MODAL -->
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
                                    <textarea class="form-control" v-model="form.title"
                                              :class="{ 'is-invalid': form.errors.has('title') }">
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
                            <div class="col-md-6">
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
        <!-- Upload File -->
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
        <!-- View uploader file -->
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
        <!-- Edit Profile -->
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
                                        <option v-for="lecture in dataRaw.lectures" :value="lecture.id">{{
                                                lecture.name
                                            }}
                                        </option>
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
        <!-- Ganti Stase -->
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
import DashboardAgenda from "../dashboard/DashboardAgenda";

export default {
    components: {
        DashboardAgenda
    },
    data() {
        return {
            base_url: '/cmsr/stases',
            editMode: false,
            editStaseMode: false,
            disable: false,
            dataDetail: {},
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
        this.loadInfoCards();

        Fire.$on('ModalSuccess', () => {
            this.loadData();
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

<style scoped>
.dashboard2 .card {
    border-radius: 14px;
}

.profile-card {
    border: 0;
    overflow: hidden;
}

.profile-cover {
    height: 90px;
    background: linear-gradient(135deg, #0f8dbf 0%, #2cb5a9 100%);
}

.profile-photo {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background-size: cover;
    background-position: center;
    border: 3px solid #fff;
    margin-top: -36px;
}

.stat-card {
    border-radius: 10px;
    padding: 8px 6px;
    background: #f8fafc;
}

.stat-value {
    font-weight: 700;
    font-size: 1.1rem;
}

.stat-label {
    font-size: 0.7rem;
    color: #6c757d;
}

.action-tile {
    border-radius: 10px;
    padding: 10px 6px;
}

.action-tile i {
    font-size: 20px;
}

.action-text {
    font-size: 0.7rem;
    margin-top: 4px;
}

.presence-card {
    background: #eef6f8;
}

.presence-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: #d6eef3;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #0f8dbf;
}

.quick-link-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: #e8f4fd;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.pointer:hover {
    cursor: pointer;
}

@media (max-width: 575.98px) {
    .profile-photo {
        width: 64px;
        height: 64px;
    }

    .stat-label {
        font-size: 0.65rem;
    }
}
</style>
