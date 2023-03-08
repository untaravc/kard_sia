<template>
    <div class="content-wrapper">
        <div class="content">
            <div class="row">
                <div class="col-12">
                    <div class="card mt-2">
                        <div class="card-header">
                            <h1 class="card-title">Kategori Soal</h1>
                            <div class="card-tools">
                                <button class="btn btn-sm btn-th-primary" @click="addModal">
                                    <i class="fa fa-user-plus"></i> Tambah
                                </button>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <vue-loader :active.sync="is_loading" :can-cancel="false" loader="dots"
                                        :is-full-page="false"></vue-loader>
                            <table class="table table-head-fixed">
                                <tr>
                                    <th style="width: 40px">No</th>
                                    <th>Nama</th>
                                    <th>Keterangan</th>
                                    <th><span style="float: right">Aksi</span></th>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td>
                                        <input type="text" v-model="filter.name" @keyup.enter="loadDataContent"
                                               class="form-control" placeholder="Cari..">
                                    </td>
                                    <td></td>
                                </tr>
                                <tbody>
                                <tr role="row" class="odd" v-for="(data, i) in dataContent.data" :key="data.id">
                                    <td>
                                        <span :title="data.id">
                                            {{ dataContent.per_page * (dataContent.current_page - 1) + i + 1 }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ data.name }}
                                    </td>
                                    <td>{{ data.desc }}</td>
                                    <td class="action-button">
                                        <button class="btn btn-wd btn-sm btn-th-primary" @click="editModal(data)">
                                            Ubah
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
                                        Menampilkan data {{ dataContent.from }} hingga {{ dataContent.to }} dari
                                        {{ dataContent.total }} data.
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-7">
                                    <div class="dataTables_paginate paging_simple_numbers" style="float: right">
                                        <pagination :data="dataContent"
                                                    @pagination-change-page="loadDataContent"></pagination>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade bd-example-modal-lg" id="editAddModal" tabindex="-1" role="dialog"
             aria-labelledby="myLargeModalLabel" aria-hidden="true">
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
                                <div class="col-md-12">
                                    <label>Nama</label>
                                    <input type="text" class="form-control" v-model="form.name"
                                              :class="{ 'is-invalid': form.errors.has('name') }">
                                    <has-error :form="form" field="name"></has-error>
                                </div>
                                <div class="col-md-12">
                                    <label>Deskripsi</label>
                                    <input type="text" class="form-control" v-model="form.desc"
                                              :class="{ 'is-invalid': form.errors.has('desc') }">
                                    <has-error :form="form" field="desc"></has-error>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Tutup</button>
                        <button v-show="!editMode" type="submit" class="btn btn-sm btn-th-primary" @click="createData">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status"
                                  aria-hidden="true"></span>
                            Simpan
                        </button>
                        <button v-show="editMode" type="submit" class="btn btn-sm btn-th-primary" @click="updateData">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status"
                                  aria-hidden="true"></span>
                            Ubah
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            base_url: '/sadmin/categories',
            editMode: false,
            is_loading: false,
            disable: false,
            dataContent: {},
            dataDetail: '',
            options: {
                option_1: false,
                option_2: false,
                option_3: false,
                option_4: false,
                option_5: false,
            },
            data_raw: {
                image_url: '',
                lectures: [],
            },
            form: new form({
                id: '',
                name: '',
                desc: '',
            }),
            filter: new form({
                question: '',
                category: '',
                page: ''
            })
        }
    },
    methods: {
        loadDataContent(page = 1) {
            this.filter.page = page
            this.is_loading = true
            axios.get(this.base_url, {params: this.filter})
                .then(({data}) => {
                    this.dataContent = data;
                    this.is_loading = false;
                }).catch(() => {
                this.is_loading = false;
            })
        },
        addModal() {
            this.form.reset();
            this.editMode = false;
            $('#editAddModal').modal({backdrop: 'static', keyboard: false})
        },
        createData() {
            this.disable = true;
            this.form.post(this.base_url)
                .then(({data}) => {
                    this.disable = false;
                    $('#editAddModal').modal('hide');
                    this.popGlobalSuccess({});
                    this.loadDataContent();
                }).catch((error) => {
                this.disable = false;
            });
        },
        editModal(data) {
            this.editMode = true;
            this.form.fill(data);
            this.options[data.answer] = true;
            $('#editAddModal').modal({backdrop: 'static', keyboard: false});
        },
        updateData() {
            this.disable = true;
            this.form.patch(this.base_url + '/' + this.form.id)
                .then(({data}) => {
                    this.disable = false;
                    $('#editAddModal').modal('hide');
                    this.popGlobalSuccess({});
                    this.loadDataContent();
                }).catch((error) => {
                this.disable = false;
            });
        },
        detailModal(data) {
            $('#detailModal').modal('show');
            this.dataDetail = data;
        },
        deleteData(id) {
            if (confirm('Hapus data?')) {
                this.form.delete(this.base_url + '/' + id).then((response) => {
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
        },
        selectOption(event) {
            let value = event.target.value;
            let checked = event.target.checked;

            this.options.option_1 = false;
            this.options.option_2 = false;
            this.options.option_3 = false;
            this.options.option_4 = false;
            this.options.option_5 = false;

            if (checked) {
                this.options[value] = true;
                this.form.answer = value;
            } else {
                this.options[value] = false;
                this.form.answer = '';
            }
        },
    },
    created() {
        this.loadDataContent();
    },
}
</script>

<style scoped>
input.largerCheckbox {
    transform: scale(2);
}

.form-check {
    width: 20px;
    margin-left: 10px;
    margin-top: 5px;
}
</style>
