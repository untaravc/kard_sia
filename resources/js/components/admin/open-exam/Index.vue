<template>
    <div class="content-wrapper">
        <div class="content">
            <div class="row">
                <div class="col-12">
                    <div class="card mt-2">
                        <div class="card-header">
                            <h1 class="card-title">Jadwal Ujian yang Dibuka</h1>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <vue-loader :active.sync="is_loading" :can-cancel="false" loader="dots" :is-full-page="false"></vue-loader>
                            <table class="table table-head-fixed">
                                <tr>
                                    <th width="50px">No</th>
                                    <th width="45%">Nama Residen</th>
                                    <th width="">Penilaian</th>
                                    <th><span style="float: right">Aksi</span></th>
                                </tr>
                                <tbody>
                                <tr>
                                    <td></td>
                                    <td>
                                        <input type="text" v-model="filter.keyword"
                                               @keyup.enter="loadDataContent" placeholder="Cari.." class="form-control form-control">
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr role="row" class="odd" v-for="(data, i) in dataContent.data" :key="data.id">
                                    <td><span :title="data.id">{{dataContent.per_page * (dataContent.current_page - 1) + i + 1}} </span></td>
                                    <td>
                                        <b v-if="data.student">{{data.student.name}}</b><br>
                                        <i v-if="data.stase_task">#{{data.stase_task.name}}</i><br>
                                    </td>
                                    <td>
<!--                                        Bila single lecture-->
                                        <small v-if="data.lecture">{{data.lecture.name}} :
                                            <span v-if="data.scores && data.scores[0] && data.scores[0].point_average" class="badge badge-success">{{data.scores[0].point_average}}</span>
                                            <i v-else class="text-black-50">belum menilai</i>
                                        </small>
<!--                                        Multi lectures-->
                                        <span v-if="!data.lecture && data.scores && !data.scores[0]"><i>Belum ada yang menilai</i></span>
                                        <div v-if="!data.lecture && data.scores && data.scores[0]">
                                            <div v-for="(score, s) in data.scores">
                                                <small v-if="score.lecture">{{score.lecture.name}} </small> :
                                                <small class="badge badge-success" v-if="score.point_average"> {{score.point_average}}</small><br>
                                            </div>
                                        </div>
                                        <br>
                                        <small><i>Dibuat : {{data.created_at | formatDateTime}}</i></small>
                                    </td>
                                    <td class="action-button">
                                        <button class="btn btn-sm btn-th-primary" @click="deleteData(data.id)">
                                            Tutup
                                        </button>
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
                                        <pagination :data="dataContent" :limit="3" @pagination-change-page="pagination"></pagination>
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
                base_url: '/sadmin/open-stase-tasks',
                editMode : false,
                is_loading : false,
                dataContent: {},
                dataDetail: '',
                dataRaw: {
                    image_url: ''
                },
                form: new form({
                    id:'',
                    email:'',
                    password:'',
                    name:'',
                    role:'',
                }),
                filter: new form({
                    keyword: '',
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
            pagination(page = 1) {
                this.$Progress.start();
                axios.get(this.base_url+'?page=' + page)
                    .then(response => {
                        this.dataContent = response.data;
                        this.filter.page = response.data.current_page
                    });
                this.$Progress.finish()
            },
            withFilter(){
                this.$Progress.start();
                axios.get(this.base_url, {
                    params: this.filter
                }).then((response) => {
                    this.dataContent = response.data
                    this.$Progress.finish();
                });
            },
            resetData(){
                this.filter.reset();
                this.loadDataContent()
            },
            deleteData(id){
                Swal.fire({
                    title: 'Tutup sesi ujian?',
                    text: "Sesi ujian hanya bisa dibuka kembali oleh residen bersangkutan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya!'
                }).then((result) => {
                    this.$Progress.start();
                    if (result.value) {
                        axios.post(this.base_url+'/' + id, { _method: 'delete'})
                            .then((response) => {
                            this.loadDataContent(this.filter.page);
                            this.popSuccessSwal();
                            this.$Progress.finish();
                        })
                            .catch((errror) => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Unauthorized',
                                text: 'You can not delete this data!',
                            })
                        });
                    }
                })
            },
            popSuccessSwal(){
                Swal.fire({
                    position: 'top-end',
                    icon: 'success',
                    title: 'Success',
                    toast: true,
                    showConfirmButton: false,
                    timer: 1000,
                })
            },
        },
        created(){
            this.$Progress.start();
            this.loadDataContent();

            Fire.$on('ModalSuccess', () => {
                this.loadDataContent();
                this.popSuccessSwal();
                $('#editAddModal').modal('hide');
                this.$Progress.finish();
            })
        },
        mounted() {
            this.$Progress.finish()
        },
    }
</script>
