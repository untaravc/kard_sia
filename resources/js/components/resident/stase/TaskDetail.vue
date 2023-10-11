<template>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark"><i class="fa fa-chalkboard-teacher"></i> Detail Tugas Stase:
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
                                <button class="btn btn-sm btn-success" @click="addModal">Tambah Stase</button>
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
                                    <th><span style="float: right">Aksi</span></th>
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
                                    <td class="action-button">

                                    </td>
                                </tr>
                                </tbody>
                            </table>
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
                                <div class="col-md-6" v-if="!editMode">
                                    <label>Stase</label>
                                    <select v-model="form.stase_id" class="form-control" :class="{ 'is-invalid': form.errors.has('stase_id') }">
                                        <option v-for="(data, i) in dataRaw.stases" :key="i" :value="data.id" selected>{{data.name}}</option>
                                    </select>
                                    <has-error :form="form" field="stase_id"></has-error>
                                </div>
                                <div class="col-md-6">
                                    <label>Status</label>
                                    <select v-model="form.status" class="form-control" :class="{ 'is-invalid': form.errors.has('status') }">
                                        <option value="ongoing" selected>Sedang berlangsung</option>
                                        <option value="finish">Selesai</option>
                                        <option value="pending">Tunda</option>
                                    </select>
                                    <has-error :form="form" field="status"></has-error>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Catatan</label>
                                        <input v-model="form.note" type="text"
                                               class="form-control" :class="{ 'is-invalid': form.errors.has('note') }">
                                        <has-error :form="form" field="note"></has-error>
                                    </div>
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
    import Datepicker from 'vuejs-datepicker';
    export default {
        data(){
            return {
                base_url: '/cmsr/stases',
                editMode : false,
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
                    note:'',
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
                axios.get('/cmsr/residents')
                    .then( ({data}) => {
                        this.dataDetail = data;
                    })
            },
            addModal(){
                this.form.reset();
                this.editMode = false;
                $('#editAddModal').modal({backdrop: 'static', keyboard: false})
            },
            createData(){
                this.$Progress.start();
                this.form.post('/sadmin/stase-logs')
                    .then((response) => {
                        Fire.$emit('ModalSuccess');
                    }).catch((error) => {
                    console.log(error)
                });
            },
            editModal(data){
                this.editMode = true;
                $('#editAddModal').modal({backdrop: 'static', keyboard: false});
                this.form.fill(data);
            },
            updateData(){
                this.$Progress.start();
                this.form.patch("/sadmin/stase-logs/"+this.form.id)
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
                        this.form.delete('/sadmin/stase-logs/' + id).then((response) => {
                            this.loadData();
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
            this.loadData();

            Fire.$on('ModalSuccess', () => {
                this.loadData();
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
        }
    }
</script>
