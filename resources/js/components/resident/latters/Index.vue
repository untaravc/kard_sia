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
                <div class="col-md-3">
                    <button @click="addModal('send')" class="btn btn-primary btn-block mb-2">
                        <i class="fa fa-upload"></i> Upload Dokumen
                    </button>
                    <button @click="addModal('request')" class="btn btn-success btn-block mb-3">
                        <i class="fa fa-download"></i> Permintaan Dokumen
                    </button>

                </div>
                <div class="col-md-9">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Dokumen</h3>

                            <div class="card-tools">
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" v-model="filter.keyword" @keyup.enter="loadDataContent" placeholder="Cari dokumen">
                                    <div class="input-group-append">
                                        <div class="btn btn-primary">
                                            <i class="fas fa-search"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive mailbox-messages">
                                <table class="table table-hover table-striped">
                                    <thead>
                                    <tr>
                                        <th width="50px">No</th>
                                        <th>Dokumen</th>
                                        <th></th>
                                        <th class="text-right">Aksi</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <loading :active.sync="isLoading" :can-cancel="false" loader="dots" :is-full-page="false"></loading>
                                    <tr v-for="(data, i) in dataContent.data" :key="data.id">
                                        <td><span :title="data.id">{{dataContent.per_page * (dataContent.current_page - 1) + i + 1}} </span></td>
                                        <td>
                                            <span class="badge badge-light" v-if="data.type === 'send'">{{data.type}}</span>
                                            <span class="badge badge-primary" v-if="data.type !== 'send'">{{data.type}}</span>
                                            <i class="text-black-50">#{{data.category}}</i>
                                            <br>
                                            <b>{{data.title}}</b><br>
                                            <small>{{ data.created_at | formatDateTime}}</small>
                                        </td>
                                        <td>
                                            <file-preview v-if="data.file"  :link="'/storage/' + data.file"></file-preview>
                                        </td>
                                        <td class="action-button">
                                            <button @click="deleteData(data.id)" class="btn btn-sm btn-danger">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                            <button @click="editModal(data)" class="btn btn-sm btn-info">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <a target="_blank" v-if="data.file" download :href="`/storage/`+data.file" class="btn btn-sm btn-success">
                                                <i class="fa fa-download"></i>
                                            </a>
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
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 v-if="!editMode && sendMode" class="modal-title">Upload Dokumen</h5>
                        <h5 v-if="!editMode && !sendMode" class="modal-title">Permintaan Dokumen</h5>
                        <h5 v-if="editMode" class="modal-title">Ubah Dokumen</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label>Judul</label>
                                <input type="text" class="form-control" v-model="form.title" :class="{ 'is-invalid': form.errors.has('title') }">
                                <has-error :form="form" field="title"></has-error>
                            </div>

                            <div class="col-md-12">
                                <label>Keterangan</label>
                                <textarea rows="5" class="form-control" v-model="form.desc" :class="{ 'is-invalid': form.errors.has('desc') }"></textarea>
                                <has-error :form="form" field="desc"></has-error>
                            </div>
<<<<<<< HEAD
                            <div class="col-md-12" v-if="form.category === 'pengabdian_masyarakat'">
                                <label>Tanggal</label>
                                <input class="form-control" type="date" v-model="form.date" :class="{ 'is-invalid': form.errors.has('date') }">
                                <has-error :form="form" field="desc"></has-error>
                            </div>
=======
>>>>>>> 3a0ad237c4a32c5d3821fb143edff98da043fa9c
                            <div class="col-md-12">
                                <label>Kategori</label>
                                <select class="form-control" v-model="form.category" :class="{ 'is-invalid': form.errors.has('category') }">
                                    <option v-for="cat in dataRaw.categories" :value="cat.desc">{{cat.name}}</option>
                                </select>
                                <has-error :form="form" field="category"></has-error>
                            </div>
                            <div v-show="!editMode && sendMode" class="col-md-12">
                                <label>File</label><br>
                                <input type="file" id="form_file" :class="{ 'is-invalid': form.errors.has('file') }">
                                <has-error :form="form" field="file"></has-error>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button :disabled="disable" v-show="!editMode" class="btn btn-primary" @click="createData">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Simpan</button>
                        <button :disabled="disable" v-show="editMode" class="btn btn-primary" @click="updateData">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Ubah</button>
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
<<<<<<< HEAD
                    date:'',
=======
>>>>>>> 3a0ad237c4a32c5d3821fb143edff98da043fa9c
                    type:'',
                }),
                filter: {
                    keyword: '',
                    page: '',
                    cat: '',
                    lecture_id:'',
                }
            }
        },
        methods:{
            loadDataContent(page = 1){
                this.filter.page = page;
                this.isLoading = true;
                axios.get('/cmsr/documents', {params: this.filter})
                    .then( ({data}) => {
                        this.dataContent = data;
                        this.isLoading = false;
                    }).catch((error)=>{
                        this.isLoading = false;
                })
            },
            loadCategories(type){
                axios.get('/cmsr/document_categories', {params: {type: type}})
                .then(({data})=>{
                    this.dataRaw.categories = data.data;
                })
            },
            resetData(){
                this.filter.reset();
                this.loadDataContent()
            },
            addModal(type){
                this.editMode = false;
                this.form.reset();
                this.form.type = type;
                this.loadCategories(type);

                if(type == 'send'){
                    this.sendMode = true;
                }else{
                    this.sendMode = false;
                }

                $('#form_file').val('')
                $('#editAddModal').modal({backdrop: 'static', keyboard: false})
            },
            createData() {
                this.disable = true;
                const form_file = document.getElementById('form_file');

                const formData = new FormData();
                if (document.getElementById('form_file').value != '') {
                    formData.append('file', form_file.files[0]);
                }
                formData.append('category', this.form.category);
                formData.append('title', this.form.title);
                formData.append('desc', this.form.desc);
                formData.append('type', this.form.type);

                axios.post( '/cmsr/documents', formData)
                    .then(({data}) => {
                        this.disable = false;
                        $('#editAddModal').modal('hide');
                        this.loadDataContent()
                    }).catch((e) => {
                    this.disable = false;
                    let errors = e.response.data.errors;

                    if (errors) {
                        this.form.errors.errors = errors;
                    }
                });
            },
            editModal(data){
                this.editMode = true;
                $('#editAddModal').modal({backdrop: 'static', keyboard: false});
                this.form.fill(data);
                this.loadCategories(data.type)
            },
            updateData(){
                this.disable = true;
                this.form.patch('/cmsr/documents/'+this.form.id)
                    .then((response) => {
                        Fire.$emit('ModalSuccess');
                        this.disable = false;
                    }).catch((error) => {
                    this.disable = false;
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
        watch:{
            'form.category': function (category){
                let note = this.dataRaw.categories.find(cat => cat.desc === category).note;
                if(note){
                    this.form.desc = '';
                    note.forEach((nt)=>{
                        this.form.desc += nt + ': \n';
                    } )
                }
            },
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
