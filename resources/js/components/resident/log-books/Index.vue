<template>
    <div class="content-wrapper">
        <div class="content mt-3">
            <div class="row">
                <div class="col-12">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card card-primary card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">Log Book</h3>
                                </div>
                                <div class="card-body p-0">
                                    <div class="list-group">
                                        <div class="list-group-item">
                                            <input type="text" v-model="filter_stase"
                                                   v-debounce:600ms="loadStase"
                                                   class="form-control" placeholder="Cari stase..">
                                        </div>
                                        <div v-for="st in stase"
                                             class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                             :style="st.stase && stase_id === st.stase.id ? 'background-color: #3a8ce56b' :''">
                                            <small v-if="st.stase">{{ st.stase.name }}</small>
                                            <div class="btn-group" v-if="st.has_log > 0">
                                                <button type="button" class="btn btn-sm btn-info"
                                                        @click="loadLog(st.stase.id, true)">Logbook
                                                </button>
<!--                                                <button type="button"-->
<!--                                                        class="btn btn-sm btn-info dropdown-toggle dropdown-toggle-split"-->
<!--                                                        id="dropdownMenuReference" data-toggle="dropdown"-->
<!--                                                        aria-expanded="false" data-reference="parent">-->
<!--                                                    <span class="sr-only">Toggle Dropdown</span>-->
<!--                                                </button>-->
<!--                                                <div class="dropdown-menu" aria-labelledby="dropdownMenuReference">-->
<!--                                                    <button class="dropdown-item" @click="modalLog(st.stase.id)">-->
<!--                                                        Tambah-->
<!--                                                    </button>-->
<!--                                                    <button class="dropdown-item" @click="singleModal(st.stase)">-->
<!--                                                        Upload-->
<!--                                                    </button>-->
<!--                                                </div>-->
                                            </div>
                                            <small v-if="st.has_log === 0">
                                                <i class="text-black-50">tidak ada log book</i>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.card-body -->
                            </div>
                        </div>
                        <!-- /.col -->
                        <div class="col-md-8" id="right-side">
                            <div class="row">
                                <div class="col-md-4" v-for="skill in dataRaw.skills">
                                    <div class="card p-2">
                                        <div class="text-sm text-bold text-gray">{{skill.name}}</div>
                                        <div>{{skill.count}} / {{skill.desc}}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="card card-primary card-outline" v-for="book in logbook">
                                <div class="p-2">
                                    <small><b>{{ book.name }}</b></small>
                                    <div class="float-right">
                                        <button class="btn btn-sm btn-primary" @click="modalLog(book.relation_id, book.value)">
                                            Tambah <i class="fa fa-plus-square"></i>
                                        </button>
                                        <span >
                                            <a target="_blank" :href="'/cmsr/logbook-pdf/' + stase_id"
                                               class="btn btn-sm btn-light border-secondary">
                                                Cetak <i class="fa fa-print"></i>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <vue-loader :active.sync="loader_score" :can-cancel="false" loader="dots"
                                                :is-full-page="false"></vue-loader>
                                    <div class="table-responsive" v-if="book.logbook.length > 0">
                                        <table class="table table-custom" style="font-size: 13px">
                                            <tr v-if="book.parse_desc">
                                                <th>Tanggal</th>
                                                <th v-for="desc in book.parse_desc">
                                                    {{ desc }}
                                                </th>
                                                <th>Mengetahui</th>
                                                <th></th>
                                            </tr>
                                            <tr v-for="data in book.logbook">
                                                <td>{{ data.date | formatDate }}</td>
                                                <td v-for="(desc, key) in book.parse_desc">
                                                    {{ data[key] }}
                                                </td>
                                                <td>
                                                    <span v-if="data.lecture">
                                                        <i v-if="data.status === 0"
                                                           class="fa fa-clock text-secondary"></i>
                                                        <i v-if="data.status === 1"
                                                           class="fa fa-check-circle text-success"></i>
                                                        {{ data.lecture.name | truncate(10, '..') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm border-info m-1" @click="editLog(data)">
                                                        <i class="fa fa-edit text-info" style="width: 18px"></i>
                                                    </button>
                                                    <button class="btn btn-sm border-danger m-1"
                                                            @click="deleteLog(data.id)">
                                                        <i class="fa fa-trash text-danger" style="width: 18px"></i>
                                                    </button>
                                                    <a v-if="data.photo_link" :href="data.photo_link"
                                                            class="btn btn-sm border-secondary m-1" target="_blank">
                                                        <i class="fa fa-image text-secondary" style="width: 18px"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="text-center p-2" v-if="book.logbook.length === 0">
                                        <small>Belum ada data</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="modal-img" tabindex="-1" role="dialog"
             aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <img :src="dataRaw.photo_link" width="100%" alt="">
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="modal-log" tabindex="-1" role="dialog"
             aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">Update Logbook</div>
                    <div class="modal-body table-responsive">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal</label>
                                    <input class="form-control" type="date" v-model="logs.date">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Supervisor</label>
                                    <select class="form-control" v-model="logs.lecture_id">
                                        <option value="">Diluar Kardiologi</option>
                                        <option v-for="lecture in dataRaw.lectures" :value="lecture.id">{{ lecture.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipe</label>
                                    <select class="form-control" v-model="logs.type"
                                            :class="{ 'is-invalid': logs.errors.has('type') }" @change="parseQuestion">
                                        <option v-for="option in dataRaw.options" :value="option.value">{{ option.name }}
                                        </option>
                                    </select>
                                    <has-error :form="logs" field="type"></has-error>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kategori</label>
                                    <select class="form-control" v-model="logs.category"
                                            :class="{ 'is-invalid': logs.errors.has('category') }" @change="parseQuestion">
                                        <option v-for="option in dataRaw.categories" :value="option.value">{{ option.name }}
                                        </option>
                                    </select>
                                    <has-error :form="logs" field="category"></has-error>
                                </div>
                            </div>
                            <div class="col-md-6" v-for="(question, q) in dataRaw.questions">
                                <div class="form-group" >
                                    <label>{{ question }}</label>
                                    <textarea class="form-control" v-model="logs[q]" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-sm text-bold text-gray">Ketrampilan</div>
                                <div class="text-sm">
                                    <div class="form-check" v-for="skill in dataRaw.skills">
                                        <input v-model="logs.skills[skill.id]" class="form-check-input" type="checkbox" :id="skill.value">
                                        <label class="form-check-label" :for="skill.value">
                                            {{skill.name}}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
<!--                        <div class="form-group">-->
<!--                            <label>Upload Logbook (gambar/pdf)</label> <br>-->
<!--                            <input type="file" id="form_file" accept="application/pdf,image/png, image/gif, image/jpeg">-->
<!--                        </div>-->
<!--                        <div>-->
<!--                            <img :src="dataRaw.image_url" width="100%">-->
<!--                            <img :src="logs.photo_link" id="data-image" v-show="showThumbnail"-->
<!--                                 width="100%">-->
<!--                        </div>-->
                    </div>
                    <div class="modal-footer">
                        <button type="button" v-if="!edit_mode" :disabled="disable" @click="addSingleLog"
                                class="btn btn-success">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status"
                                  aria-hidden="true"></span>
                            Simpan
                        </button>
                        <button type="button" v-if="edit_mode" :disabled="disable" @click="updateLog"
                                class="btn btn-info">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status"
                                  aria-hidden="true"></span>
                            Perbarui
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="modal-bulk-log" tabindex="-1" role="dialog"
             aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">Tambah Log Book</div>
                    <div class="modal-body table-responsive">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tanggal</label>
                                    <input class="form-control" type="date" v-model="bulk_logs.date">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Supervisor</label>
                                    <select class="form-control" v-model="bulk_logs.lecture_id">
                                        <option value="">Diluar Kardiologi</option>
                                        <option v-for="lecture in dataRaw.lectures" :value="lecture.id">
                                            {{ lecture.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tipe</label>
                                    <select class="form-control" v-model="bulk_logs.type"
                                            :class="{ 'is-invalid': logs.errors.has('type') }" @change="parseQuestion2">
                                        <option v-for="option in dataRaw.options" :value="option.value">
                                            {{ option.name }}
                                        </option>
                                    </select>
                                    <has-error :form="logs" field="type"></has-error>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Kategori</label>
                                    <select class="form-control" v-model="bulk_logs.category"
                                            :class="{ 'is-invalid': logs.errors.has('category') }">
                                        <option v-for="cat in dataRaw.categories" :value="cat.value">
                                            {{ cat.name }}
                                        </option>
                                    </select>
                                    <has-error :form="logs" field="category"></has-error>
                                </div>
                            </div>
                        </div>
                        <table style="width: 100%;" v-if="bulk_logs.type">
                            <tr v-for="num in dataRaw.row_number">
                                <td v-for="(question, q) in dataRaw.bulk_questions">
                                    <textarea class="form-control" :placeholder="question"
                                              v-model="bulk_logs.data[num-1][q]"
                                              rows="2"></textarea>
                                </td>
                                <td>
                                    <div class="text-sm text-bold text-gray">Ketrampilan</div>
                                    <div class="text-sm">
                                        <div class="form-check" v-for="skill in dataRaw.skills">
                                            <input v-model="bulk_logs.data[num-1]['skills'][skill.id]" class="form-check-input" type="checkbox" :id="skill.value + '_' + num">
                                            <label class="form-check-label" :for="skill.value + '_' + num">
                                                {{skill.name}}
                                            </label>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center" style="width: 40px;">
                                    <i class="fa fa-trash text-danger" @click="deleteRow(num)"></i>
                                </td>
                            </tr>
                        </table>
                        <div class="row mt-2" v-if="bulk_logs.type">
                            <div class="col-12 text-center">
                                <button class="btn btn-sm btn-outline-secondary" style="width: 100%" @click="addRow"><i
                                    class="fa fa-plus"></i>Tambah
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" v-if="!edit_mode" :disabled="disable" @click="addLogBulk"
                                class="btn btn-success">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status"
                                  aria-hidden="true"></span>
                            Simpan
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
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
            edit_mode: false,
            disable: false,
            showThumbnail: false,
            loader_stase: false,
            loader_score: false,
            detail_page: null,
            stase_id: null,
            stase: [],
            scores: [],
            logbook: [],
            detailFile: '',
            dataPoints: '',
            dataPointNote: '',
            dataRaw: {
                lectures: [],
                options: [],
                questions: [],
                bulk_questions: [],
                image_url: null,
                photo_link: null,
                row_number: 1,
                categories: [],
                skills: [],
            },
            filter_stase: '',
            logs: new form({
                id: '',
                lecture_id: '',
                stase_id: '',
                stase_log_id: '',
                category: '',
                field_1: '',
                field_2: '',
                field_3: '',
                field_4: '',
                date: '',
                type: '',
                photo: '',
                photo_link: '',
                skills:{}
            }),
            bulk_logs: {
                id: '',
                lecture_id: '',
                stase_id: '',
                stase_log_id: '',
                category: '',
                date: '',
                type: '',
                data: [
                    {
                        field_1: '',
                        field_2: '',
                        field_3: '',
                        field_4: '',
                        skills: {},
                    }
                ]
            }
        }
    },
    methods: {
        loadStase() {
            this.loader_stase = true;
            axios.get('/cmsr/stase-list', {params: {name: this.filter_stase}})
                .then(({data}) => {
                    this.stase = data;
                    this.loader_stase = false;
                }).catch(() => {
                this.loader_stase = false;
            });
        },
        loadLog(id, animate = false) {
            this.stase_id = id;
            this.loader_score = true;
            this.detail_page = 'logbook';
            this.loadOption(id)
            axios.get('/cmsr/student-logs/' + id)
                .then(({data}) => {
                    this.logbook = data.result;
                    this.dataRaw.categories = data.categories;
                    this.loader_score = false;

                    if(animate){
                        $('html, body').animate({
                            scrollTop: $("#right-side").offset().top
                        }, 1000);
                    }

                }).catch(() => {
                this.loader_score = false;
            });
        },
        addSingleLog() {
            this.disable = true;
            const form_file = document.getElementById('form_file');

            const formData = new FormData();
            if (document.getElementById('form_file').value != '') {
                formData.append('file', form_file.files[0]);
            }

            formData.append('lecture_id', this.logs.lecture_id ?? '');
            formData.append('stase_id', this.logs.stase_id ?? '');
            formData.append('stase_log_id', this.logs.stase_log_id ?? '');
            formData.append('field_1', this.logs.field_1 ?? '');
            formData.append('field_2', this.logs.field_2 ?? '');
            formData.append('field_3', this.logs.field_3 ?? '');
            formData.append('date', this.logs.date ?? '');
            formData.append('type', this.logs.type ?? '');

            axios.post('/cmsr/student-logs', formData)
                .then(({data}) => {
                    this.disable = false;
                    $('#modal-log').modal('hide');
                    this.loadLog(this.logs.stase_id)
                    this.popGlobalSuccess({});
                    document.getElementById('form_file').value = "";
                }).catch((e) => {
                this.disable = false;
            });
        },
        modalLog(id, type) {
            this.loadOption(id);
            this.logs.stase_id = id;
            this.bulk_logs.type = type;
            this.bulk_logs.stase_id = id;
            this.edit_mode = false;
            this.dataRaw.row_number = 1;
            this.bulk_logs.data = [
                {
                    field_1: '',
                    field_2: '',
                    field_3: '',
                    field_4: '',
                    skills: {}
                }
            ]
            this.parseQuestion2();
            $('#modal-bulk-log').modal({backdrop: 'static', keyboard: false});
        },
        singleModal(data) {
            this.logs.reset();
            this.edit_mode = false;
            this.showThumbnail = false;
            this.logs.stase_id = data.id;
            axios.get('/stase-option/' + data.id)
                .then(({data}) => {
                    this.dataRaw.options = data.types;
                    this.dataRaw.skills = data.skills;
                    this.parseQuestion();
                });
            $('#modal-log').modal({backdrop: 'static', keyboard: false});
            document.getElementById('form_file').value = "";
        },
        loadOption(id) {
            axios.get('/stase-option/' + id)
                .then(({data}) => {
                    this.dataRaw.options = data.types;
                    this.dataRaw.skills = data.skills;
                });
        },
        loadLectures() {
            axios.get('/get-lectures')
                .then(({data}) => {
                    this.dataRaw.lectures = data;
                })
        },
        parseQuestion() {
            let val = this.logs.type;
            this.dataRaw.options.forEach((option) => {
                if (option.value === val) {
                    this.dataRaw.questions = option.parse_desc;
                }
            });
        },
        addLog() {
            this.disable = true;
            this.logs.post('/cmsr/student-logs')
                .then(({data}) => {
                    if (data.status) {
                        this.disable = false;
                        $('#modal-log').modal('hide');
                        this.loadLog(this.logs.stase_id)
                        this.logs.reset();
                        this.popGlobalSuccess({});
                        this.dataRaw.image_url = null;
                    }
                }).catch(() => {
                this.disable = false;
            })
        },
        deleteLog(id) {
            if (confirm('Hapus?')) {
                axios.delete('/cmsr/student-logs/' + id)
                    .then(({data}) => {
                        if (data.status) {
                            this.popGlobalSuccess({});
                            this.loadLog(this.stase_id);
                        } else {
                            alert('gagal menghapus.')
                        }
                    })
            }
        },
        editLog(parse) {
            this.edit_mode = true;
            this.showThumbnail = true;
            axios.get('/stase-option/' + parse.stase_id)
                .then(({data}) => {
                    this.dataRaw.options = data.types;
                    this.dataRaw.skills = data.skills;
                    this.logs.fill(parse);
                    this.parseQuestion();
                });
            $('#modal-log').modal({backdrop: 'static', keyboard: false});
        },
        updateLog() {
            this.disable = true;
            // const form_file = document.getElementById('form_file');

            const formData = new FormData();
            // if (document.getElementById('form_file').value != '') {
            //     formData.append('file', form_file.files[0]);
            // }

            // formData.append('id', this.logs.id ?? '');
            // formData.append('lecture_id', this.logs.lecture_id ?? '');
            // formData.append('stase_id', this.logs.stase_id);
            // formData.append('stase_log_id', this.logs.stase_log_id ?? '');
            // formData.append('field_1', this.logs.field_1 ?? '');
            // formData.append('field_2', this.logs.field_2 ?? '');
            // formData.append('field_3', this.logs.field_3 ?? '');
            // formData.append('date', this.logs.date ?? '');
            // formData.append('type', this.logs.type ?? '');
            // formData.append('category', this.logs.category ?? '');
            // formData.append('skills', this.logs.skills ?? {});

            axios.post('/cmsr/student-logs/' + this.logs.id, this.logs)
                .then(({data}) => {
                    this.disable = false;
                    $('#modal-log').modal('hide');
                    this.loadLog(this.logs.stase_id)
                    this.popGlobalSuccess({});
                    document.getElementById('form_file').value = "";
                }).catch((e) => {
                this.disable = false;
            });
        },
        showImg(data) {
            this.dataRaw.photo_link = data.photo_link;
            $('#modal-img').modal('show');
        },
        parseQuestion2() {
            let val = this.bulk_logs.type;
            this.dataRaw.options.forEach((option) => {
                if (option.value === val) {
                    this.dataRaw.bulk_questions = option.parse_desc;
                }
            });
        },
        addLogBulk() {
            // this.disable = true;
            console.log(this.bulk_logs)
            axios.post('/cmsr/student-logs-bulk', this.bulk_logs)
                .then(({data}) => {
                    this.disable = false;
                    if (data.status) {
                        $('#modal-bulk-log').modal('hide');
                        this.loadLog(this.logs.stase_id)
                        this.popGlobalSuccess({});
                    }
                }).catch(() => {
                this.disable = false;
            })
        },
        addRow() {
            this.dataRaw.row_number++;
            this.bulk_logs.data.push({
                field_1: '',
                field_2: '',
                field_3: '',
                field_4: '',
                skills: {},
            })
        },
        deleteRow(i) {
            this.bulk_logs.data.splice(i - 1, 1);
            this.dataRaw.row_number--;
        }
    },
    created() {
        this.loadStase();
        this.loadLectures();
    },
}
</script>

<style scoped>
label {
    margin-top: 0.5rem;
}

.score-list {
    text-decoration: underline;
    cursor: pointer;
}

.table-custom td, .table-custom th {
    padding: 5px;
}
</style>
