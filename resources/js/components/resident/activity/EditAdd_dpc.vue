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
                                               class="form-control" placeholder="dr. Don Joe"
                                               :class="{ 'is-invalid': form.errors.has('speaker') }">
                                        <has-error :form="form" field="speaker"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Judul</label>
                                        <input v-model="form.title" type="text"
                                               placeholder="ex: Perbedaan Derajat Aliran Koroner TIMI 3 Antara.."
                                               class="form-control" :class="{ 'is-invalid': form.errors.has('title') }">
                                        <has-error :form="form" field="title"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Waktu Mulai</label>
                                        <VueCtkDateTimePicker
                                            v-model="form.start_date"
                                            label="Pilih tanggal dan waktu"
                                            format="YYYY-MM-DD HH:mm"
                                            minute-interval="10"
                                            locale="id"/>
                                        <has-error :form="form" field="date"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Selesai</label>
                                        <VueCtkDateTimePicker
                                            v-model="form.end_date"
                                            label="Pilih tanggal dan waktu"
                                            format="YYYY-MM-DD HH:mm"
                                            minute-interval="10"
                                            locale="id"/>
                                        <has-error :form="form" field="date"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Tempat</label>
                                        <input v-model="form.place" type="text"
                                               placeholder="ex : online Webex 0922829, Ruang Konferensi..."
                                               class="form-control" :class="{ 'is-invalid': form.errors.has('place') }">
                                        <has-error :form="form" field="place"></has-error>
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
                                    <label>Template</label>
                                    <span class="badge badge-success" @click="makeTemplate">
                                        <i class="fa fa-bookmark"></i> Generate
                                    </span>
                                    <span class="badge badge-primary" @click="copyTemplate" v-if="this.template">
                                        <i class="fa fa-copy"></i>
                                        <span v-if="!copy">Copy</span>
                                        <span v-if="copy">Copied</span>
                                    </span>
                                    <textarea v-model="template" ref="text"
                                              class="form-control" rows="10"
                                    ></textarea>
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
            base_url: '/cmsr/activities',
            editMode: false,
            disable: false,
            copy: false,
            dataContent: {},
            dataDetail: '',
            addMode: '',
            template: '',
            dataRaw: {
                lectures: [],
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
        lectureModal(mode) {
            this.addMode = mode;
            $('#addLecture').modal('show')
        },
        addParticipant() {
            switch (this.addMode) {
                case 'penguji':
                    this.disable = true;
                    axios.post(
                        '/cmsr/activities/add-participants/' + this.form.id,
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
                        '/cmsr/activities/add-participants/' + this.form.id,
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
                        '/cmsr/activities/add-participants/' + this.form.id,
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
                axios.post('/cmsr/activities/init-activity')
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
                axios.get('/cmsr/activities/' + this.$route.params.id)
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
            axios.patch('/cmsr/activities/' + this.form.id, {
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
            }).then(() => {
                this.disable = false;
                this.$router.push('/resident/activity')
            }).catch(() => {
                alert('Lengkapi data!')
            })
        },
        makeTemplate() {
            this.copy = false;
            this.template = '';
            let start = this.$options.filters.formatDayTime(this.form.start_date);
            let end = this.form.end_date ? '-' + this.$options.filters.formatTime(this.form.end_date) : '';
            let day = this.$options.filters.formatDay(this.form.start_date);
            let title = this.form.title ? '\nJudul: ' + this.form.title + '\n' : '\n';

            let pengampu = '';
            if (this.form.pengampu.length > 0) {
                pengampu += 'Pengampu: \n';

                for (let i = 0; i < this.form.pengampu.length; i++) {
                    pengampu += i + 1 + '. ' + this.form.pengampu[i]['name'] + '\n';
                }
                pengampu += '\n'
            }

            let penguji = '';
            if (this.form.penguji.length > 0) {
                penguji += 'Penguji: \n';

                for (let i = 0; i < this.form.penguji.length; i++) {
                    penguji += i + 1 + '. ' + this.form.penguji[i]['name'] + '\n';
                }
                penguji += '\n'
            }

            let pembimbing = '';
            if (this.form.pembimbing.length > 0) {
                pembimbing += 'Pembimbing: \n';

                for (let i = 0; i < this.form.pembimbing.length; i++) {
                    pembimbing += i + 1 + '. ' + this.form.pembimbing[i]['name'] + '\n';
                }
                pembimbing += '\n'
            }
            this.template = 'Yth Prof/Dr/dr,\n' +
                '\n' +
                'Menyampaikan agenda ilmiah *' + day + '*\n' +
                '*'+ start + end +'* di *'+ this.form.place +'*\n' +

                this.form.note +
                '\n' +
                '*'+ this.form.name +
                '* dengan '+ this.form.speaker +
                title +
                '\n' +
                pengampu + penguji + pembimbing +
                'Terima kasih\n' +
                '-SEKON-'


        },
        selectText(element) {
            var range;
            if (document.selection) {
                // IE
                range = document.body.createTextRange();
                range.moveToElementText(element);
                range.select();
            } else if (window.getSelection) {
                range = document.createRange();
                range.selectNode(element);
                window.getSelection().removeAllRanges();
                window.getSelection().addRange(range);
            }
        },
        copyTemplate(){
            this.selectText(this.$refs.text); // e.g. <div ref="text">
            document.execCommand("copy");
            this.copy = true;
        }
    },
    created() {
        this.loadLectures();
        this.initData();
    },
    updated() {

    },
    mounted() {

    },
}
</script>
