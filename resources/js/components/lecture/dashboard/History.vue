<template>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark"><i class="fa fa-chalkboard-teacher"></i> Riwayat Penilaian
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="card" >
                        <div class="card-body p-0 table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th width="50px">No</th>
                                    <th>Ujian</th>
                                    <th width="180px">Nilai</th>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>
                                        <input type="text" placeholder="Cari nama" class="form-control" v-model="filter.name"
                                            @keyup.enter="loadData">
                                    </td>
                                    <td></td>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-if="dataContent && dataContent.data && dataContent.data[0]" v-for="(data, i) in dataContent.data" :key="i">
                                    <td>
                                        <span :title="data.id">{{dataContent.per_page * (dataContent.current_page - 1) + i + 1}} </span>
                                    </td>
                                    <td>
                                        <b v-if="data.student">{{data.student.name}}</b><br>
                                        <span>Stase {{data.stase.name}}</span><br>
                                        <small v-if="data.stase_task"><b>{{data.stase_task.name}}</b> {{data.title}}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="score">{{data.point_average}}</span>
                                        <br>
                                        <small>{{data.updated_at | formatDateTime}}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4" v-if="dataContent && dataContent.data && !                                      dataContent.data[0]"><i>Tidak ada riwayat penilaian ujian</i></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-sm-12 col-md-5">
                                    <div class="dataTables_info" role="status" aria-live="polite">
                                        Menampilkan data {{dataContent.from}} hingga {{dataContent.to}} dari {{dataContent.total}} data.
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-7">
                                    <div class="dataTables_paginate paging_simple_numbers" style="float: right">
                                        <pagination :data="dataContent" :limit="2" @pagination-change-page="loadData"></pagination>
                                    </div>
                                </div>
                            </div>
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
                base_url: '/cmsr/stases',
                editMode : false,
                is_loading : false,
                dataContent: {},
                detailFile:{},
                dataRaw: {
                    image_url: '',
                    lectures:[],
                    stases: {},
                },
                filter: new form({
                    name: '',
                    page: ''
                }),
            }
        },
        methods:{
            loadData(page = 1){
                this.filter.page = page
                axios.get('/cmsd/history', {params: this.filter})
                    .then( ({data}) => {
                        this.dataContent = data;
                    })
            },
            loadSchedule(){
                axios.get('/cmsd/get-schedule')
                .then((response)=>{
                    this.schedules = response.data
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
                this.loadSchedule();
                this.popGlobalSuccess({});
                $('#openModal').modal('hide');
                $('#scheduleModal').modal('hide');
                this.$Progress.finish();
            })
        },
    }
</script>

<style>
    .score{
        color: green;
        font-size: 26px;
        font-weight: 700;
    }
</style>
