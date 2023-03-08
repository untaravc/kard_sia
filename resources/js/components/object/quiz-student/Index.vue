<template>
    <div class="content-wrapper">
        <div class="content">
            <div class="row pt-2">
                <div class="col-md-6">
                    <div class="card mb-1 pointer-event" @click="$router.push('/object/quiz-sections/' + $route.params.id)">
                        <div class="card-body">
                            <u>Paket Soal</u>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-1" >
                        <div class="card-body">
                            <b>Kirim Paket Ujian</b>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card mt-2">
                        <div class="card-header">
                            <h1 class="card-title">Kirim Paket Ujian</h1>
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
                                    <th>Detail</th>
                                    <th>Status</th>
                                    <th><span style="float: right">Aksi</span></th>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>
                                        <input type="text" v-model="filter.question" @keyup.enter="loadDataContent"
                                           class="form-control" placeholder="Cari..">
                                    </td>
                                    <td></td>
                                    <td></td>
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
                                        <span v-if="data.student">{{data.student.name}}</span> <br>
                                        <small class="text-black-50">{{ data.date_at | formatDate }}</small>
                                    </td>
                                    <td>
                                        <small>
                                            Jumlah Dijawab: {{data.answered}} <br>
                                            Benar: {{data.answer_true}} <br>
                                            Link:
                                            <span style="background-color: #c4c4c4; padding: 2px 3px">{{data.link}}</span>
                                            <button @click="copyText(data.link)" class="btn btn-sm btn-outline-secondary">
                                                <i class="fa fa-copy"></i>
                                            </button>
                                        </small>
                                    </td>
                                    <td>
                                        <small>
                                            Status: {{data.status_label}} <br>
                                            Tipe: {{data.is_closed_label}}
                                        </small>
                                    </td>
                                    <td class="action-button">
                                        <button class="btn btn-sm btn-th-primary" @click="editModal(data)">
                                            Edit
                                        </button>
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
                            <label>Residen</label>
                            <v-select :options="data_raw.students" label="name" :reduce="name => name.id"
                                      v-model="form.student_id"></v-select>
                            <has-error :form="form" field="student_id"></has-error>
                        </div>
                        <div class="col-12">
                            <label>Tanggal</label>
                            <input type="date" class="form-control" v-model="form.date_at"
                                   :class="{ 'is-invalid': form.errors.has('date_at') }">
                            <has-error :form="form" field="date_at"></has-error>
                        </div>
                        <div class="col-12">
                            <label>Status</label>
                            <select class="form-control" v-model="form.status"
                                    :class="{ 'is-invalid': form.errors.has('status') }">
                                <option value="0">Draft</option>
                                <option value="1">Open</option>
                                <option value="2">Finish</option>
                            </select>
                            <has-error :form="form" field="status"></has-error>
                        </div>
                        <div class="col-12">
                            <label>Tipe</label>
                            <select class="form-control" v-model="form.is_closed"
                                    :class="{ 'is-invalid': form.errors.has('is_closed') }">
                                <option value="0">Terbuka</option>
                                <option value="1">Harus Login</option>
                            </select>
                            <has-error :form="form" field="is_closed"></has-error>
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
import 'vue-select/dist/vue-select.css';
export default {
    data() {
        return {
            base_url: '/objects/quiz-students',
            editMode: false,
            is_loading: false,
            disable: false,
            dataContent: {},
            data_raw: {
                students: [],
            },
            form: new form({
                id: '',
                student_id: '',
                date_at: '',
                status: '',
                is_closed: '',
            }),
            filter: new form({
                section_id: this.$route.params.id,
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
            this.form.section_id = this.$route.params.id;
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
            this.form.date_at = data.date_at.substring(0,10);
            $('#editAddModal').modal({backdrop: 'static', keyboard: false});
        },
        updateData() {
            this.disable = true;
            this.form.section_id = this.$route.params.id;

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
        },
        copyText(value){
            navigator.clipboard.writeText(value);
            this.popGlobalSuccess({title: 'Link copied.'})
        }
    },
    created() {
        this.loadDataContent();
        this.loadStudentList();
    },
}
</script>

<style scoped>
.pointer-event{
    cursor: pointer;
}

.pointer-event:hover{
    /*background-color: #d3d3d3;*/
    box-shadow: 0 0 1px rgb(0 0 0 / 13%), 0 0 0 rgb(0 0 0 / 20%);
}
</style>
