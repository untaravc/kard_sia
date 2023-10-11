<template>
    <div class="content-wrapper">
        <div class="content mt-3">
            <div class="row">
                <div class="col-12">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card card-primary card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">Stase</h3>
                                </div>
                                <div class="card-body p-0">
                                    <div class="list-group">
                                        <div class="list-group-item">
                                            <input type="text" v-model="filter_stase"
                                                   v-debounce:600ms="loadStase"
                                                   class="form-control" placeholder="Cari stase..">
                                        </div>
                                        <div v-for="st in stase" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                             :style="st.stase && stase_id === st.stase.id ? 'background-color: #3a8ce56b' :''">
                                            <b v-if="st.stase">{{st.stase.name}}</b>
                                            <button class="btn btn-sm btn-light" @click="loadScore(st.stase.id)">
                                                Nilai <i class="fa fa-arrow-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.card-body -->
                            </div>
                        </div>
                        <!-- /.col -->
                        <div class="col-md-8" id="right-side">
                            <div class="card card-primary card-outline" v-if="detail_page === 'score'">
                                <div class="card-header">
                                    <h3 class="card-title"><b>Penilaian </b>

                                    </h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body p-0">
                                    <vue-loader :active.sync="loader_score" :can-cancel="false" loader="dots" :is-full-page="false"></vue-loader>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <tbody>
                                            <tr v-for="score in scores">
                                                <td>
                                                    <b v-if="score.stase_task">{{score.stase_task.name}}</b> <br>
                                                    <small>{{score.title}}</small>
                                                    <div v-if="score.scores">
                                                        <small v-for="score in score.scores" :key="score.id"
                                                               v-if="score.lecture" @click="detailModal(score)">
                                                            <i class="fa fa-marker text-primary"></i>
                                                            <span class="score-list">{{ score.lecture.name }}</span>
                                                            <span class="badge-success badge" style="font-size: smaller" v-if="score.point_average > 0">
                                                                {{score.point_average}}
                                                            </span>
                                                            <i class="text-black-50" v-if="score.point_average > 0">on: {{score.date | formatDate}}</i>
                                                            <br>
                                                        </small>
                                                    </div>
                                                    <div v-if="score.status === 'pending'">
                                                        <small><i class="text-black-50">Belum ada data penilaian.</i></small>
                                                    </div>
                                                </td>
                                                <td class="text-right">
<!--                                                    <button v-if="data.files && data.files[0]" class="btn btn-wd btn-sm btn-th-dark"-->
<!--                                                            @click="fileModal(data)" ><i class="fa fa-paperclip"></i> File</button>-->
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <!-- /.table -->
                                    </div>
                                    <!-- /.mail-box-messages -->
                                </div>
                                <!-- /.card-body -->
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="pointModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-body table-responsive p-0">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Poin Penilaian</th>
                                <th>Nilai</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(data,d) in dataPoints" :key="d">
                                <td><span v-if="data.task_detail">{{data.task_detail.name}}</span></td>
                                <td>{{data.score}}</td>
                            </tr>
                            </tbody>
                        </table>
                        <div class="px-3">
                            <label>Catatan</label>
                            <p>{{dataPointNote}}</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Uploaded File</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <table>
                                <tbody>
                                <tr>
                                    <td>Judul</td>
                                    <td>:</td>
                                    <td>{{detailFile.title}}</td>
                                </tr>
                                <tr>
                                    <td>Keterangan</td>
                                    <td>:</td>
                                    <td>{{ detailFile.desc }}</td>
                                </tr>
                                <tr v-if="detailFile.link">
                                    <td>File</td>
                                    <td>:</td>
                                    <td>
                                        <a target="_blank" :href="`/storage/`+detailFile.link" download>download</a>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Datepicker from 'vuejs-datepicker';
    export default {
        data(){
            return {
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
                dataRaw:{
                    lectures: [],
                    options: [],
                    questions: [],
                },
                filter_stase: '',
            }
        },
        methods:{
            loadStase(){
                this.loader_stase = true;
                axios.get('/cmsr/stase-list', {params: {name: this.filter_stase}})
                .then(({data})=>{
                    this.stase = data;
                    this.loader_stase = false;
                }).catch(()=>{
                    this.loader_stase = false;
                });
            },
            loadScore(id){
                this.detail_page = 'score';
                this.stase_id = id;
                this.loader_score = true;
                axios.get('/cmsr/stase-score/' + id)
                    .then(({data})=>{
                        this.scores = data;
                        this.loader_score = false;

                        $('html, body').animate({
                            scrollTop: $("#right-side").offset().top
                        }, 1000);
                    }).catch(()=>{
                    this.loader_score = false;
                });
            },
            loadOption(id){
                axios.get('/stase-option/' + id)
                    .then(({data}) => {
                        this.dataRaw.options = data;
                    });
            },
            loadLectures() {
                axios.get('/get-lectures')
                    .then(({data}) => {
                        this.dataRaw.lectures = data;
                    })
            },
            detailModal(data){
                if(data.admin === 0){
                    this.dataPoints = data.stase_task_log_point;
                    this.dataPointNote = data.note;
                    $('#pointModal').modal('show');
                }
            },
            fileModal(data){
                this.detailFile = data;
                $('#detailModal').modal({backdrop: 'static', keyboard: false})
            },
        },
        created(){
            this.loadStase();
            this.loadLectures();
        },
    }
</script>

<style scoped>
label{
    margin-top: 0.5rem;
}

.score-list{
    text-decoration: underline;
    cursor: pointer;
}

.table-custom td, .table-custom th{
    padding: 5px;
}
</style>
