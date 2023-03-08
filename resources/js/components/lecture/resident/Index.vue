<template>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark"><i class="fa fa-user-graduate"></i> Residen

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
                            <h1 class="card-title">Data Residen</h1>
                            <div class="card-tools">
                                <input type="text" v-model="filter.keyword" class="form-control form-control-sm" id="keyword">
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-head-fixed">
                                <tr>
                                    <th width="50px">No</th>
                                    <th>Nama</th>
                                    <th><span style="float: right">Aksi</span></th>
                                </tr>
                                <tbody>
                                <tr role="row" class="odd" v-for="(data, i) in dataContent.data" :key="data.id">
                                    <td><span :title="data.id">{{dataContent.per_page * (dataContent.current_page - 1) + i + 1}} </span></td>
                                    <td>
                                        {{data.name}}
                                        <small v-if="data.stase_logs_active"><br>#{{data.stase_logs_active.stase.desc}}</small>
                                        <small v-if="data.stase_logs_active"><br>{{data.stase_logs_active.stase.name}}</small>
                                    </td>
                                    <td class="action-button">
                                        <router-link :to="`/dosen/resident/`+data.id" class="btn btn-sm btn-primary">
                                            <i class="fa fa-eye"></i>
                                        </router-link>
                                        <router-link :to="`/dosen/resident-score/`+data.id" class="btn btn-sm btn-warning">
                                            <i class="fa fa-star-half-alt"></i>
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
                                        <pagination :data="dataContent" @pagination-change-page="pagination"></pagination>
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
    import Datepicker from 'vuejs-datepicker';
    export default {
        data(){
            return {
                base_url: '/cmsd/students',
                editMode : false,
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
                    status:'',
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
            loadDataContent(){
                axios.get(this.base_url, {params: this.filter})
                    .then( ({data}) => ( this.dataContent = data))
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
            detailModal(data){
                $('#detailModal').modal('show');
                this.dataDetail = data;
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
        components: {
            Datepicker,
        },
        watch:{
            'filter.keyword':function(){
                this.loadDataContent()
            }
        }
    }
</script>
