<template>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark"><i class="fa fa-calendar-alt"></i> Aktivitas
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
                            <h1 class="card-title">Data Aktivitas</h1>
                            <div class="card-tools">
                                <router-link to="/resident/activity/edit-add/create" class="btn btn-sm btn-success">Tambah</router-link>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-head-fixed">
                                <tr>
                                    <th width="50px" class="d-none d-lg-table-cell">No</th>
                                    <th width="30%" style="min-width: 200px">Nama</th>
                                    <th width="40%" style="min-width: 250px">Keterangan</th>
                                    <th style="min-width: 200px"><span style="float: right">Aksi</span></th>
                                </tr>
                                <tbody>
                                <tr>
                                    <td class="d-none d-lg-table-cell"></td>
                                    <td>
                                        <input type="text" placeholder="Cari.." v-model="filter.keyword" class="form-control form-control-sm">
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr role="row" class="odd" v-for="(data, i) in dataContent.data" :key="data.id">
                                    <td width="50px" class="d-none d-lg-table-cell">
                                        <span :title="data.id">{{dataContent.per_page * (dataContent.current_page - 1) + i + 1}} </span></td>
                                    <td>
                                        <i class="fa fa-bookmark text-th"></i>
                                        <b>{{data.name}}</b><br>
                                        <small v-if="data.speaker"><b>{{data.speaker}}:</b></small>
                                        <small>{{data.title | truncate(40)}}</small>
                                    </td>
                                    <td>
                                        <i class="fa fa-calendar-check" :class="data.status === 'active' ? 'text-success' : 'text-secondary'"></i> <b>{{data.start_date | formatDayTime}}</b> <br>
                                        <small style="color: #0a58ca">https://sia.kardiologi-fkkmk.com/presensi/{{ data.id }} </small>
                                        <br>
                                        <small v-if="data.creator">Kelola: {{data.creator.name}}</small>
                                    </td>
                                    <td class="text-right">
                                        <router-link :to="'/resident/activity/edit-add/' + data.id" v-if="data.is_author"
                                                     class="btn mt-1 btn-sm btn-wd btn-th-dark" @click="editModal(data)">
                                            <i class="fa fa-edit" ></i> Kelola
                                        </router-link>
                                        <button class="btn btn-sm mt-1 btn-wd btn-primary" @click="detailModal(data)">
                                            <i class="fa fa-chalkboard"></i> Laporan
                                        </button>
                                        <br>
                                        <small v-if="data.attended">
                                            <i class="fa fa-check-circle"></i>
                                            Presensi pada {{data.attended.created_at | formatTime }}</small>
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
                                        <pagination :limit="2" :data="dataContent" @pagination-change-page="pagination"></pagination>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade bd-example-modal-lg" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 text-center mb-2">
                                <h5 class="mb-0">{{dataDetail.name}}</h5>
                                <span>{{dataDetail.start_date | formatDateTime}}</span>
                                <span v-if="dataDetail.end_date">- {{dataDetail.end_date | formatTime}}</span>
                                <br>
                                <small><b>{{dataDetail.title}}</b></small> <br>
                                <i>{{dataDetail.speaker}}</i>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-primary card-outline">
                                    <div class="card-body">
                                        <h6>Presensi Dosen</h6>
                                        <div v-if="dataDetail.activity_lectures">
                                            <div style="font-size: smaller" :style="lect.created_at > dataDetail.end_date ? 'color: red' : ''"
                                                 v-for="(lect, l) in dataDetail.activity_lectures" :key="l">
                                                <span v-if="lect.lecture">{{l + 1}}. {{lect.lecture.name}}</span>
                                                <i class="text-black-50"> on {{lect.created_at | formatTime}}</i>
                                                <span class="text-black-50" v-if="lect.note"> : {{lect.note}}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-info card-outline">
                                    <div class="card-body">
                                        <h6>Presensi Residen</h6>
                                        <div v-if="dataDetail.activity_students">
                                            <div style="font-size: smaller" :style="stud.created_at > dataDetail.end_date ? 'color: red' : ''" v-for="(stud, s) in dataDetail.activity_students" :key="s">
                                                <span style="width: 10px">{{s + 1}}. </span><span v-if="stud.student"> {{stud.student.name}}</span>
                                                <i class="text-black-50"> on {{stud.created_at | formatTime}}</i>
                                                <span class="text-black-50" v-if="stud.note"> : {{stud.note}}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
    import { VueEditor } from "vue2-editor";
    export default {
        components: { VueEditor},
        data(){
            return {
                base_url: '/cmsr/activities',
                editMode : false,
                disable : false,
                dataContent: {},
                dataDetail: '',
                dataRaw: {
                    image_url: ''
                },
                form: new form({
                    id:'',
                    name:'',
                    title:'',
                    place:'',
                    desc:'',
                    note:'',
                    start_date:'',
                    end_date:'',
                }),
                filter: new form({
                    keyword: '',
                })
            }
        },
        methods:{
            loadDataContent(){
                axios.get(this.base_url, {
                    params: this.filter
                })
                    .then( ({data}) => ( this.dataContent = data))
            },
            pagination(page = 1) {
                this.$Progress.start();
                this.filter.page = page;
                axios.get(this.base_url, {
                    params: this.filter
                })
                    .then(response => {
                        this.dataContent = response.data;
                    });
                this.$Progress.finish()
            },
            resetData(){
                this.filter.reset();
                this.loadDataContent()
            },
            detailModal(act){
                this.dataDetail = '';
                axios.get('/cmsr/activities/' + act.id)
                    .then(({data})=>{
                        this.dataDetail = data;
                    })
                $('#detailModal').modal('show');
            },
            deleteData(id){
                Swal.fire({
                    title: 'Are you sure to delete this data?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    this.$Progress.start();
                    if (result.value) {
                        axios.post(this.base_url+'/' + id, {_method: 'delete'}).then((response) => {
                            this.loadDataContent();
                            Swal.fire(
                                'Deleted!',
                                'Data has been deleted.',
                                'success'
                            );
                            this.$Progress.finish();
                        }).catch((error) => {
                            alert(error);
                            this.$Progress.finish();
                        });
                    }
                })
            },
            popSuccessSwal(){
                Swal.fire({
                    position: 'top-end',
                    icon: 'success',
                    title: 'Success',
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
        watch:{
            'filter.keyword': function (val){
                this.loadDataContent();
            }
        }
    }
</script>
