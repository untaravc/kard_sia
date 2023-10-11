<template>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark"><i class="fa fa-chalkboard-teacher"></i> Document Review</h1>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h1 class="card-title">Pengabdian Masyarakat</h1>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-head-fixed">
                                <tr>
                                    <th style="width: 50px">No</th>
                                    <th>Residen</th>
                                    <th>Judul</th>
                                    <th>Laporan</th>
                                    <th>Mon&Ev</th>
                                    <th><span style="float: right">Aksi</span></th>
                                </tr>
                                <tbody>
                                <tr role="row" class="odd" v-for="(data, i) in dataContent.data" :key="data.id">
                                    <td>
                                        <span :title="data.id">{{ dataContent.per_page * (dataContent.current_page - 1) + i + 1 }} </span>
                                    </td>
                                    <td :title="data.student_id">
                                        <span v-if="data.student">{{ data.student.name}}</span>
                                        <div>
                                            <small>{{data.date | formatDate }}</small>
                                        </div>
                                    </td>
                                    <td :title="data.title">
                                        {{ data.title | truncate(30)}}
                                    </td>
                                    <td>
                                        <a v-if="data.file" :href="'/storage/' + data.file" target="_blank">File<br></a>
                                        <a v-if="data.attachment" :href="'/storage/' + data.attachment" target="_blank">Attachment</a>
                                    </td>
                                    <td :title="data.comment">
                                        {{ data.comment | truncate(30)}}
                                    </td>
                                    <td class="action-button">
                                        <button class="btn btn-sm btn-success" @click="detailModal(data)">
                                            <i class="fa fa-edit"></i>
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
                                        <pagination :data="dataContent" :limit="3"
                                                    @pagination-change-page="loadDataContent"></pagination>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade bd-example-modal-lg" id="detailModal" tabindex="-1" role="dialog"
             aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Monitoring & Evaluasi</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <b>Judul</b>
                                <p>{{form.title}}</p>
                                <b>Deskripsi</b>
                                <p>{{form.desc}}</p>
                                <b>Laporan & Dokumentasi</b>
                                <div>
                                    <a v-if="form.file" :href="'/storage/' + form.file" target="_blank">File<br></a>
                                    <a v-if="form.attachment" :href="'/storage/' + form.attachment" target="_blank">Attachment</a>
                                </div>
                            </div>
                            <div class="col-12">
                                <div>Monitoring & Evaluasi</div>
                                <textarea class="form-control" v-model="form.comment"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" @click="addComment">Simpan</button>
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
            base_url: '/sadmin/documents-review',
            editMode: false,
            dataContent: {},
            dataDetail: '',
            dataRaw: {
                image_url: ''
            },
            form: new form({
                id: "",
                lecture_id: "",
                student_id: "",
                type: "",
                title: "",
                category: "",
                desc: "",
                file: "",
                attachment: "",
                comment: "",
                date: "",
                model: "",
                relation_id: "",
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
    methods: {
        loadDataContent(page = 1) {
            this.filter.page = page
            console.log(this.filter)
            axios.get(this.base_url, {params: this.filter})
                .then(({data}) => (this.dataContent = data))
        },
        resetData() {
            this.filter.reset();
            this.loadDataContent()
        },
        addComment() {
            this.form.post('/sadmin/documment-comment/' + this.form.id)
                .then((response) => {
                    $('#detailModal').modal('hide')
                    this.loadDataContent(this.filter.page)
                }).catch((error) => {

            });
        },
        detailModal(data) {
            $('#detailModal').modal({
                backdrop: 'static',
                keyboard: false
            }).modal('show');
            this.form.fill(data);
        },
        popSuccessSwal() {
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: 'Success',
                showConfirmButton: false,
                timer: 1000,
            })
        },
    },
    created() {
        this.loadDataContent();
    },
}
</script>
