<template>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark"><i class="fa fa-calendar"></i> Tambah Agenda
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nama</label>
                                        <input v-model="form.name" type="text" placeholder="ex: Seminar Hasil"
                                               class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                                        <has-error :form="form" field="name"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Pembicara</label>
                                        <input v-model="form.speaker" type="text"
                                               class="form-control" placeholder="Nama dipisahkan titik koma ';' bila banyak"
                                               :class="{ 'is-invalid': form.errors.has('speaker') }">
                                        <has-error :form="form" field="speaker"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Judul</label>
                                        <textarea v-model="form.title" type="text"
                                               placeholder="ex: Perbedaan Derajat Aliran Koroner TIMI 3 Antara.."
                                                  class="form-control" :class="{ 'is-invalid': form.errors.has('title') }">
                                        </textarea>
                                        <has-error :form="form" field="title"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Stase</label>
                                        <select class="form-control" v-model="form.stase_id">
                                            <option :value="stase.id" v-for="stase in dataRaw.stase">{{stase.name}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Mulai</label>
                                        <VueCtkDateTimePicker
                                            :no-label="true"
                                            id="waktu_mulai"
                                            v-model="form.start_date"
                                            format="YYYY-MM-DD HH:mm"
                                            minute-interval="5"
                                            locale="id"/>
                                        <has-error :form="form" field="start_date"></has-error>
                                    </div>
                                    <div class="form-group">
                                        <label>Selesai</label>
                                        <VueCtkDateTimePicker
                                            :no-label="true"
                                            id="waktu_selesai"
                                            v-model="form.end_date"
                                            format="YYYY-MM-DD HH:mm"
                                            minute-interval="5"
                                            locale="id"/>
                                        <has-error :form="form" field="end_date"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tipe</label>
                                        <div class="form-check">
                                            <input class="form-check-input" v-model="form.type" value="0" type="radio" name="type_form" id="type_0">
                                            <label class="form-check-label" for="type_0">
                                                Umum <i class="text-black-50">residen dan atau staff</i>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" v-model="form.type" value="3" type="radio" name="type_form" id="type_3">
                                            <label class="form-check-label" for="type_3">
                                                Staff <i class="text-black-50">hanya staff</i>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" v-model="form.type" value="1" type="radio" name="type_form" id="type_1">
                                            <label class="form-check-label" for="type_1">
                                                Residen <i class="text-black-50">hanya residen</i>
                                            </label>
                                        </div>
                                        <div class="form-check" :class="{ 'is-invalid': form.errors.has('type') }">
                                            <input class="form-check-input" v-model="form.type" value="2" type="radio" name="type_form" id="type_2">
                                            <label class="form-check-label" for="type_2">
                                                Residen Wajib <i class="text-black-50">(semua/sebagian besar residen harus mengikuti)</i>
                                            </label>
                                        </div>
                                        <has-error :form="form" field="type"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tempat</label>
                                        <input v-model="form.place" type="text"
                                               placeholder="ex : online Webex 0922829, Ruang Konferensi..."
                                               class="form-control" :class="{ 'is-invalid': form.errors.has('place') }">
                                        <has-error :form="form" field="place"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Kategori</label>
                                        <select v-model="form.category"
                                                class="form-control" :class="{ 'is-invalid': form.errors.has('category') }">
                                            <option v-for="cat in dataRaw.categories" :value="cat.id">{{cat.name}}</option>
                                        </select>
                                        <has-error :form="form" field="category"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Penguji
                                            <span class="badge badge-success" @click="lectureModal('penguji')"><i
                                                class="fa fa-user-edit"></i>Kelola</span>
                                        </label>
                                        <textarea disabled v-if="!form.penguji[0]"
                                                  class="form-control"></textarea>
                                        <ul style="font-size: smaller" class="pl-3">
                                            <li v-for="penguji in form.penguji">{{ penguji.name }}</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Pembimbing
                                            <span class="badge badge-success" @click="lectureModal('pembimbing')"><i
                                                class="fa fa-user-edit"></i>Kelola</span>
                                        </label>
                                        <textarea disabled v-if="!form.pembimbing[0]" class="form-control"></textarea>
                                        <ul style="font-size: smaller" class="pl-3">
                                            <li v-for="pembimbing in form.pembimbing">{{ pembimbing.name }}</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Pengampu
                                            <span class="badge badge-success" @click="lectureModal('pengampu')"><i
                                                class="fa fa-user-edit"></i>Kelola</span>
                                        </label>
                                        <textarea disabled v-if="!form.pengampu[0]"
                                                  class="form-control"></textarea>
                                        <ul style="font-size: smaller" class="pl-3">
                                            <li v-for="pengampu in form.pengampu">{{ pengampu.name }}</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label>Catatan</label>
                                    <textarea v-model="form.note"
                                              class="form-control"
                                              :class="{ 'is-invalid': form.errors.has('note') }"></textarea>
                                    <has-error :form="form" field="note"></has-error>
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="btn btn-success" style="width: 100%" @click="saveData">Simpan</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade bd-example-modal-lg" id="addLecture" tabindex="-1" role="dialog"
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
                        <label>Dosen {{ addMode }}</label>
                        <div class="form-check" v-if="addMode === 'penguji'" v-for="data in dataRaw.lectures"
                             :key="data.id">
                            <input class="form-check-input" :id="'dosen'+data.id" type="checkbox"
                                   v-model="form.lecture_penguji[data.id]">
                            <label class="form-check-label" :for="'dosen'+data.id">{{ data.name }}</label>
                        </div>
                        <div class="form-check" v-if="addMode === 'pengampu'" v-for="data in dataRaw.lectures"
                             :key="data.id">
                            <input class="form-check-input" :id="'dosen'+data.id" type="checkbox"
                                   v-model="form.lecture_pengampu[data.id]">
                            <label class="form-check-label" :for="'dosen'+data.id">{{ data.name }}</label>
                        </div>
                        <div class="form-check" v-if="addMode === 'pembimbing'" v-for="data in dataRaw.lectures"
                             :key="data.id">
                            <input class="form-check-input" :id="'dosen'+data.id" type="checkbox"
                                   v-model="form.lecture_pembimbing[data.id]">
                            <label class="form-check-label" :for="'dosen'+data.id">{{ data.name }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button :disabled="disable" v-show="!editMode" type="submit" class="btn btn-sm btn-primary"
                                @click="addParticipant">
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
import {VueEditor} from "vue2-editor";

export default {
    components: {VueEditor},
    data() {
        return {
            base_url: '/sadmin/activities',
            editMode: false,
            disable: false,
            copy: false,
            dataContent: {},
            dataDetail: '',
            addMode: '',
            template: '',
            dataRaw: {
                lectures: [],
                categories: [],
                stase: [],
            },
            form: new form({
                id: null,
                name: null,
                title: null,
                place: null,
                desc: null,
                note: null,
                speaker: null,
                link: null,
                status: null,
                start_date: null,
                end_date: null,
                type: null,
                category: null,
                stase_id: null,
                lecture_penguji: [],
                lecture_pengampu: [],
                lecture_pembimbing: [],
                penguji: [],
                pengampu: [],
                pembimbing: [],
            }),
            filter: new form({
                keyword: '',
            })
        }
    },
    methods: {
        loadLectures() {
            axios.get('/get-lectures')
                .then(({data}) => {
                    this.dataRaw.lectures = data;
                })
        },
        loadCats() {
            axios.get('/get-activity-cats')
                .then(({data}) => {
                    this.dataRaw.categories = data;
                })
        },
        loadStase(){
            axios.get('/get-stase')
                .then(({data}) => {
                    this.dataRaw.stase = data.result;
                })
        },
        lectureModal(mode) {
            this.addMode = mode;
            $('#addLecture').modal('show')
        },
        addParticipant() {
            switch (this.addMode) {
                case 'penguji':
                    this.disable = true;
                    axios.post(
                        '/sadmin/activities/add-participants/' + this.form.id,
                        {
                            ids: this.form.lecture_penguji,
                            type: 'lecture_penguji',
                        })
                        .then(({data}) => {
                            this.form.lecture_penguji = this.arrayParse(data.ids);
                            this.form.penguji = data.list;
                            $('#addLecture').modal('hide');
                            this.disable = false;
                        })
                    break;
                case 'pembimbing':
                    this.disable = true;
                    axios.post(
                        '/sadmin/activities/add-participants/' + this.form.id,
                        {
                            ids: this.form.lecture_pembimbing,
                            type: 'lecture_pembimbing',
                        })
                        .then(({data}) => {
                            this.form.lecture_pembimbing = this.arrayParse(data.ids);
                            this.form.pembimbing = data.list;
                            $('#addLecture').modal('hide');
                            this.disable = false;
                        })
                    break;
                case 'pengampu':
                    this.disable = true;
                    axios.post(
                        '/sadmin/activities/add-participants/' + this.form.id,
                        {
                            ids: this.form.lecture_pengampu,
                            type: 'lecture_pengampu',
                        })
                        .then(({data}) => {
                            this.form.lecture_pengampu = this.arrayParse(data.ids);
                            this.form.pengampu = data.list;
                            $('#addLecture').modal('hide');
                            this.disable = false;
                        })
                    break;
            }
        },
        initData() {
            if (this.$route.params.id === 'create' && this.form.id == null) {
                axios.post('/sadmin/activities/init-activity')
                    .then(({data}) => {
                        this.form.fill(data);
                        if (data.lecture_penguji.length > 1) {
                            this.form.lecture_penguji = this.arrayParse(JSON.parse(data.lecture_penguji));
                        }
                        if (data.lecture_pembimbing.length > 1) {
                            this.form.lecture_pembimbing = this.arrayParse(JSON.parse(data.lecture_pembimbing));
                        }
                        if (data.lecture_pengampu.length > 1) {
                            this.form.lecture_pengampu = this.arrayParse(JSON.parse(data.lecture_pengampu));
                        }
                    })
            } else {
                axios.get('/sadmin/activities/' + this.$route.params.id)
                    .then(({data}) => {
                        this.form.fill(data);
                        if (data.lecture_penguji.length > 1) {
                            this.form.lecture_penguji = this.arrayParse(JSON.parse(data.lecture_penguji));
                        }
                        if (data.lecture_pembimbing.length > 1) {
                            this.form.lecture_pembimbing = this.arrayParse(JSON.parse(data.lecture_pembimbing));
                        }
                        if (data.lecture_pengampu.length > 1) {
                            this.form.lecture_pengampu = this.arrayParse(JSON.parse(data.lecture_pengampu));
                        }
                    })
            }
        },
        arrayParse(data) {
            let max = Math.max.apply(null, data);
            let box = [];
            for (let i = 0; i <= max; i++) {
                box[i] = data.includes(i)
            }
            return box;
        },
        saveData() {
            this.disable = true;
            axios.patch('/sadmin/activities/' + this.form.id, {
                name: this.form.name,
                title: this.form.title,
                place: this.form.place,
                desc: this.form.desc,
                speaker: this.form.speaker,
                note: this.form.note,
                link: this.form.link,
                status: this.form.status,
                start_date: this.form.start_date,
                end_date: this.form.end_date,
                type: this.form.type,
                category: this.form.category,
                stase_id: this.form.stase_id,
            }).then(() => {
                this.disable = false;
                this.$router.push('/cmss/activity')
            }).catch(() => {
                alert('Lengkapi data!')
            })
        },
    },
    created() {
        this.loadLectures();
        this.loadCats();
        this.initData();
        this.loadStase();
    },
    updated() {

    },
    mounted() {

    },
}
</script>
<style>
.form-check label{
    margin-top: 0;
}
</style>
