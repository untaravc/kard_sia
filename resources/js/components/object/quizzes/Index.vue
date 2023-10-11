<template>
    <div class="content-wrapper">
        <div class="content">
            <div class="row">
                <div class="col-12">
                    <div class="card mt-2">
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col">
                                    <i>Draft</i>
                                    <div class="stats">{{data_stats.draft}}</div>
                                </div>
                                <div class="col">
                                    <i>Belum Ada Gambar</i>
                                    <div class="stats">{{data_stats.img_null}}</div>
                                </div>
                                <div class="col">
                                    <i>Draft Bergambar</i>
                                    <div class="stats">{{data_stats.draft_img}}</div>
                                </div>
                                <div class="col">
                                    <i>Aktif</i>
                                    <div class="stats">{{data_stats.active}}</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" v-model="filter.question" class="form-control"
                                           @keyup.enter="loadDataContent" placeholder="Cari..">
                                </div>
                                <div class="col-md-4">
                                    <select class="form-control" v-model="filter.category" @change="loadDataContent">
                                        <option value="">Semua</option>
                                        <option :value="cat" v-for="cat in data_raw.categories">
                                            {{ cat }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-control" v-model="filter.status" @change="loadDataContent">
                                        <option value="">Semua</option>
                                        <option value="100">Draft</option>
                                        <option value="110">Belum ada gambar</option>
                                        <option value="111">Draft bergambar</option>
                                        <option value="200">Aktif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mt-2">
                        <div class="card-header">
                            <h1 class="card-title">Bank Soal</h1>
                            <div class="card-tools">
<!--                                <button class="btn btn-sm btn-th-primary" @click="addModal">-->
<!--                                    <i class="fa fa-user-plus"></i> Tambah-->
<!--                                </button>-->
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <vue-loader :active.sync="is_loading" :can-cancel="false" loader="dots"
                                        :is-full-page="false"></vue-loader>
                            <table class="table table-head-fixed">
                                <tr>
                                    <th style="width: 40px">No</th>
                                    <th>Soal</th>
                                </tr>
                                <tbody>
                                <tr role="row" class="odd" v-for="(data, i) in dataContent.data" :key="data.id">
                                    <td>
                                        <span :title="data.id">
                                            {{ dataContent.per_page * (dataContent.current_page - 1) + i + 1 }}
                                        </span>
                                    </td>
                                    <td class="pb-0">
                                        <span v-html="data.question"></span> <br>
                                        <small><b> Answer: <i>{{ data.answer_str }}</i></b></small>
                                        <div class="d-flex justify-content-between border-top">
                                            <div>
                                                <small class="text-black-50">
                                                    {{ data.created_at | formatDateTime }}</small>
                                                <small v-if="data.category" ><i>{{ data.category }}</i></small>
                                                <small class="text-black-50" v-if="data.book">Book :
                                                    {{ data.book }}</small>
                                            </div>
                                            <div>
                                                <i class="fa fa-edit pointer-event" @click="editModal(data)"></i>
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
                                        <pagination :data="dataContent" :limit="3"
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
                                <div class="col-md-6">
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
                                <div class="col-md-6">
                                    <label>Kategori</label> <br>
                                    <span>{{form.category}}</span>
                                </div>
                                <div class="col-md-12">
                                    <label>Pertanyaan</label>
                                    <vue-editor v-model="form.question"
                                                :class="{ 'is-invalid': form.errors.has('question') }"></vue-editor>
                                    <has-error :form="form" field="question"></has-error>
                                </div>
                                <div class="col-md-12">
                                    <div class="border p-1">
                                        <img v-if="form.question_image !== ''" :src="form.question_image"
                                             style="max-height: 200px">
                                        <input type="file" @change="uploadImage('question_image', 'question_image')"
                                               accept="image/png, image/gif, image/jpeg" id="question_image">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label>Opsi 1</label>
                                    <div class="d-flex" :class="{ 'is-invalid': form.errors.has('option_1') }">
                                        <textarea v-model="form.option_1" type="text"
                                                  class="form-control"></textarea>
                                        <div class="form-check">
                                            <input class="form-check-input largerCheckbox" v-model="options.option_1"
                                                   @change="selectOption($event)"
                                                   type="checkbox" value="option_1">
                                        </div>
                                    </div>
                                    <has-error :form="form" field="option_1"></has-error>
                                </div>
                                <div class="col-md-12">
                                    <label>Opsi 2</label>
                                    <div class="d-flex" :class="{ 'is-invalid': form.errors.has('option_2') }">
                                        <textarea v-model="form.option_2" type="text"
                                                  class="form-control"></textarea>
                                        <div class="form-check">
                                            <input class="form-check-input largerCheckbox" v-model="options.option_2"
                                                   @change="selectOption($event)"
                                                   type="checkbox" value="option_2">
                                        </div>

                                    </div>
                                    <has-error :form="form" field="option_2"></has-error>
                                </div>
                                <div class="col-md-12">
                                    <label>Opsi 3</label>
                                    <div class="d-flex">
                                        <textarea v-model="form.option_3" type="text"
                                                  class="form-control"
                                                  :class="{ 'is-invalid': form.errors.has('option_3') }"></textarea>
                                        <div class="form-check">
                                            <input class="form-check-input largerCheckbox" v-model="options.option_3"
                                                   @change="selectOption($event)"
                                                   type="checkbox" value="option_3">
                                        </div>
                                        <has-error :form="form" field="option_3"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label>Opsi 4</label>
                                    <div class="d-flex">
                                        <textarea v-model="form.option_4" type="text"
                                                  class="form-control"
                                                  :class="{ 'is-invalid': form.errors.has('option_4') }"></textarea>
                                        <div class="form-check">
                                            <input class="form-check-input largerCheckbox" v-model="options.option_4"
                                                   @change="selectOption($event)"
                                                   type="checkbox" value="option_4">
                                        </div>
                                        <has-error :form="form" field="option_4"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label>Opsi 5</label>
                                    <div class="d-flex">
                                        <textarea v-model="form.option_5" type="text"
                                                  class="form-control"
                                                  :class="{ 'is-invalid': form.errors.has('option_5') }"></textarea>
                                        <div class="form-check">
                                            <input class="form-check-input largerCheckbox" v-model="options.option_5"
                                                   @change="selectOption($event)"
                                                   type="checkbox" value="option_5">
                                        </div>
                                        <has-error :form="form" field="option_5"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <label>Catatan</label>
                                    <textarea type="number" class="form-control" v-model="form.note"
                                              :class="{ 'is-invalid': form.errors.has('note') }"></textarea>
                                    <has-error :form="form" field="note"></has-error>
                                </div>
                                <div class="col-md-3">
                                    <label>Nilai</label>
                                    <input type="number" class="form-control" v-model="form.score"
                                           :class="{ 'is-invalid': form.errors.has('score') }">
                                    <has-error :form="form" field="score"></has-error>
                                </div>
                                <div class="col-md-12">
                                    <span :class="{ 'is-invalid': form.errors.has('answer') }"></span>
                                    <has-error :form="form" field="answer"></has-error>
                                </div>
                            </div>
                        </form>
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
import {VueEditor} from "vue2-editor";

export default {
    components: {VueEditor},
    data() {
        return {
            base_url: '/objects/quizzes',
            editMode: false,
            is_loading: false,
            disable: false,
            dataContent: {},
            dataDetail: '',
            data_stats: {
                active: 0,
                draft: 0,
                draft_img: 0,
                img_null: 0,
            },
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
            },
            form: new form({
                id: '',
                lecture_id: '',
                category: '',
                question_image: '',
                question: '',
                option_1: '',
                option_2: '',
                option_3: '',
                option_4: '',
                option_5: '',
                answer: '',
                score: 1,
                note: '',
            }),
            filter: new form({
                question: '',
                category: '',
                page: '',
                status: '',
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
                    this.data_stats = data.stats;
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
        loadCategories() {
            axios.get('/objects/get-categories')
                .then(({data}) => {
                    this.data_raw.categories = data.result
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
            this.options = {
                option_1: false,
                option_2: false,
                option_3: false,
                option_4: false,
                option_5: false,
            };
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
        detailModal(data) {
            $('#detailModal').modal('show');
            this.dataDetail = data;
        },
        deleteData(id) {
            if (confirm('Hapus data?')) {
                this.form.delete(this.base_url + '/' + id).then((response) => {
                    this.loadDataContent();
                    Swal.fire(
                        'Deleted!',
                        'Data has been deleted.',
                        'success'
                    )
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
        selectOption(event) {
            let value = event.target.value;
            let checked = event.target.checked;

            this.options.option_1 = false;
            this.options.option_2 = false;
            this.options.option_3 = false;
            this.options.option_4 = false;
            this.options.option_5 = false;

            if (checked) {
                this.options[value] = true;
                this.form.answer = value;
            } else {
                this.options[value] = false;
                this.form.answer = '';
            }
        },
        uploadImage(container_id, return_link, form = 'form') {
            const form_file = document.getElementById(container_id);
            const formData = new FormData();
            if (document.getElementById(container_id).value !== '') {
                formData.append('image', form_file.files[0]);
            }
            this[form][return_link] = '';
            axios.post('/sadmin/image-upload', formData)
                .then(({data}) => {
                    if (data.status) {
                        document.getElementById(container_id).value = "";
                        this[form][return_link] = data.result.link;
                    }
                }).catch((e) => {
                return {status: false}
            });
        }
    },
    created() {
        this.loadDataContent();
        this.loadLectures();
        this.loadCategories();
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

.pointer-event {
    cursor: pointer;
}
.stats{
    font-size: 20px;
    font-weight: 600;
    text-decoration: overline;
    color: #4090ed;
}
</style>
