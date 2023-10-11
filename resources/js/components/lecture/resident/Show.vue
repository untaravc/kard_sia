<template>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark"><i class="fa fa-user-graduate"></i> Detail: {{dataDetail.name}}
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
                            <h1 class="card-title">STASE</h1>
                            <div class="card-tools">
                                <router-link :to="`/dosen/resident-score/`+$route.params.id" class="btn btn-sm btn-info">
                                    Nilai
                                </router-link>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-head-fixed">
                                <tr>
                                    <th>No</th>
                                    <th>Stase</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Durasi</th>
                                    <th>Catatan</th>
                                    <th>Status</th>
                                </tr>
                                <tbody>
                                <tr role="row" class="odd" v-for="(data, i) in dataDetail.stase_logs" :key="data.id">
                                    <td>{{ i + 1}} </td>
                                    <td>{{data.stase.name}}</td>
                                    <td>{{data.start_date | formatDate}}</td>
                                    <td>{{data.end_date | formatDate}}</td>
                                    <td>{{data.duration}}</td>
                                    <td>{{data.note}}</td>
                                    <td>{{data.status}}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
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
                disable: false,
                dataDetail: {},
                dataRaw: {
                    image_url: '',
                    stases: {},
                },
                form: new form({
                    id:'',
                    student_id: this.$route.params.id,
                    stase_id: '',
                    status:'',
                    start_date:'',
                    end_date:'',
                    note:'',
                }),
                stase:new form({
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
                axios.get('/cmsd/students/'+this.$route.params.id)
                    .then( ({data}) => {
                        this.dataDetail = data;
                    })
            },
            openModal(data){
                $('#openModal').modal({backdrop: 'static', keyboard: false});
                this.form.stase_task_id = data.id;
            },
        },
        created(){
            this.$Progress.start();
            this.loadData();
            Fire.$on('ModalSuccess', () => {
                this.loadData();
                this.popSuccessSwal();
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
