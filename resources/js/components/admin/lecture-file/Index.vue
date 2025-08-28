<template>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1><i class="fa fa-file-alt"></i> Dokumen</h1>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-md-3">
                    <button @click="addModal" class="btn btn-primary btn-block mb-3"><i class="fa fa-plus"></i> Tambah</button>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Dosen</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <select v-model="filter.lecture_id" class="form-control">
                                        <option value="" selected>Semua</option>
                                        <option v-for="(data, i) in dataRaw.lectures" :key="i" :value="data.id">{{data.name}}</option>
                                    </select>
                                    <has-error :form="form" field="lecture_id"></has-error>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Kategori</h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <ul class="nav nav-pills flex-column">
                                <li class="nav-item">
                                    <a href="#" @click="findCat('', 0)" :id="`cat_0`" class="nav-link">
                                        Semua
                                    </a>
                                </li>
                                <li v-for="(cat, c) in dataRaw.categories" class="nav-item">
                                    <a href="#" @click="findCat(cat.category, cat.id)" :id="`cat_`+cat.id" class="nav-link">
                                        {{cat.category}}
                                        <span class="badge bg-secondary float-right">{{cat.num}}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
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
                                        <th class="float-right">Aksi</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <loading :active.sync="isLoading" :can-cancel="false" loader="dots" :is-full-page="false"></loading>
                                    <tr v-for="(data, i) in dataContent.data" :key="data.id">
                                        <td><span :title="data.id">{{dataContent.per_page * (dataContent.current_page - 1) + i + 1}} </span></td>
                                        <td>
                                            <span class="badge badge-light" v-if="data.type === 'send'">{{data.type}}</span>
                                            <span class="badge badge-primary" v-if="data.type === 'request'">{{data.type}}</span>
                                            <span class="badge badge-secondary" v-if="data.type === 'archive'">{{data.type}}</span>
                                            <i>#{{data.category}}</i>
                                            <br>
                                            <b v-if="data.lecture">{{data.lecture.name}}</b><br>
                                            <span>{{data.title}}</span><br>
                                            <small>{{ data.created_at | formatDateTime}}</small>
                                        </td>
                                        <td>
                                            <file-preview v-if="data.file" :link="data.file"></file-preview>
                                        </td>
                                        <td class="action-button">
                                            <button @click="deleteData(data.id)" class="btn btn-sm btn-danger">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                            <button @click="editModal(data)" class="btn btn-sm btn-info">
                                                <i class="fa fa-edit"></i>
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
                                        <pagination :data="dataContent" @pagination-change-page="pagination"></pagination>
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
                        <h5 v-if="!editMode" class="modal-title">Simpan Dodumen</h5>
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
                            <div class="col-md-6">
                                <label>Dosen</label>
                                <select v-model="form.lecture_id" class="form-control" :class="{ 'is-invalid': form.errors.has('lecture_id') }">
                                    <!--                                    <option value="" selected>Banyak Dosen</option>-->
                                    <option v-for="(data, i) in dataRaw.lectures" :key="i" :value="data.id">{{data.name}}</option>
                                </select>
                                <has-error :form="form" field="lecture_id"></has-error>
                            </div>
                            <div class="col-md-6">
                                <label>Kategori</label>
                                <input type="text" class="form-control" v-model="form.category" :class="{ 'is-invalid': form.errors.has('category') }">
                                <has-error :form="form" field="category"></has-error>
                            </div>

                            <div class="col-md-12">
                                <label>Keterangan</label>
                                <textarea class="form-control" v-model="form.desc" :class="{ 'is-invalid': form.errors.has('desc') }"></textarea>
                                <has-error :form="form" field="desc"></has-error>
                            </div>
                            <div v-if="!editMode" class="col-md-12">
                                <label>File</label><br>
                                <i>Format dalam bentuk : PDF, Gambar, atau Zip</i><br>
                                <input type="file" @change="getFile" id="form_file" :class="{ 'is-invalid': form.errors.has('file') }">
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
    import Datepicker from 'vuejs-datepicker';
    export default {
        data(){
            return {
                base_url: '/sadmin/documents',
                editMode : false,
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
                    lecture_id:'',
                    file:'',
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
                axios.get(this.base_url, {params: this.filter})
                    .then( ({data}) => {
                        this.dataContent = data;
                        this.isLoading = false;
                    }).catch((error)=>{
                        alert(error);
                        this.isLoading = false;
                })
            },
            loadCategories(){
                axios.get('/document-categories', {params: this.filter})
                .then((response)=>{
                    this.dataRaw.categories = response.data;
                })
            },
            loadLectures(){
                axios.get('/get-lectures')
                    .then( ({data}) => {
                        this.dataRaw.lectures = data;
                    })
            },
            getFile(e){
                let file = e.target.files[0];

                let reader = new FileReader();
                if (file['size'] < 2200000){
                    reader.onloadend = (file)=>{
                        this.form.file = reader.result;
                    };
                    reader.readAsDataURL(file)
                } else {
                    this.form.file = '';
                    $('#form-image').val('');
                    Swal.fire({
                        icon: 'error',
                        title: 'You upload large file',
                        text: 'Please upload not more than 2MB file',
                    })
                }
            },
            getCat(obj){
                this.form.category = obj.category;
            },
            pagination(page = 1) {
                this.filter.page = page;
            },
            resetData(){
                this.filter.reset();
                this.loadDataContent()
            },
            addModal(){
                this.form.reset();
                $('#form_file').val('')
                this.editMode = false;
                $('#editAddModal').modal({backdrop: 'static', keyboard: false})
            },
            createData(){
                this.disable = true;
                this.form.post(this.base_url)
                    .then((response) => {
                        Fire.$emit('ModalSuccess');
                        this.disable = false;
                    }).catch((error) => {
                    console.log(error);
                    this.disable = false;
                });
            },
            editModal(data){
                this.editMode = true;
                $('#editAddModal').modal({backdrop: 'static', keyboard: false});
                this.form.fill(data);
            },
            updateData(){
                this.disable = true;
                this.form.patch(this.base_url+'/'+this.form.id)
                    .then((response) => {
                        Fire.$emit('ModalSuccess');
                        this.disable = false;
                    }).catch((error) => {
                    console.log(error)
                    this.disable = false;
                });
            },
            findCat(cat, id){
                this.filter.cat = cat;
                $('.nav-link').removeClass('active');
                $('#cat_'+id).addClass('active');
            },
            detailModal(data){
                $('#detailModal').modal('show');
                this.dataDetail = data;
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
                    showConfirmButton: false,
                    timer: 1000,
                })
            },
        },
        created(){
            this.$Progress.start();
            this.loadDataContent();
            this.loadCategories();
            this.loadLectures();

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
            'filter.page': function (val) {
                this.loadDataContent(val)
            },
            'filter.cat': function (val) {
                this.loadDataContent()
            },
            'filter.lecture_id': function (val) {
                this.loadDataContent()
            },
        }
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
