<template>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-6">
                        <h1 class="m-0 text-dark"><i class="fa fa-calendar-alt"></i> Agenda</h1>
                    </div>
                    <div class="col-6 text-right">
                        <router-link to="/cmss/activity/edit-add/create" class="btn btn-sm btn-th-dark">
                            <i class="fa fa-plus"></i> Tambah
                        </router-link>
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
                        </div>
                        <div class="card-body table-responsive p-0">
                            <vue-loader :active.sync="is_loading" :can-cancel="false" loader="dots" :is-full-page="false"></vue-loader>
                            <table class="table table-head-fixed">
                                <tr>
                                    <th width="50px" class="d-none d-md-table-cell">No</th>
                                    <th>Nama</th>
                                    <th width="30%">Keterangan</th>
                                    <th width="150px">Kehadiran</th>
                                    <th width="120px"><span style="float: right">Aksi</span></th>
                                </tr>
                                <tbody>
                                <tr>
                                    <td class="d-none d-md-table-cell"></td>
                                    <td>
                                        <input type="text" v-model="filter.keyword" placeholder="Cari judul/speaker"
                                               @keyup.enter="loadDataContent" class="form-control mb-2 mr-sm-2">
                                    </td>
                                    <td>
                                        <select class="form-control" v-model="filter.lecture_id" @change="loadDataContent">
                                            <option value="">Pilih Dosen</option>
                                            <option :value="lecture.id" v-for="lecture in dataRaw.lectures">
                                                {{lecture.name}}
                                            </option>
                                        </select>
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr role="row" class="odd" v-for="(data, i) in dataContent.data" :key="data.id">
                                    <td class="d-none d-md-table-cell"><span :title="data.id">{{dataContent.per_page * (dataContent.current_page - 1) + i + 1}} </span></td>
                                    <td>
                                        <i class="fa fa-bookmark text-th"></i>
                                        <b>{{data.name}}</b><br>
                                        <small v-if="data.speaker"><b>{{data.speaker}}:</b></small>
                                        <small class="d-none d-md-table-cell">{{data.title | truncate(40)}}</small>
                                    </td>
                                    <td>
                                        <i class="fa fa-calendar-check" :class="data.status === 'active' ? 'text-success' : 'text-default'"></i> <b>{{data.start_date | formatDayTime}}</b>
                                        <br>
                                        <small style="color: #0a58ca">https://sia.kardiologi-fkkmk.com/presensi/{{ data.id }} </small>
                                        <br>
                                        <small v-if="data.creator">ts: {{data.creator.name}}</small> <br>
                                        <a v-if="data.category === 7 || data.category === 8"
                                           class="btn btn-outline-info btn-sm" target="_blank"
                                           :href="'/sadmin/pdf/sk-referat-lapsus/' + data.id">
                                            <i class="fa fa-download"></i> Surat Keterangan
                                        </a>
                                    </td>
                                    <td>
                                        <small>Dosen : {{data.activity_lectures_count}}</small> <br>
                                        <small>Residen : {{data.activity_students_count}}</small>
                                    </td>
                                    <td class="text-right">
                                        <button class="btn btn-sm mt-1 btn-wd btn-primary" @click="detailModal(data)">
                                            <i class="fa fa-chalkboard"></i> Laporan
                                        </button>
                                        <router-link :to="'/cmss/activity/edit-add/' + data.id" class="btn mt-1 btn-wd btn-sm btn-th-dark">
                                            <i class="fa fa-edit"></i> Ubah
                                        </router-link>
                                        <button class="btn btn-sm mt-1 btn-danger d-none btn-wd d-lg-inline-block" @click="deleteData(data.id)">
                                            <i class="fa fa-trash"></i> Hapus
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
                                        <pagination :data="dataContent" :limit="2" @pagination-change-page="loadDataContent"></pagination>
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
                                        <small v-if="dataDetail.activity_lectures && !dataDetail.activity_lectures[0]"><i>belum ada presensi</i></small>
                                        <br>
                                        <div class="mb-2" v-if="dataDetail.pengampu && dataDetail.pengampu[0]">
                                            <h6 class="mb-0">Pengampu</h6>
                                            <div style="font-size: smaller" v-for="(pengampu, peng) in dataDetail.pengampu" :key="peng">
                                                <span>{{peng + 1}}. {{pengampu.name}}</span>
                                            </div>
                                        </div>
                                        <div class="mb-2" v-if="dataDetail.pembimbing && dataDetail.pembimbing[0]">
                                            <h6 class="mb-0">Pembimbing</h6>
                                            <div style="font-size: smaller" v-for="(pembimbing, pem) in dataDetail.pembimbing" :key="pem">
                                                <span>{{pem + 1}}. {{pembimbing.name}}</span>
                                            </div>
                                        </div>
                                        <div class="mb-2" v-if="dataDetail.penguji && dataDetail.penguji[0]">
                                            <h6 class="mb-0">Penguji</h6>
                                            <div style="font-size: smaller" v-for="(penguji, uji) in dataDetail.penguji" :key="uji">
                                                <span>{{uji + 1}}. {{penguji.name}}</span>
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
                base_url: '/sadmin/activities',
                editMode : false,
                is_loading : false,
                dataContent: {},
                dataRaw: {
                    lectures:[],
                },
                dataDetail: '',
                filter: new form({
                    keyword: '',
                    lecture_id: '',
                })
            }
        },
        methods:{
            loadDataContent(page = 1){
                this.filter.page = page;
                this.is_loading = true;
                axios.get(this.base_url, {
                    params: this.filter
                }).then( ({data}) => {
                    this.dataContent = data;
                    this.is_loading = false;
                })
            },
            resetData(){
                this.filter.reset();
                this.loadDataContent()
            },
            detailModal(act){
                this.dataDetail = '';
                axios.get('/sadmin/activities/' + act.id)
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
            loadLectureList() {
                axios.get('/sadmin/lecture-list')
                    .then(({data})=>{
                        this.dataRaw.lectures = data.result;
                    })
            },
        },
        created(){
            this.loadDataContent();
            this.loadLectureList();

            Fire.$on('ModalSuccess', () => {
                this.loadDataContent();
                this.popSuccessSwal();
                $('#editAddModal').modal('hide');
            })
        },
    }
</script>
