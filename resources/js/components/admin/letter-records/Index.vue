<template>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1><i class="fa fa-file-alt"></i> Surat Prodi</h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <button @click="addModal('send')" class="btn btn-primary btn-sm mb-2">
                            <i class="fa fa-upload"></i> Tambah Baru
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-body p-0">
                            <div class="table-responsive mailbox-messages">
                                <table class="table table-hover table-striped">
                                    <thead>
                                    <tr>
                                        <th width="50px">No</th>
                                        <th>Nomor</th>
                                        <th>Kategori</th>
                                        <th>Nama</th>
                                        <th>Dokumen</th>
                                        <th style="max-width: 130px" class="text-right">Aksi</th>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>
                                            <input type="text" class="form-control" placeholder="Cari nomor.."
                                                   @keyup.enter="loadDataContent" v-model="filter.number">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" placeholder="Cari kategori.."
                                                   @keyup.enter="loadDataContent" v-model="filter.category">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" placeholder="Cari nama.."
                                                   @keyup.enter="loadDataContent" v-model="filter.name">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" placeholder="Cari dokumen.."
                                                   @keyup.enter="loadDataContent" v-model="filter.link">
                                        </td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <loading :active.sync="isLoading" :can-cancel="false" loader="dots"
                                             :is-full-page="false"></loading>
                                    <tr v-for="(data, i) in dataContent.data" :key="data.id">
                                        <td>
                                            <span :title="data.id">
                                                {{ dataContent.per_page * (dataContent.current_page - 1) + i + 1 }}
                                            </span>
                                        </td>
                                        <td>
                                            <span v-if="data.status == 1" class="badge badge-success">publish</span>
                                            <span v-if="data.status == 0" class="badge badge-success">draft</span>
                                            {{ data.number }}
                                            <br>
                                            <small v-if="data.user">Owner: {{ data.user.name }}</small>
                                        </td>
                                        <td>
                                            <i>{{ data.category }}</i><br>
                                            <small>Tgl {{ data.date | formatDate }}</small>
                                        </td>
                                        <td>
                                            <span>{{ data.name }}</span>
                                        </td>
                                        <td>
                                            <file-preview v-if="data.file"
                                                          :link="'/storage/' + data.file"></file-preview>
                                            <small v-if="data.link">
                                                <a target="_blank"
                                                   :href="data.link">{{ data.link | truncate(30) }}</a>
                                            </small>
                                        </td>
                                        <td class="text-right">
                                            <div class="dropdown">
                                                <a href="#" role="button" data-toggle="dropdown"
                                                                     aria-haspopup="true" aria-expanded="false"
                                                                     class="btn btn-light">
                                                    <i class="fa fa-ellipsis-v"></i>
                                                </a>
                                                <div aria-labelledby="dropdownMenuLink" class="dropdown-menu" style="">
                                                    <a href="#" @click="editModal(data)" class="dropdown-item">Edit</a>
                                                    <a href="#" @click="tagModal(data)" class="dropdown-item">Link Residen & Dosen</a>
                                                    <a href="#" @click="deleteData(data.id)" class="dropdown-item">Hapus</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
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
                                        <pagination :data="dataContent"
                                                    @pagination-change-page="loadDataContent"></pagination>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="modal fade bd-example-modal-lg" id="tagModal" tabindex="-1" role="dialog"
             aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Hubungkan ke Dosen & Mahasiswa</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h5>{{form.name}}</h5>
                        <div class="form-group">
                            <label>Residen</label>
                            <multiselect
                                v-model="tags.students"
                                :multiple="true"
                                label="name"
                                :options="dataRaw.students"></multiselect>
                        </div>
                        <div class="form-group">
                            <label>Dosen</label>
                            <multiselect
                                v-model="tags.lectures"
                                :multiple="true"
                                label="name"
                                :options="dataRaw.lectures"></multiselect>
                        </div>
                        <button class="btn btn-success" @click="updateTags" :disabled="disable" style="width: 100%">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status"
                                  aria-hidden="true"></span>
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="editAddModal" tabindex="-1" role="dialog"
             aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 v-if="editMode" class="modal-title">Ubah Surat</h5>
                        <h5 v-if="!editMode" class="modal-title">Tambah Surat</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Nomor</label>
                                    <input class="form-control" v-model="form.number"
                                           :class="{ 'is-invalid': form.errors.has('number') }">
                                    <has-error :form="form" field="number"></has-error>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Nama</label>
                                    <input class="form-control" v-model="form.name"
                                           :class="{ 'is-invalid': form.errors.has('name') }">
                                    <has-error :form="form" field="name"></has-error>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kategori</label>
                                    <input class="form-control" v-model="form.category"
                                           :class="{ 'is-invalid': form.errors.has('category') }">
                                    <has-error :form="form" field="category"></has-error>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal</label>
                                    <input class="form-control" type="date" v-model="form.date"
                                           :class="{ 'is-invalid': form.errors.has('date') }">
                                    <has-error :form="form" field="date"></has-error>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Deskripsi</label>
                                    <textarea class="form-control" type="date" v-model="form.desc"
                                              :class="{ 'is-invalid': form.errors.has('desc') }"></textarea>
                                    <has-error :form="form" field="desc"></has-error>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Link Lampiran</label>
                                    <input type="text" class="form-control" v-model="form.link"
                                           :class="{ 'is-invalid': form.errors.has('link') }">
                                    <has-error :form="form" field="link"></has-error>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select type="text" class="form-control" v-model="form.status"
                                            :class="{ 'is-invalid': form.errors.has('status') }">
                                        <option value="0">Draft</option>
                                        <option value="1">Publish</option>
                                    </select>
                                    <has-error :form="form" field="status"></has-error>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>File</label> <br>
                                    <input style="border: 1px solid grey; border-radius: 3px; padding: 2px" type="file"
                                           id="form_file" :class="{ 'is-invalid': form.errors.has('file') }">
                                    <has-error :form="form" field="file"></has-error>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button :disabled="disable" v-show="editMode" class="btn btn-primary" @click="updateData">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status"
                                  aria-hidden="true"></span>
                            Perbarui
                        </button>
                        <button :disabled="disable" v-show="!editMode" class="btn btn-primary" @click="createData">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status"
                                  aria-hidden="true"></span>
                            Tambah
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
            editMode: false,
            sendMode: true,
            isLoading: false,
            disable: false,
            dataContent: {},
            tags: {
                students:{},
                lectures:{},
            },
            dataDetail: '',
            dataRaw: {
                file: '',
                categories: [],
                students: [],
                lectures: [],
            },
            form: new form({
                id: '',
                number: '',
                user_id: '',
                name: '',
                desc: '',
                category: '',
                file: '',
                link: '',
                date: '',
                status: 1,
            }),
            filter: {
                page: '',
                category: '',
                number: '',
                name: '',
                link: '',
            }
        }
    },
    methods: {
        loadDataContent(page = 1) {
            this.filter.page = page;
            this.isLoading = true;
            axios.get('/sadmin/letter-records', {params: this.filter})
                .then(({data}) => {
                    this.dataContent = data;
                    this.isLoading = false;
                }).catch((error) => {
                this.isLoading = false;
            })
        },
        addModal() {
            $('#editAddModal').modal('show');
        },
        resetData() {
            this.filter.reset();
            this.loadDataContent()
        },
        tagModal(data) {
            $('#tagModal').modal({backdrop: 'static', keyboard: false});

            this.form.fill(data);
            this.tags.lectures = data.lectures;
            this.tags.students = data.students;
        },
        loadStudentList() {
            axios.get('/sadmin/student-list')
            .then(({data})=>{
                this.dataRaw.students = data.result;
            })
        },
        loadLectureList() {
            axios.get('/sadmin/lecture-list')
            .then(({data})=>{
                this.dataRaw.lectures = data.result;
            })
        },
        editModal(data) {
            this.editMode = true;
            $('#editAddModal').modal({backdrop: 'static', keyboard: false});
            this.form.fill(data);
        },
        createData() {
            this.disable = true;
            const form_file = document.getElementById('form_file');

            const formData = new FormData();
            if (document.getElementById('form_file').value !== '') {
                formData.append('file', form_file.files[0]);
            }

            formData.append('number', this.form.number ?? '');
            formData.append('name', this.form.name ?? '');
            formData.append('desc', this.form.desc ?? '');
            formData.append('category', this.form.category ?? '');
            formData.append('link', this.form.link ?? '');
            formData.append('status', this.form.status ?? '1');
            formData.append('date', this.form.date ?? '');

            axios.post('/sadmin/letter-records', formData)
                .then(({data}) => {
                    if (data.status) {
                        $('#editAddModal').modal('hide');
                        this.loadDataContent();
                        $('#form_file').val('')
                    }
                    this.disable = false;
                }).catch((e) => {
                this.disable = false;
                let errors = e.response.data.errors;

                if (errors) {
                    this.form.errors.errors = errors;
                }
            });
        },
        updateData() {
            this.disable = true;
            const form_file = document.getElementById('form_file');

            const formData = new FormData();
            if (document.getElementById('form_file').value != '') {
                formData.append('file', form_file.files[0]);
            }

            formData.append('number', this.form.number ?? '');
            formData.append('name', this.form.name ?? '');
            formData.append('desc', this.form.desc ?? '');
            formData.append('category', this.form.category ?? '');
            formData.append('link', this.form.link ?? '');
            formData.append('status', this.form.status ?? '1');
            formData.append('date', this.form.date ?? '');

            axios.post('/sadmin/letter-records/' + this.form.id, formData)
                .then(({data}) => {
                    this.disable = false;
                    $('#editAddModal').modal('hide');
                    this.loadDataContent();
                    $('#form_file').val('')
                }).catch((e) => {
                this.disable = false;
                let errors = e.response.data.errors;

                if (errors) {
                    this.form.errors.errors = errors;
                }
            });
        },
        deleteData(id) {
            Swal.fire({
                title: 'Are you sure to delete this data?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.value) {
                    axios.delete('/sadmin/letter-records/' + id)
                        .then(({data}) => {
                            if (data.status) {
                                this.loadDataContent(this.filter.page);
                                Swal.fire(
                                    'Hapus!',
                                    'Berhasil hapus data.',
                                    'success'
                                )
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: data.message,
                                })
                            }
                        }).catch((error) => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Unauthorized',
                            text: 'You can not delete this data!',
                        })
                    });
                }
            })
        },
        popSuccessSwal() {
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: 'Success',
                toast: true,
                showConfirmButton: false,
                timer: 1000,
            })
        },
        updateTags(){
            this.disable = true;
            axios.post('sync-letter',{
                letter_id: this.form.id,
                students: this.tags.students,
                lectures: this.tags.lectures,
            }).then(({data})=>{
                $('#tagModal').modal('hide');
                this.loadDataContent(this.filter.page);
                this.disable = false;
            }).catch(()=>{
                this.disable = false;
            })
        }
    },
    created() {
        this.loadDataContent();
        this.loadStudentList();
        this.loadLectureList();

        Fire.$on('ModalSuccess', () => {
            this.loadDataContent();
            this.popSuccessSwal();
            $('#editAddModal').modal('hide');
            this.$Progress.finish();
        })
    },
}
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<style>
.autocomplete-input {
    display: block;
    width: 100%;
    height: calc(2.25rem + 2px);
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    color: #495057;
    background-color: #ffffff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    box-shadow: inset 0 0 0 rgba(0, 0, 0, 0);
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}
</style>
