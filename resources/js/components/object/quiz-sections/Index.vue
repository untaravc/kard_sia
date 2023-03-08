<template>
    <div class="content-wrapper">
        <div class="content">
            <div class="row">
                <div class="col-12">
                    <div class="card mt-2">
                        <div class="card-header">
                            <h1 class="card-title">Paket Ujian</h1>
                            <div class="card-tools">
                                <button class="btn btn-sm btn-th-primary" @click="addModal">
                                    <i class="fa fa-plus"></i> Tambah
                                </button>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <vue-loader :active.sync="is_loading" :can-cancel="false" loader="dots"
                                        :is-full-page="false"></vue-loader>
                            <table class="table table-head-fixed">
                                <tr>
                                    <th style="width: 40px">No</th>
                                    <th>Nama</th>
                                    <th>Pengampu</th>
                                    <th><span style="float: right">Aksi</span></th>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>
                                    <input type="text" v-model="filter.question" @keyup.enter="loadDataContent"
                                           class="form-control" placeholder="Cari..">
                                    </td>
                                    <td>
                                    </td>
                                    <td></td>
                                </tr>
                                <tbody>
                                <tr role="row" class="odd" v-for="(data, i) in dataContent.data" :key="data.id">
                                    <td>
                                        <span :title="data.id">
                                            {{ dataContent.per_page * (dataContent.current_page - 1) + i + 1 }}
                                        </span>
                                    </td>
                                    <td>
                                        <span>{{data.name}}</span> <br>
                                        <small class="text-black-50">{{ data.created_at | formatDateTime }}</small>
                                    </td>
                                    <td>
                                        <span v-if="data.lecture">{{data.lecture.name}}</span>
                                    </td>
                                    <td class="action-button">
                                        <button class="btn btn-sm btn-th-primary" @click="editModal(data)">
                                            Edit
                                        </button>
                                        <router-link class="btn btn-sm btn-success" :to="'/object/quiz-sections/' + data.id">
                                            Soal
                                        </router-link>
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
                                        <pagination :data="dataContent"
                                                    @pagination-change-page="loadDataContent"></pagination>
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
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" v-show="!editMode">Tambah</h5>
                        <h5 class="modal-title" v-show="editMode">Ubah</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="col-12">
                            <label>Dosen Pengampu</label>
                            <select v-model="form.lecture_id"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.has('lecture_id') }">
                                <option value="">Pilih Dosen</option>
                                <option :value="lecture.id" v-for="lecture in data_raw.lectures">
                                    {{ lecture.name }}
                                </option>
                            </select>
                            <has-error :form="form" field="lecture_id"></has-error>
                        </div>
                        <div class="col-12">
                            <label>Nama</label>
                            <input type="text" class="form-control" v-model="form.name"
                                   :class="{ 'is-invalid': form.errors.has('name') }">
                            <has-error :form="form" field="name"></has-error>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Tutup</button>
                        <button v-show="!editMode" type="submit" class="btn btn-sm btn-th-primary" @click="createData">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status"
                                  aria-hidden="true"></span>
                            Simpan
                        </button>
                        <button v-show="editMode" type="submit" class="btn btn-sm btn-th-primary" @click="updateData">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status"
                                  aria-hidden="true"></span>
                            Ubah
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="sendModal" tabindex="-1" role="dialog"
             aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Jadwalkan Ke Residen</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="col-12">
                            <label>Dosen Pengampu</label>
                            <select v-model="form.lecture_id"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.has('lecture_id') }">
                                <option value="">Pilih Dosen</option>
                                <option :value="lecture.id" v-for="lecture in data_raw.lectures">
                                    {{ lecture.name }}
                                </option>
                            </select>
                            <has-error :form="form" field="lecture_id"></has-error>
                        </div>
                        <div class="col-12">
                            <label>Nama</label>
                            <input type="text" class="form-control" v-model="form.name"
                                   :class="{ 'is-invalid': form.errors.has('name') }">
                            <has-error :form="form" field="name"></has-error>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Tutup</button>
                        <button v-show="!editMode" type="submit" class="btn btn-sm btn-th-primary" @click="createData">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status"
                                  aria-hidden="true"></span>
                            Simpan
                        </button>
                        <button v-show="editMode" type="submit" class="btn btn-sm btn-th-primary" @click="updateData">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status"
                                  aria-hidden="true"></span>
                            Ubah
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { VueEditor } from "vue2-editor";
export default {
    components:{VueEditor},
    data() {
        return {
            base_url: '/objects/sections',
            editMode: false,
            is_loading: false,
            disable: false,
            dataContent: {},
            dataDetail: '',
            options: {
                option_1: false,
                option_2: false,
                option_3: false,
                option_4: false,
                option_5: false,
            },
            data_raw: {
                image_url: '',
                lectures: [],
                categories: [],
                students: [],
            },
            form: new form({
                id: '',
                lecture_id: '',
                name: '',
            }),
            filter: new form({
                question: '',
                category: '',
                page: ''
            })
        }
    },
    methods: {
        loadDataContent(page = 1) {
            this.filter.page = page
            this.is_loading = true
            axios.get(this.base_url, {params: this.filter})
                .then(({data}) => {
                    this.dataContent = data;
                    this.is_loading = false;
                }).catch(() => {
                this.is_loading = false;
            })
        },
        loadLectures() {
            axios.get('/get-lectures')
                .then(({data}) => {
                    this.data_raw.lectures = data
                })
        },
        resetData() {
            this.filter.reset();
            this.loadDataContent()
        },
        addModal() {
            this.form.reset();
            this.editMode = false;
            $('#editAddModal').modal({backdrop: 'static', keyboard: false})
        },
        createData() {
            this.disable = true;
            this.form.post(this.base_url)
                .then(({data}) => {
                    this.disable = false;
                    $('#editAddModal').modal('hide');
                    this.popGlobalSuccess({});
                    this.loadDataContent();
                }).catch((error) => {
                this.disable = false;
            });
        },
        editModal(data) {
            this.editMode = true;
            this.form.fill(data);
            this.options[data.answer] = true;
            $('#editAddModal').modal({backdrop: 'static', keyboard: false});
        },
        updateData() {
            this.disable = true;
            this.form.patch(this.base_url + '/' + this.form.id)
                .then(({data}) => {
                    this.disable = false;
                    $('#editAddModal').modal('hide');
                    this.popGlobalSuccess({});
                    this.loadDataContent();
                }).catch((error) => {
                this.disable = false;
            });
        },
        deleteData(id) {
            if (confirm('Hapus data?')) {
                this.form.delete(this.base_url + '/' + id).then((response) => {
                    this.loadDataContent();
                    this.$Progress.finish();
                }).catch((errror) => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Unauthorized',
                        text: 'You can not delete this data!',
                    })
                });
            }
        },
        sendModal(){
            $('#sendModal').modal();
        },
        loadStudentList() {
            axios.get('/sadmin/student-list')
                .then(({data})=>{
                    this.data_raw.students = data.result;
                })
        }
    },
    created() {
        this.loadDataContent();
        this.loadLectures();
    },
}
</script>

<style scoped>
input.largerCheckbox {
    transform: scale(2);
}

.form-check {
    width: 20px;
    margin-left: 10px;
    margin-top: 5px;
}
</style>
