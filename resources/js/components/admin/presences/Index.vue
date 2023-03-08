<template>
    <div class="content-wrapper">
        <div class="content mt-2">
            <div class="row">
                <div class="col-lg-3 col-sm-6">
                    <div class="info-box"><span class="info-box-icon bg-primary"><i
                        class="fa fa-check-double"></i></span>
                        <div class="info-box-content"><span class="info-box-text">Telah Presensi</span> <span
                            class="info-box-number">{{dataInfo.all}}</span></div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="info-box"><span class="info-box-icon bg-green bg-primary"><i
                        class="fa fa-check-circle"></i></span>
                        <div class="info-box-content"><span class="info-box-text">Masuk</span> <span
                            class="info-box-number">{{dataInfo.on}}</span></div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="info-box"><span class="info-box-icon bg-warning"><i
                        class="fa fa-minus-circle"></i></span>
                        <div class="info-box-content"><span class="info-box-text">Tidak Masuk</span> <span
                            class="info-box-number">{{dataInfo.off}}</span></div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="info-box"><span class="info-box-icon bg-secondary"><i
                        class="fa fa-sign-out-alt"></i></span>
                        <div class="info-box-content"><span class="info-box-text">Pulang</span> <span
                            class="info-box-number">{{dataInfo.checkout}}</span></div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-body table-responsive p-0">
                            <table class="table table-head-fixed">
                                <tr>
                                    <th width="30px" class="d-none d-md-table-cell">No</th>
                                    <th width="48%">In</th>
                                    <th width="48%">Out</th>
                                </tr>
                                <tbody>
                                <tr>
                                    <td class="d-none d-md-table-cell"></td>
                                    <td>
                                        <input type="text" class="form-control" placeholder="Cari nama.."
                                               v-model="filter.name">
                                    </td>
                                    <td></td>
                                </tr>
                                <tr v-for="(data, i) in dataContent.data" :key="data.id">
                                    <td class="d-none d-md-table-cell">
                                        <span :title="data.id">
                                            {{ dataContent.per_page * (dataContent.current_page - 1) + i + 1 }}
                                        </span>
                                    </td>
                                    <td>
                                        <i v-if="data.status === 'on'" class="fa fa-check-circle text-success"></i>
                                        <i v-if="data.status !== 'on'" class="fa fa-minus-circle text-secondary"></i>
                                        <b v-if="data.student">{{ data.student.name }}</b><br>
                                        <div class="row ml-0 mt-1">
                                            <div class="d-inline-block" style="width: 80px" v-if="data.checkin_photo">
                                                <img @click="photoModal(data.checkin_photo)" :src="data.checkin_photo"
                                                     style="height: 70px" :alt="data.checkin">
                                            </div>
                                            <div class="d-inline-block" style="font-size: smaller">
                                                <b>{{ data.checkin | formatSimple }} </b><br>
                                                <span v-if="data.checkin_data">
                                                    Akurasi : {{ Math.floor(data.checkin_data.accuracy) }} m <br>
                                                    Jarak : {{ Math.floor(data.checkin_data.distance) }} m <br>
                                                    Note : {{ data.checkin_note }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row ml-0 mt-4">
                                            <div class="d-inline-block" style="width: 80px" v-if="data.checkout_photo">
                                                <img @click="photoModal(data.checkout_photo)" :src="data.checkout_photo"
                                                     style="height: 70px">
                                            </div>
                                            <div class="d-inline-block" style="font-size: smaller">
                                                <b>{{ data.checkout | formatSimple }} </b><br>
                                                <span v-if="data.checkout_data">
                                                    Akurasi : {{ Math.floor(data.checkout_data.accuracy) }} m <br>
                                                    Jarak : {{ Math.floor(data.checkout_data.distance) }} m <br>
                                                    Note : {{ data.checkout_note }}
                                                </span>
                                            </div>
                                        </div>
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
                                        <pagination :data="dataContent" :limit="2"
                                                    @pagination-change-page="loadDataContent"></pagination>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade bd-example-modal-lg" id="imgModal" tabindex="-1" role="dialog"
             aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md text-center">
                <img :src="img" alt="">
            </div>
        </div>
    </div>
</template>

<script>
import Datepicker from 'vuejs-datepicker';

export default {
    data() {
        return {
            base_url: '/sadmin/presences',
            dataContent: {},
            dataInfo: {},
            img: '',
            filter: new form({
                name: '',
                checkin: '',
                status: '',
                page: ''
            })
        }
    },
    methods: {
        loadDataContent(page = 1) {
            console.log(page)
            this.filter.page = page;
            axios.get(this.base_url, {params: this.filter})
                .then(({data}) => {
                    this.dataContent = data;
                })
        },
        loadPresence(){
            axios.get('/sadmin/presences_today')
                .then(({data}) => {
                    this.dataInfo = data;
                })
        },
        resetData() {
            this.filter.reset();
            this.loadDataContent();
        },
        photoModal(img) {
            this.img = img
            $('#imgModal').modal('show')

        },
    },
    created() {
        this.loadDataContent();
        this.loadPresence();
        Fire.$on('ModalSuccess', () => {
            this.loadDataContent();
            this.popSuccessSwal();
            $('#editAddModal').modal('hide');
            this.$Progress.finish();
        })
    },
    components: {
        Datepicker,
    },
    watch: {
        'filter.name': function (val) {
            if (val.length > 3 || val.length === 0) {
                this.loadDataContent();
            }
        },
        'filter.status': function (val) {
            this.loadDataContent();
        },
    }
}
</script>
