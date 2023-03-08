<template>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1><i class="fa fa-file-alt"></i> Dokumen dan Surat</h1>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">
<!--                <div class="col-md-3">-->
<!--                    <button @click="addModal('send')" class="btn btn-primary btn-block mb-2">-->
<!--                        <i class="fa fa-upload"></i> Upload Dokumen-->
<!--                    </button>-->
<!--                </div>-->
                <div class="col-md-12">
                    <div class="card card-primary card-outline">
<!--                        <div class="card-header">-->
<!--                            <h3 class="card-title">Dokumen</h3>-->

<!--                            <div class="card-tools">-->
<!--                                <div class="input-group input-group-sm">-->
<!--                                    <input type="text" class="form-control" v-model="filter.keyword" @keyup.enter="loadDataContent" placeholder="Cari dokumen">-->
<!--                                    <div class="input-group-append">-->
<!--                                        <div class="btn btn-primary">-->
<!--                                            <i class="fas fa-search"></i>-->
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
                        <div class="card-body p-0">
                            <div class="table-responsive mailbox-messages">
                                <table class="table table-hover table-striped">
                                    <thead>
                                    <tr>
                                        <th width="50px">No</th>
                                        <th>Tipe</th>
                                        <th>Kategori</th>
                                        <th>Residen</th>
                                        <th>Dokumen</th>
                                        <th style="max-width: 130px" class="text-right">Aksi</th>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>
                                            <select class="form-control" v-model="filter.type" @change="loadDataContent">
                                                <option value="">Semua Tipe</option>
                                                <option value="request">Request</option>
                                                <option value="send">Send</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" placeholder="Cari kategori.."
                                                   @keyup.enter="loadDataContent" v-model="filter.category">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" placeholder="Cari residen.."
                                                   @keyup.enter="loadDataContent" v-model="filter.student">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" placeholder="Cari dokumen.."
                                                   @keyup.enter="loadDataContent" v-model="filter.title">
                                        </td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <loading :active.sync="isLoading" :can-cancel="false" loader="dots" :is-full-page="false"></loading>
                                    <tr v-for="(data, i) in dataContent.data" :key="data.id">
                                        <td><span :title="data.id">{{dataContent.per_page * (dataContent.current_page - 1) + i + 1}} </span></td>
                                        <td>
                                            <span class="badge badge-light" v-if="data.type === 'send'">{{data.type}}</span>
                                            <span class="badge badge-warning" v-if="data.type !== 'send'">{{data.type}}</span>
                                        </td>
                                        <td>
                                            <i>{{data.category}}</i><br>
                                            <small>{{ data.created_at | formatDateTime}}</small>
                                        </td>
                                        <td>
                                            <span v-if="data.student">{{data.student.name}}</span>
                                        </td>
                                        <td>
                                            <span>{{data.title}}</span>
                                            <file-preview v-if="data.file"  :link="'/storage/' + data.file"></file-preview>
                                        </td>
                                        <td class="text-right">
                                            <button v-if="data.type === 'request'"
                                                    @click="editModal(data)" class="btn btn-sm btn-info">
                                                <i class="fa fa-upload"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
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
        </section>

        <div class="modal fade bd-example-modal-lg" id="editAddModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 v-if="editMode" class="modal-title">Upload Dokumen</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <h4>{{ form.title }}</h4>
                                <i>#{{form.category}}</i>
                                <p>{{form.desc}}</p>
                            </div>

                            <div class="col-md-12 text-center">
                                <label>File</label><br>
                                <input style="border: 1px solid grey; border-radius: 3px; padding: 2px" type="file"
                                       id="form_file" :class="{ 'is-invalid': form.errors.has('file') }">
                                <has-error :form="form" field="file"></has-error>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button :disabled="disable" v-show="editMode" class="btn btn-primary" @click="updateData">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Proses</button>
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
                editMode : false,
                sendMode : true,
                isLoading : false,
                disable : false,
                dataContent: {},
                dataDetail: '',
                dataRaw: {
                    file: '',
                    categories:[],
                },
                form: new form({
                    id:'',
                    title:'',
                    desc:'',
                    category:'',
                    file:'',
                    type:'',
                }),
                filter: {
                    keyword: '',
                    page: '',
                    category: '',
                    title:'',
                    type:'',
                    student:'',
                }
            }
        },
        methods:{
            loadDataContent(page = 1){
                this.filter.page = page;
                this.isLoading = true;
                axios.get('/sadmin/letters', {params: this.filter})
                    .then( ({data}) => {
                        this.dataContent = data;
                        this.isLoading = false;
                    }).catch((error)=>{
                        this.isLoading = false;
                })
            },
            loadCategories(type){
                axios.get('/sadmin/document_categories', {params: {type: type}})
                .then(({data})=>{
                    this.dataRaw.categories = data.data;
                })
            },
            resetData(){
                this.filter.reset();
                this.loadDataContent()
            },
            editModal(data){
                this.editMode = true;
                $('#editAddModal').modal({backdrop: 'static', keyboard: false});
                this.form.fill(data);
                this.loadCategories(data.type)
            },
            updateData(){
                this.disable = true;
                const form_file = document.getElementById('form_file');

                const formData = new FormData();
                if (document.getElementById('form_file').value != '') {
                    formData.append('file', form_file.files[0]);
                }

                axios.post( '/sadmin/letters/' + this.form.id, formData)
                    .then(({data}) => {
                        this.disable = false;
                        $('#editAddModal').modal('hide');
                        this.loadDataContent();
                        $('#form_file').val('')
                    }).catch((e) => {
                        this.disable = false;
                        let errors = e.response.data.errors;

                        if (errors) {
                            this.form.errors.errors = errors;
                        }
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
                    if (result.value) {
                        axios.post('/cmsr/documents/' + id, {_method: 'delete'}).then((response) => {
                            this.loadDataContent(this.filter.page);
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
                    toast: true,
                    showConfirmButton: false,
                    timer: 1000,
                })
            },
        },
        created(){
            this.loadDataContent();

            Fire.$on('ModalSuccess', () => {
                this.loadDataContent();
                this.popSuccessSwal();
                $('#editAddModal').modal('hide');
                this.$Progress.finish();
            })
        },
    }
</script>

<style>
    .autocomplete-input {
        display: block;
        width: 100%;
        height: calc(2.25rem + 2px);
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #495057;
        background-color: #ffffff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        box-shadow: inset 0 0 0 rgba(0, 0, 0, 0);
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
</style>
