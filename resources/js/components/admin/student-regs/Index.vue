<template>
    <div class="content-wrapper">
        <div class="content">
            <div class="row">
                <div class="col-12">
                    <div class="card mt-2">
                        <div class="card-header">
                            <h1 class="card-title">Calon PPDS</h1>
                            <div class="card-tools">
                                <button class="btn btn-sm btn-th-primary" @click="addModal"><i
                                    class="fa fa-user-plus"></i> Tambah
                                </button>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <vue-loader :active.sync="is_loading" :can-cancel="false" loader="dots"
                                        :is-full-page="false"></vue-loader>
                            <table class="table table-head-fixed">
                                <tr>
                                    <th style="width: 50px">No</th>
                                    <th style="width: 10%">Status</th>
                                    <th style="width: 35%">Nama</th>
                                    <th>Periode</th>
                                    <th style="width: 20%">Lampiran</th>
                                    <th style="width: 70px"><span style="float: right">Aksi</span></th>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>
                                        <select class="form-control" @change="loadDataContent" v-model="filter.status">
                                            <option value="">Semua</option>
                                            <option value="0">Mendaftar</option>
                                            <option value="1">Diterima</option>
                                            <option value="2">Ditolak</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" v-model="filter.name" @keyup.enter="loadDataContent"
                                               class="form-control" placeholder="Cari nama..">
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tbody>
                                <tr role="row" class="odd" v-for="(data, i) in dataContent.data" :key="data.id">
                                    <td>
                                        <span
                                            :title="data.id">{{ dataContent.per_page * (dataContent.current_page - 1) + i + 1 }} </span>
                                    </td>
                                    <td>
                                        <small>{{data.status_label}}</small>
                                    </td>
                                    <td>
                                        <b>{{ data.name }}</b><br>
                                        <small>{{data.address}}</small>
                                    </td>
                                    <td>
                                        {{data.date | formatDate }}
                                    </td>
                                    <td>
                                        <Docs v-for="(doc, i) in data.documents" :key="i" :document="doc"></Docs>
                                    </td>
                                    <td class="action-button">
                                        <div class="dropdown show">
                                            <a class="btn btn-light" href="#" role="button" data-toggle="dropdown"
                                               aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-ellipsis-v"></i>
                                            </a>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                                <a class="dropdown-item" href="#" @click="editModal(data)">Detail</a>
                                                <a class="dropdown-item" href="#" @click="uploadModal(data)">Upload File</a>
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
                                        Menampilkan data {{ dataContent.from }} hingga {{ dataContent.to }} dari
                                        {{ dataContent.total }} data.
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-7">
                                    <div class="dataTables_paginate paging_simple_numbers" style="float: right">
                                        <pagination :data="dataContent" :limit="2"
                                                    @pagination-change-page="pagination"></pagination>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="editAddModal" tabindex="-1" role="dialog"
             aria-labelledby="myLargeModalLabel" aria-hidden="true">
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
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home"
                                   role="tab" aria-controls="home" aria-selected="true">Identitas</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="profile-tab" data-toggle="tab"
                                   href="#profile" role="tab" aria-controls="profile" aria-selected="false">Catatan</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="doc-tab" data-toggle="tab"
                                   href="#doc" role="tab" aria-controls="doc" aria-selected="false">Lampiran</a>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nama</label>
                                            <input v-model="form.name" type="text"
                                                   class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                                            <has-error :form="form" field="name"></has-error>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Kota Asal</label>
                                            <input v-model="form.address" type="text"
                                                   class="form-control" :class="{ 'is-invalid': form.errors.has('address') }">
                                            <has-error :form="form" field="address"></has-error>
                                        </div>
                                    </div>
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
                                            <label>No Hp</label>
                                            <input v-model="form.phone" type="text"
                                                   class="form-control" :class="{ 'is-invalid': form.errors.has('phone') }">
                                            <has-error :form="form" field="phone"></has-error>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label>Status</label>
                                        <select v-model="form.status" class="form-control"
                                                :class="{ 'is-invalid': form.errors.has('status') }" name="">
                                            <option value="0">Mendaftar</option>
                                            <option value="1">Diterima</option>
                                            <option value="2">Ditolak</option>
                                        </select>
                                        <has-error :form="form" field="status"></has-error>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Periode Pendaftaran</label>
                                            <input v-model="form.date" type="date"
                                                   class="form-control" :class="{ 'is-invalid': form.errors.has('date') }">
                                            <has-error :form="form" field="date"></has-error>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                                <div class="form-group">
                                    <label>Identitas</label>
                                    <textarea v-model="form.cv" class="form-control"
                                              :class="{ 'is-invalid': form.errors.has('cv') }"></textarea>
                                    <has-error :form="form" field="cv"></has-error>
                                </div>
                                <div class="form-group">
                                    <label>Izin Belajar</label>
                                    <textarea v-model="form.permission" class="form-control"
                                              :class="{ 'is-invalid': form.errors.has('permission') }"></textarea>
                                    <has-error :form="form" field="permission"></has-error>
                                </div>
                                <div class="form-group">
                                    <label>Ujian Tulis</label>
                                    <textarea v-model="form.written_exam" class="form-control"
                                              :class="{ 'is-invalid': form.errors.has('written_exam') }"></textarea>
                                    <has-error :form="form" field="written_exam"></has-error>
                                </div>
                                <div class="form-group">
                                    <label>Ujian Tulis</label>
                                    <textarea v-model="form.psychology" class="form-control"
                                              :class="{ 'is-invalid': form.errors.has('psychology') }"></textarea>
                                    <has-error :form="form" field="psychology"></has-error>
                                </div>
                                <div class="form-group">
                                    <label>Kesehatan</label>
                                    <textarea v-model="form.health" class="form-control"
                                              :class="{ 'is-invalid': form.errors.has('health') }"></textarea>
                                    <has-error :form="form" field="health"></has-error>
                                </div>
                                <div class="form-group">
                                    <label>Wawancara</label>
                                    <textarea v-model="form.interview" class="form-control"
                                              :class="{ 'is-invalid': form.errors.has('interview') }"></textarea>
                                    <has-error :form="form" field="interview"></has-error>
                                </div>
                                <div class="form-group">
                                    <label>Jurnal</label>
                                    <textarea v-model="form.journal" class="form-control"
                                              :class="{ 'is-invalid': form.errors.has('journal') }"></textarea>
                                    <has-error :form="form" field="journal"></has-error>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="doc" role="tabpanel" aria-labelledby="doc-tab">
                                <table class="table">
                                    <tr v-for="docs in data_raw.documents">
                                        <td>
                                            <file-preview v-if="docs.file" :thumbnail="true" :link="'/storage/' + docs.file"></file-preview>
                                        </td>
                                        <td>{{docs.category}}</td>
                                        <td>{{docs.title}}</td>
                                        <td>
                                            <button class="btn btn-outline-danger btn-sm" @click="deleteAttachment(docs)">Hapus</button>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Tutup</button>
                        <button v-show="!editMode" type="submit" class="btn btn-sm btn-th-primary" @click="createData">
                            Simpan
                        </button>
                        <button v-show="editMode" type="submit" class="btn btn-sm btn-th-primary" @click="updateData">
                            Ubah
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="uploadModal" tabindex="-1" role="dialog"
             aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Lampiran</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Nama Dokumen</label>
                                    <input class="form-control" v-model="attach.name"
                                           :class="{ 'is-invalid': attach.errors.has('name') }">
                                    <has-error :form="attach" field="name"></has-error>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kategori Dokumen</label>
                                    <select class="form-control" v-model="attach.category"
                                            :class="{ 'is-invalid': attach.errors.has('category') }">
                                        <option v-for="cat in data_raw.categories" :value="cat.tag">{{cat.text}}</option>
                                    </select>
                                    <has-error :form="attach" field="category"></has-error>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>File</label> <br>
                                    <input style="border: 1px solid grey; border-radius: 3px; padding: 2px" type="file"
                                           id="upload_file" :class="{ 'is-invalid': attach.errors.has('file') }">
                                    <has-error :form="attach" field="file"></has-error>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button :disabled="disable" class="btn btn-primary" @click="uploadFile">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status"
                                  aria-hidden="true"></span>
                            Tambah Lampiran
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Docs from "./Docs";
export default {
    components:{Docs},
    data() {
        return {
            base_url: '/sadmin/student-regs',
            editMode: false,
            disable: false,
            is_loading: false,
            dataContent: {},
            dataDetail: '',
            data_raw: {
                documents:[],
                image_url: '',
                categories: [
                    {tag:'cv','text':'Curriculum Vitae'},
                    {tag:'ktp','text':'KTP'},
                    {tag:'permission','text':'Surat ijin belajar'},
                    {tag:'written_exam','text':'Nilai Ujian tulis akademik'},
                    {tag:'psychology','text':'Nilai Psikiatri MMPI'},
                    {tag:'health','text':'Hasil Tes Kesehatan'},
                    {tag:'interview','text':'Nilai Wawancara di Prodi'},
                    {tag:'journal_reading','text':'Nilai Jurnal reading'},
                    {tag:'other','text':'Lain-lain'},
                ],
            },
            form: new form({
                id: '',
                name: '',
                photo: '',
                phone: '',
                email: '',
                address: '',
                status: '',
                date: '',

                cv: '',
                permission: '',
                written_exam: '',
                notes: '',
                psychology: '',
                health: '',
                interview: '',
                journal: '',
            }),
            attach: new form({
                name:'',
                student_reg_id:'',
                category:'',
                file:'',
            }),
            filter: new form({
                name: '',
                page: ''
            })
        }
    },
    methods: {
        loadDataContent(page = 1) {
            this.filter.page = page;
            this.is_loading = true;
            axios.get(this.base_url, {params: this.filter})
                .then(({data}) => {
                    this.dataContent = data;
                    this.is_loading = false;
                }).catch(() => {
                this.is_loading = false;
            })
        },
        getImage(e) {
            let file = e.target.files[0];

            let reader = new FileReader();
            if (file['size'] < 2200000) {
                reader.onloadend = (file) => {
                    this.form.image = reader.result;
                };
                this.data_raw.image_url = URL.createObjectURL(file);
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
            axios.get(this.base_url + '?page=' + page)
                .then(response => {
                    this.dataContent = response.data;
                    this.filter.page = response.data.current_page
                });
            this.$Progress.finish()
        },
        withFilter() {
            this.$Progress.start();
            axios.get(this.base_url, {
                params: this.filter
            }).then((response) => {
                this.dataContent = response.data
                this.$Progress.finish();
            });
        },
        resetData() {
            this.filter.reset();
            this.loadDataContent()
        },
        addModal() {
            this.form.reset();
            this.editMode = false;
            $('#editAddModal').modal('show')
        },
        createData() {
            this.$Progress.start();
            this.form.post(this.base_url)
                .then((response) => {
                    Fire.$emit('ModalSuccess');
                }).catch((error) => {

            });
        },
        editModal(data) {
            this.editMode = true;
            this.data_raw.image_url = '';
            $('#editAddModal').modal({backdrop: 'static', keyboard: false});
            this.form.fill(data);
            this.data_raw.documents = data.documents;
        },
        uploadModal(data){
            this.attach.student_reg_id = data.id;
            $('#uploadModal').modal('show')
        },
        uploadFile(){
            this.disable = true;
            const form_file = document.getElementById('upload_file');

            const formData = new FormData();
            if (document.getElementById('upload_file').value !== '') {
                formData.append('file', form_file.files[0]);
            }

            formData.append('student_reg_id', this.attach.student_reg_id ?? '');
            formData.append('category', this.attach.category ?? '');
            formData.append('name', this.attach.name ?? '');

            axios.post('/sadmin/student-regs-upload', formData)
                .then(({data}) => {
                    if(data.status){
                        this.disable = false;
                        $('#uploadModal').modal('hide');
                        this.loadDataContent();
                        $('#upload_file').val('')
                        this.attach.clear()
                    }

                }).catch((e) => {
                    this.disable = false;
                    let errors = e.response.data.errors;

                    if (errors) {
                        this.attach.errors.errors = errors;
                    }
            });
        },
        updateData() {
            this.$Progress.start();
            this.form.patch(this.base_url + '/' + this.form.id)
                .then((response) => {
                    Fire.$emit('ModalSuccess');
                }).catch((error) => {

            });
        },
        detailModal(data) {
            $('#detailModal').modal('show');
            this.dataDetail = data;
        },
        deleteData(id) {
            if(confirm('Hapus data?')){
                axios.delete(this.base_url + '/' + id).then(({data}) => {
                    if(data.status){
                        this.loadDataContent();
                    }
                }).catch((error) => {

                });
            }
        },
        deleteAttachment(data){

        }
    },
    created() {
        this.loadDataContent();
        Fire.$on('ModalSuccess', () => {
            this.loadDataContent();
            this.popGlobalSuccess({});
            $('#editAddModal').modal('hide');
            this.$Progress.finish();
        })
    }
}
</script>
