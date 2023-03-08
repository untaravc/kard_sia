<template>
    <div class="content-wrapper">
        <div class="content">
            <div class="row">
                <div class="col-12">
                    <div class="card mt-2">
                        <div class="card-header">
                            <h1 class="card-title">Data Residen</h1>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <vue-loader :active.sync="is_loading" :can-cancel="false" loader="dots" :is-full-page="false"></vue-loader>
                            <table class="table table-head-fixed">
                                <tr>
                                    <th width="70px">No</th>
                                    <th width="45%">Nama</th>
                                    <th >Data</th>
                                    <th width="20%"><span style="float: right">Aksi</span></th>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>
                                        <input type="text" v-model="filter.name" @keyup.enter="loadDataContent" class="form-control" placeholder="Cari nama..">
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tbody>
                                <tr role="row" class="odd" v-for="(data, i) in dataContent.data" :key="data.id">
                                    <td>
                                        <span :title="data.id">{{dataContent.per_page * (dataContent.current_page - 1) + i + 1}} </span>
                                    </td>
                                    <td>
                                        <i v-if="data.status === 'active'" class="fa fa-check-circle text-success"></i>
                                        <i v-if="data.status !== 'active'" class="fa fa-check-circle text-secondary"></i>
                                        <b>{{data.name}}</b> ({{data.year}})<br>
                                        <a :href="`/sadmin/login-student/`+data.id" target="_blank" >
                                            {{data.email}}
                                        </a> <br>
                                        <small v-if="data.stase_logs_active">
                                            <i>{{data.stase_logs_active.stase.desc}}</i> :{{data.stase_logs_active.stase.name}}
                                        </small>
                                    </td>
                                    <td>
                                        <small>Presensi : </small>
                                        <small v-if="data.today_presence && data.today_presence.status === 'on'">{{data.today_presence.created_at | formatTime}}</small>
                                        <small v-if="data.today_presence && data.today_presence.status === 'off'">izin</small>
                                        <small v-if="!data.today_presence"><i>belum absen</i></small> <br>
                                        <small>Jadwal Penilaian : {{data.open_stase_task_count}}</small> <br>
                                        <small>Presensi Agenda : {{data.today_activity_count}}</small>
                                    </td>
                                    <td class="action-button">
                                        <router-link :to="`/dosen/resident-score/`+data.id" class="btn btn-sm btn-wd btn-th-secondary">
                                            Nilai
                                        </router-link>
                                        <router-link :to="`/dosen/resume-presence/`+data.id" class="btn btn-sm btn-wd btn-th-primary">
                                            Presensi
                                        </router-link>
                                    </td>
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
                                        <pagination :data="dataContent" :limit="2" @pagination-change-page="loadDataContent"></pagination>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data(){
            return {
                base_url: '/cmsd/students',
                editMode : false,
                dataContent: {},
                is_loading: false,
                dataDetail: '',
                dataRaw: {
                    image_url: ''
                },
                form: new form({
                    id:'',
                    email:'',
                    password:'',
                    name:'',
                    status:'',
                    year:'',
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
            loadDataContent(page = 1){
                this.filter.page = page;
                this.is_loading = true;
                axios.get(this.base_url, {params: this.filter})
                    .then( ({data}) => {
                        this.dataContent = data;
                        this.is_loading = false;
            })
            },
            resetData(){
                this.filter.reset();
                this.loadDataContent()
            },
        },
        created(){
            this.loadDataContent();
        },
    }
</script>
