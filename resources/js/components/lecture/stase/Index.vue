<template>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark"><i class="fa fa-chalkboard-teacher"></i> Stase
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h1 class="card-title">Stase</h1>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4 col-md-6 col-12" v-for="(data, d) in dataDashboard" :key="d">
                                    <div v-if="data.desc === 'Tahap 1'" class="position-relative kotak border border-success p-3 mb-3" style="height: 150px">
                                        <div class="ribbon-wrapper ribbon-md">
                                            <div class="ribbon bg-secondary">
                                                Tahap 1
                                            </div>
                                        </div>
                                        <p style="height: 50px" class="mb-0">{{data.name}}</p>
                                        <small class="pb-1" v-for="(item, i) in data.stase_log_ongoing" :key="i">{{item.student.name}}<br></small>
                                    </div>
                                    <div v-if="data.desc === 'Tahap 2'" class="position-relative kotak border border-info p-3 mb-3" style="height: 150px">
                                        <div class="ribbon-wrapper ribbon-md">
                                            <div class="ribbon bg-info">
                                                Tahap 2
                                            </div>
                                        </div>
                                        <p style="height: 50px" class="mb-0">{{data.name}}</p>
                                        <small v-for="(item, i) in data.stase_log_ongoing" :key="i">{{item.student.name}}<br></small>
                                    </div>
                                    <div v-if="data.desc === 'Tahap 3'" class="position-relative kotak border border-danger p-3 mb-3" style="height: 150px">
                                        <div class="ribbon-wrapper ribbon-md">
                                            <div class="ribbon bg-danger">
                                                Tahap 3
                                            </div>
                                        </div>
                                        <p style="height: 50px" class="mb-0">{{data.name}}</p>
                                        <small v-for="(item, i) in data.stase_log_ongoing" :key="i">{{item.student.name}}<br></small>
                                    </div>
                                    <div v-if="data.desc === 'Referat'" class="position-relative kotak border border-warning p-3 mb-3" style="height: 150px">
                                        <div class="ribbon-wrapper ribbon-md">
                                            <div class="ribbon bg-warning">
                                                Referat
                                            </div>
                                        </div>
                                        <p style="height: 50px" class="mb-0">{{data.name}}</p>
                                        <small v-for="(item, i) in data.stase_log_ongoing" :key="i">{{item.student.name}}<br></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="openModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Buka Lembar Penilaian</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label>Dosen Penguji</label>
                                <select v-model="form.lecture_id" class="form-control" :class="{ 'is-invalid': form.errors.has('lecture_id') }">
                                    <option value="" selected>Banyak Dosen</option>
                                    <option v-for="(data, i) in dataRaw.lectures" :key="i" :value="data.id">{{data.name}}</option>
                                </select>
                                <has-error :form="form" field="lecture_id"></has-error>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
                dataContent: {},
                dataDashboard:{},
                detailFile:{},
                dataRaw: {
                    image_url: '',
                    lectures:[],
                    stases: {},
                },
                form: new form({
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
                axios.get('/cmsd/get-open-stase-task')
                    .then( ({data}) => {
                        this.dataContent = data;
                    })
            },
            loadDashboard(){
                axios.get('/cmsd/dashboard-stase')
                    .then((response)=>{
                        this.dataDashboard = response.data
                    })
            },
            detailModal(data){
                this.detailFile = data;
                $('#detailModal').modal({backdrop: 'static', keyboard: false})
            }
        },
        created(){
            this.$Progress.start();
            this.loadData();
            this.loadDashboard();

            Fire.$on('ModalSuccess', () => {
                this.loadData();
                this.popSuccessSwal();
                $('#openModal').modal('hide');
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
<style>
    .kotak{
        overflow-y: auto;
        overflow-x: hidden;
    }
</style>
