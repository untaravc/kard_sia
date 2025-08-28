<template>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark"><i class="fa fa-chalkboard-teacher"></i> Tugas Stase: {{dataDetail.name}}
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="row">
                <div class="col-12">
                    <div class="card" v-if="!dataDetail.stase_logs">
                        <div class="card-body">
                            <p>Belum ada data</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Cari Stase</label>
                                        <input type="text" class="form-control" v-model="filter.keyword" @keyup="withFilter">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card" v-for="(data, i) in dataDetail.stase_logs" :key="i">
                        <div class="card-header">
                            <h1 class="card-title">STASE {{data.stase.name}}</h1>
                            <div class="card-tools">

                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table">
                            <thead>
                            <tr>
                                <th>No</th>
                                <th>Status</th>
                                <th>Tugas</th>
                                <th>Dosen</th>
                                <th>Tanggal</th>
                                <th>Poin total</th>
                                <th>Jumlah diisi</th>
                                <th>Rata-rata</th>
                                <th>Files</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(item, n) in data.stase_task_logs" :key="n">
                                <td>{{n+1}}</td>
                                <td>
                                    <span v-if="item.point_average" class="badge badge-success">selesai</span>
                                    <span v-if="!item.point_average" class="badge badge-secondary">tunda</span>
                                </td>
                                <td>
                                    <span v-if="item.stase_task">{{item.stase_task.name}}</span><br>
                                    <small>{{item.title}}</small>
                                </td>
                                <td><span v-if="item.lecture">{{item.lecture.name}}</span></td>
                                <td><span v-if="item.lecture">{{item.date | formatDateTime}}</span></td>
                                <td>{{item.point_total}}</td>
                                <td>{{item.point_amount}}</td>
                                <td>{{item.point_average}}</td>
                                <td>
                                    <ol class="pl-3" v-if="item.files">
                                        <li v-for="(file, f) in item.files" :key="f">
                                            <span style="text-decoration: underline; cursor:pointer" @click="detailModal(file)">{{file.title}}</span>
                                        </li>
                                    </ol>
                                </td>
                            </tr>
                            </tbody>
                            </table>
                        </div>
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
                                        <a target="_blank" :href="detailFile.link" download>download</a>
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
                base_url: '/cmsd/task-details',
                editMode : false,
                dataDetail: {},
                dataPoints:{},
                detailFile:{},
                dataRaw: {
                    lectures: {},
                },
                form: new form({
                    id:'',
                    lecture_id:'',
                    date:'',
                    point_average:'',
                }),
                score: new form({
                    lecture_id: '',
                    stase_task_id:'',
                }),
                filter: new form({
                    keyword: '',
                    type: '',
                    status: '',
                    start_date: '',
                    end_date: '',
                    page: ''
                })
            }
        },
        methods:{
            loadData(){
                axios.get('/cmsd/student-score/'+this.$route.params.id)
                    .then( ({data}) => {
                        this.dataDetail = data;
                    })
            },
            withFilter(){
                axios.get('/cmsd/student-score/'+this.$route.params.id, {params:this.filter})
                    .then( ({data}) => {
                        this.dataDetail = data;
                    })
            },
            detailModal(data){
                this.detailFile = data;
                $('#detailModal').modal({backdrop: 'static', keyboard: false})
            },
        },
        created(){
            this.$Progress.start();
            this.loadData();

            Fire.$on('ModalSuccess', () => {
                this.loadData();
                this.popSuccessSwal();
                $('#editAddModal').modal('hide');
                this.$Progress.finish();
            })
        },
        mounted() {
            this.$Progress.finish()
        },
        components: {
            Datepicker,
        }
    }
</script>
