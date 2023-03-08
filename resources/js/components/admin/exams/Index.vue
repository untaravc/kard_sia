<template>
    <div class="content-wrapper">
        <div class="content">
            <div class="row">
                <div class="col-12">
                    <div class="card mt-2">
                        <div class="card-header">
                            <h1 class="card-title">Data Ujian Onlen</h1>
                            <div class="card-tools">
                                <button class="btn btn-sm btn-th-primary" @click="addModal">
                                    <i class="fa fa-user-plus"></i> Tambah
                                </button>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <vue-loader :active.sync="is_loading" :can-cancel="false" loader="dots"
                                        :is-full-page="false"></vue-loader>
                            <table class="table table-head-fixed">
                                <tr>
                                    <th width="50px">No</th>
                                    <th width="35%">Nama</th>
                                    <th>Scope</th>
                                    <th><span style="float: right">Aksi</span></th>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>
                                        <input type="text" v-model="filter.name" @keyup.enter="loadDataContent"
                                               class="form-control" placeholder="Cari nama..">
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tbody>
                                <tr role="row" class="odd" v-for="(data, i) in dataContent.data" :key="data.id">
                                    <td><span
                                        :title="data.id">{{dataContent.per_page * (dataContent.current_page - 1) + i + 1 }} </span>
                                    </td>
                                    <td>
                                        <b>{{ data.name }}</b>
                                        <br>
                                        <small style="color: #0a58ca">https://sia.kardiologi-fkkmk.com/e/{{ data.token }} </small>
                                    </td>
                                    <td>
                                        <span v-if="data.lecture">{{data.lecture.name}}<br></span>
                                        <span v-if="data.stase">{{data.stase.name}}<br></span>
                                        <span v-if="data.stase_task">{{data.stase_task.name}}</span>
                                    </td>
                                    <td class="action-button">
                                        <button class="btn btn-wd btn-sm btn-th-primary" @click="editModal(data)">
                                            Ubah
                                        </button>
                                        <router-link :to="`/cmss/exams/`+data.id"
                                                     class="btn btn-wd btn-sm btn-th-secondary">
                                            Detail
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
                        <form @submit.prevent="createData">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Nama</label>
                                        <input v-model="form.name" type="email"
                                               class="form-control" :class="{ 'is-invalid': form.errors.has('email') }">
                                        <has-error :form="form" field="email"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Link</label>
                                        <textarea v-model="form.link"
                                                  class="form-control"
                                                  :class="{ 'is-invalid': form.errors.has('link') }"></textarea>
                                        <has-error :form="form" field="link"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Deskripsi</label>
                                        <textarea v-model="form.desc"
                                                  class="form-control"
                                                  :class="{ 'is-invalid': form.errors.has('desc') }"></textarea>
                                        <has-error :form="form" field="desc"></has-error>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Dosen</label>
                                        <select class="form-control" v-model="form.lecture_id"
                                                :class="{ 'is-invalid': form.errors.has('lecture_id') }">
                                            <option v-for="lecture in dataRaw.lectures" :value="lecture.id">
                                                {{ lecture.name }}
                                            </option>
                                        </select>
                                        <has-error :form="form" field="lecture_id"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Stase</label>
                                        <select class="form-control" @change="loadStaseTask" v-model="form.stase_id"
                                                :class="{ 'is-invalid': form.errors.has('stase_id') }">
                                            <option v-for="stase in dataRaw.stase" :value="stase.id">{{ stase.name }}
                                            </option>
                                        </select>
                                        <has-error :form="form" field="stase_id"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tugas Stase</label>
                                        <select class="form-control" v-model="form.stase_task_id"
                                                :class="{ 'is-invalid': form.errors.has('stase_task_id') }">
                                            <option v-for="task in dataRaw.stase_tasks" :value="task.id">
                                                {{ task.name }}
                                            </option>
                                        </select>
                                        <has-error :form="form" field="stase_task_id"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Durasi (menit)</label>
                                        <input v-model="form.duration" type="number"
                                               class="form-control"
                                               :class="{ 'is-invalid': form.errors.has('duration') }">
                                        <has-error :form="form" field="duration"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Batas Awal</label>
                                        <VueCtkDateTimePicker
                                            :no-label="true"
                                            id="available_at"
                                            v-model="form.available_at"
                                            format="YYYY-MM-DD HH:mm"
                                            minute-interval="5"
                                            locale="id"/>
                                        <has-error :form="form" field="available_at"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Batas Akhir</label>
                                        <VueCtkDateTimePicker
                                            :no-label="true"
                                            id="expired_at"
                                            v-model="form.expired_at"
                                            format="YYYY-MM-DD HH:mm"
                                            minute-interval="5"
                                            locale="id"/>
                                        <has-error :form="form" field="expired_at"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select class="form-control" v-model="form.status"
                                                :class="{ 'is-invalid': form.errors.has('status') }">
                                            <option value="0">Draft</option>
                                            <option value="1">Aktif</option>
                                        </select>
                                        <has-error :form="form" field="status"></has-error>
                                    </div>
                                </div>

                            </div>
                        </form>
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
    </div>
</template>

<script>
export default {
    data() {
        return {
            editMode: false,
            is_loading: false,
            dataContent: {},
            dataDetail: '',
            dataRaw: {
                stase: [],
                stase_tasks: [],
                lectures: [],
            },
            form: new form({
                id: '',
                name: '',
                desc: '',
                lecture_id: '',
                stase_id: '',
                stase_task_id: '',
                available_at: '',
                expired_at: '',
                link: '',
                duration: '',
                status: '',
            }),
            filter: new form({
                page: '',
                name: '',
            })
        }
    },
    methods: {
        loadDataContent(page = 1) {
            this.filter.page = page
            this.is_loading = true
            axios.get('/sadmin/exams', {params: this.filter})
                .then(({data}) => {
                    this.dataContent = data;
                    this.is_loading = false
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
            this.form.post('/sadmin/exams')
                .then(({data}) => {
                    if (data.status) {
                        $('#editAddModal').modal('hide');
                        this.loadDataContent();
                    }
                }).catch((error) => {

            });
        },
        editModal(data) {
            this.editMode = true;
            $('#editAddModal').modal({backdrop: 'static', keyboard: false});
            this.form.fill(data);
        },
        updateData() {
            this.$Progress.start();
            this.form.patch('/sadmin/exams/' + this.form.id)
                .then((response) => {
                    $('#editAddModal').modal('hide')
                    this.loadDataContent();
                }).catch((error) => {
            });
        },
        deleteData(id) {

        },

        loadLectures() {
            axios.get('/get-lectures')
                .then(({data}) => {
                    this.dataRaw.lectures = data;
                })
        },
        loadStase() {
            axios.get('/sadmin/get-stases')
                .then(({data}) => (this.dataRaw.stase = data))
        },
        loadStaseTask() {
            axios.get('/sadmin/get-stase-tasks/' + this.form.stase_id)
                .then(({data}) => (this.dataRaw.stase_tasks = data))
        }
    },
    created() {
        this.loadDataContent();
        this.loadStase();
        this.loadLectures();
    },
    watch: {
        'form.stase_id': function (val) {
            if (val) {
                this.loadStaseTask()
            }
        }
    }
}
</script>
