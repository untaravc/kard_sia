<template>
    <div class="content-wrapper">
        <div class="content">
            <div class="row pt-2">
                <div class="col-md-6">
                    <div class="card mb-1">
                        <div class="card-body">
                            <b>Paket Soal: {{ quiz_sections.length }}</b>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-1 pointer-event"
                         @click="$router.push('/object/quiz-student/' + $route.params.id)">
                        <div class="card-body">
                            <u>Kirim Paket Ujian</u>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card mt-2">
                        <div class="card-header">
                            <h1 class="card-title">Buat & Edit Paket Soal</h1>
                            <div class="card-tools">
                                <button class="btn btn-sm btn-success" @click="addModal()">Tambah Soal</button>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <vue-loader :active.sync="loading" :can-cancel="false" loader="dots"
                                        :is-full-page="false"></vue-loader>
                            <table class="table">
                                <tr>
                                    <th style="width: 50px">No</th>
                                    <th style="width: 50px">Order</th>
                                    <th>Soal</th>
                                    <th>Aksi</th>
                                </tr>
                                <tr v-for="(quiz_section, i) in quiz_sections">
                                    <td>
                                        {{ i + 1 }}
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-secondary" v-if="i > 0"
                                                @click="reorder(quiz_section.order, 'up', i)"
                                                style="padding: 0 0.4rem;">
                                            <i class="fa fa-chevron-up">
                                            </i></button>
                                        <button class="btn btn-sm btn-outline-secondary" v-if="i < total_quiz"
                                                @click="reorder(quiz_section.order, 'down', i)"
                                                style="padding: 0 0.4rem;">
                                            <i class="fa fa-chevron-down">
                                            </i></button>
                                    </td>
                                    <td>
                                        <div v-if="quiz_section.quiz">
                                            <small v-html="quiz_section.quiz.question"></small>
                                            <br>
                                            <i><small>Answer: {{ quiz_section.quiz.answer_str }}</small></i>
                                            <br>
                                            <small> Book : <b>{{ quiz_section.quiz.book }}</b> Section:
                                                <b>{{ quiz_section.quiz.category }}</b></small>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-danger" @click="deleteQuiz(quiz_section.id)">
                                            <i class="fa fa-times-circle"></i>
                                        </button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="addModal" tabindex="-1" role="dialog"
             aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Soal</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="row p-3">
                            <div class="col-md-5">
                                <input class="form-control" placeholder="Cari.."
                                       @keyup.enter="loadQuizzes" v-model="quiz_filter.name">
                            </div>
                            <div class="col-md-5">
                                <select class="form-control" v-model="quiz_filter.category" @change="loadQuizzes">
                                    <option value="">Semua</option>
                                    <option :value="cat" v-for="cat in data_raw.categories">
                                        {{ cat }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-sm btn-th-primary" @click="updateData">
                                    <span v-if="disable" class="spinner-border spinner-border-sm" role="status"
                                          aria-hidden="true"></span>
                                            Simpan
                                </button>
                            </div>
                        </div>
                        <table class="table">
                            <tr>
                                <th>Soal</th>
                                <th style="width: 60px;">Pilih</th>
                            </tr>
                            <tr v-for="(quiz, i) in quizzes">
                                <td>
                                    <span v-html="quiz.question"></span> <br>
                                    <small> Book : <b>{{ quiz.book }}</b> Section: <b>{{ quiz.category }}</b></small>
                                </td>
                                <td style="width: 75px">
                                    <input class="form-check-input largerCheckbox" :checked="quiz.selected"
                                           @change="selectQuiz($event)"
                                           type="checkbox" :value="quiz.id">
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-sm btn-th-primary" @click="updateData">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status"
                                  aria-hidden="true"></span>
                            Simpan
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
            loading: false,
            disable: false,
            quizzes: [],
            selected: [],
            quiz_sections: [],
            data_raw: {
                categories: [],
            },
            quiz_filter: {
                name: '',
                section_id: this.$route.params.id,
                category: '',
            },
        }
    },
    methods: {
        loadSection(loading = true) {
            this.loading = loading;
            axios.get('/objects/sections/' + this.$route.params.id)
                .then(({data}) => {
                    this.loading = false;
                    if (data.status) {
                        this.quiz_sections = data.quizzes;
                        this.selected = [];
                        for (let i = 0; i < this.quiz_sections.length; i++) {
                            this.selected.push(this.quiz_sections[i].quiz_id)
                        }
                    }
                }).catch(() => {
                this.loading = false;
            })
        },
        loadCategories() {
            axios.get('/objects/get-categories')
                .then(({data}) => {
                    this.data_raw.categories = data.result
                })
        },
        addModal() {
            $('#addModal').modal('show');
            this.loadQuizzes();
        },
        loadQuizzes() {
            axios.get('/objects/quiz-list', {params: this.quiz_filter})
                .then(({data}) => {
                    if (data.status) {
                        this.quizzes = data.result;
                    }
                });
        },
        selectQuiz(event) {
            let quiz_id = event.target.value;
            let checked = event.target.checked;
            if (checked) {
                if (this.selected.indexOf(quiz_id) === -1) {
                    this.selected.push(quiz_id);
                }
            } else {
                for (let i = 0; i < this.selected.length; i++) {
                    if (this.selected[i] == quiz_id) {
                        this.selected.splice(i, 1);
                    }
                }
            }
        },
        updateData() {
            axios.post('/objects/sections-quiz/' + this.$route.params.id, {quiz_ids: this.selected})
                .then(({data}) => {
                    if (data.status) {
                        this.loadSection();
                        this.popGlobalSuccess({});
                    }
                })
        },
        reorder(order, type, i) {
            if (type === 'up') {
                this.move(i, i - 1);
            } else {
                this.move(i, i + 1);
            }

            axios.get('/objects/section-quiz-reorder/', {
                params: {
                    section_id: this.$route.params.id,
                    order: order,
                    type: type,
                }
            }).then(() => {
                this.loadSection(false)
            })
        },
        move(from, to) {
            this.quiz_sections.splice(to, 0, this.quiz_sections.splice(from, 1)[0]);
        },
        deleteQuiz(id) {
            if (confirm('Hapus quiz dari daftar?')) {
                axios.get('/objects/section-quiz-delete/', {
                    params: {
                        section_id: this.$route.params.id,
                        id: id,
                    }
                }).then(() => {
                    this.loadSection(true)
                })
            }
        }
    },
    created() {
        this.loadSection();
        this.loadCategories();
    },
    computed: {
        total_quiz() {
            let length = this.quiz_sections.length;
            return length - 1;
        }
    }
}
</script>
<style scoped>
input.largerCheckbox {
    transform: scale(2);
}

.form-check-input {
    width: 20px;
    margin-left: 10px;
    margin-top: 5px;
}

.pointer-event {
    cursor: pointer;
}

.pointer-event:hover {
    /*background-color: #d3d3d3;*/
    box-shadow: 0 0 1px rgb(0 0 0 / 13%), 0 0 0 rgb(0 0 0 / 20%);
}
</style>
