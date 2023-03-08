<template>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
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
                            <h1 class="card-title">Data Stase</h1>
                            <div class="card-tools">
                                <button class="btn btn-sm btn-success" @click="addModal">Add New</button>
                                <button @click="resetData" class="btn btn-secondary btn-sm">Reset</button>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-head-fixed">
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Desc</th>
                                    <th>Tugas</th>
                                    <th><span style="float: right">Aksi</span></th>
                                </tr>
                                <tbody>
                                <tr>
                                    <td></td>
                                    <td>
                                        <input type="text" v-model="filter.keyword" @keyup.enter="loadDataContent"
                                               class="form-control form-control-sm mb-2 mr-sm-2" >
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr role="row" class="odd" v-for="(data, i) in dataContent.data" :key="data.id">
                                    <td><span :title="data.id">{{dataContent.per_page * (dataContent.current_page - 1) + i + 1}} </span></td>
                                    <td>
                                        {{data.name}}
                                        <span class="text-black-50">{{data.alias}}</span>
                                        <i class="fa fa-circle" v-if="data.color" :style="'color: ' + data.color"></i>
                                    </td>
                                    <td>{{data.desc}}</td>
                                    <td>{{data.stase_tasks_count}}</td>
                                    <td class="action-button">
                                        <button class="btn btn-sm btn-danger" @click="deleteData(data.id)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                        <button class="btn btn-sm btn-success" @click="editModal(data)">
                                            <i class="fa fa-edit" ></i>
                                        </button>
                                        <router-link :to="`/cmss/stase/`+data.id" class="btn btn-sm btn-primary">
                                            <i class="fa fa-eye"></i>
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
                                        <pagination :data="dataContent" @pagination-change-page="loadDataContent"></pagination>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade bd-example-modal-lg" id="editAddModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" v-show="!editMode">Tambah</h5>
                        <h5 class="modal-title" v-show="editMode">Ubah</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="createData">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nama Stase</label>
                                        <input v-model="form.name" type="name"
                                               class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                                        <has-error :form="form" field="name"></has-error>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label>Bagian</label>
                                    <input v-model="form.desc" type="text"
                                           class="form-control" :class="{ 'is-invalid': form.errors.has('desc') }">
                                    <has-error :form="form" field="desc"></has-error>
                                </div>
                                <div class="col-md-6">
                                    <label>Inisial</label>
                                    <input v-model="form.alias" type="text"
                                           class="form-control" :class="{ 'is-invalid': form.errors.has('alias') }">
                                    <has-error :form="form" field="alias"></has-error>
                                </div>
                                <div class="col-md-6">
                                    <label>Warna Label</label>
                                    <input v-model="form.color" type="color"
                                           class="form-control" :class="{ 'is-invalid': form.errors.has('color') }">
                                    <has-error :form="form" field="color"></has-error>
                                </div>
                                <div class="col-md-6">
                                    <label>Warna Font</label>
                                    <input v-model="form.font_color" type="color"
                                           class="form-control" :class="{ 'is-invalid': form.errors.has('font_color') }">
                                    <has-error :form="form" field="font_color"></has-error>
                                </div>
                                <div class="col-md-6">
                                    <label>Demo</label>
                                    <div style="width: 180px; height: 30px" class="text-center"
                                         :style="'background-color:' + form.color + '; color:'+form.font_color">
                                        <span v-if="form.alias">{{form.alias}}</span>
                                        <span v-if="!form.alias">{{form.name}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label>Dosen Pengampu</label>
                                    <input type="text" class="form-control" v-model="form.lecture_name">
                                </div>
                                <div class="col-md-6">
                                    <label>Dosen Pengajar</label>
                                    <textarea class="form-control" v-model="form.lecture_names"></textarea>
                                </div>
                                <div class="col-md-12">
                                    <label>Link Evaluasi</label>
                                    <textarea class="form-control" v-model="form.evaluation_link"></textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button v-show="!editMode" type="submit" class="btn btn-primary" @click="createData">Simpan</button>
                        <button v-show="editMode" type="submit" class="btn btn-primary" @click="updateData">Ubah</button>
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
                base_url: '/sadmin/stases',
                editMode : false,
                dataContent: {},
                dataDetail: '',
                dataRaw: {
                    image_url: ''
                },
                form: new form({
                    id:'',
                    name:'',
                    desc:'',
                    color:'',
                    alias:'',
                    font_color:'',
                    lecture_name:'',
                    lecture_names:'',
                    evaluation_link:'',
                }),
                filter: new form({
                    keyword: '',
                    page: '',
                })
            }
        },
        methods:{
            loadDataContent(page = 1){
                this.filter.page = page;
                axios.get(this.base_url, {params: this.filter})
                    .then( ({data}) => ( this.dataContent = data))
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
            addModal(){
                this.form.reset();
                this.editMode = false;
                $('#editAddModal').modal({backdrop: 'static', keyboard: false})
            },
            createData(){
                this.$Progress.start();
                this.form.post(this.base_url)
                    .then((response) => {
                        Fire.$emit('ModalSuccess');
                    }).catch((error) => {
                    console.log(error)
                });
            },
            editModal(data){
                this.editMode = true;
                this.dataRaw.image_url = '';
                $('#editAddModal').modal({backdrop: 'static', keyboard: false});
                this.form.fill(data);
            },
            updateData(){
                this.$Progress.start();
                this.form.patch(this.base_url+'/'+this.form.id)
                    .then((response) => {
                        Fire.$emit('ModalSuccess');
                    }).catch((error) => {
                    console.log(error)
                });
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
                        this.form.delete(this.base_url+'/' + id).then((response) => {
                            this.loadDataContent();
                            Swal.fire(
                                'Deleted!',
                                'Data has been deleted.',
                                'success'
                            )
                            this.$Progress.finish();
                        }).catch((errror) => {
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
                    showConfirmButton: false,
                    timer: 1000,
                })
            },
        },
        created(){
            this.loadDataContent();

            Fire.$on('ModalSuccess', () => {
                this.loadDataContent(this.filter.page);
                this.popSuccessSwal();
                $('#editAddModal').modal('hide');
                this.$Progress.finish();
            })
        },
    }
</script>
