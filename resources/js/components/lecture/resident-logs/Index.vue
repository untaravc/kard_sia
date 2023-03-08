<template>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12"><h1><i class="fa fa-file-alt"></i> Log book Residen</h1></div>
                </div>
            </div>
        </section>
        <div class="content mt-3">
            <div class="row">
                <div class="col-12">
                    <div class="row">
                        <div class="col-md-4">

                            <div class="card card-primary card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">Residen</h3>
                                </div>
                                <div class="card-body p-0">
                                    <div class="list-group" v-if="residents.length > 0">
                                        <a href="#" v-for="re in residents" v-if="re.student"
                                           @click="selectResident(re)"
                                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                           :class="resident_id === re.student.id ? 'active' : ''">
                                            <span>{{ re.student.name }}</span>
                                            <span class="badge badge-primary badge-pill">{{ re.pending }}</span>
                                        </a>
                                    </div>
                                    <div v-if="residents.length === 0" class="text-center p-3">
                                        <small class="text-black-50">Tidak ada logbook residen yang dalam proses
                                            persetujuan.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.col -->
                        <div class="col-md-8">
                            <div class="card card-primary card-outline" v-if="acc_count > 0">
                                <div class="card-header">
                                    <span>Persetujuan</span>
                                    <button @click="accModal"
                                            class="float-right btn btn-sm btn-success">
                                        <i class="fa fa-check-double"></i>
                                        Setujui Semua
                                        <span class="badge badge-light">{{ acc_count }}</span>
                                    </button>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body p-0">
                                    <vue-loader :active.sync="is_loading" :can-cancel="false" loader="dots"
                                                :is-full-page="false"></vue-loader>
                                    <div class="table-responsive">
                                        <table class="table table-custom">
                                            <tr>
                                                <td style="width: 90px">Tgl</td>
                                                <td>Data</td>
                                            </tr>
                                            <tr v-for="log in logs">
                                                <td>{{ log.date | formatSortDate }}</td>
                                                <td>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <b v-if="log.stase && log.options">{{ log.stase.name }}:
                                                                {{ log.options.name }}</b>
                                                            <div v-if="log.options && log.options.parse_desc">
                                                            <span v-if="log.options.parse_desc.field_1">
                                                                <u>{{ log.options.parse_desc.field_1 }}</u> : {{
                                                                    log.field_1
                                                                }} <br>
                                                            </span>
                                                                <span v-if="log.options.parse_desc.field_2">
                                                                <u>{{ log.options.parse_desc.field_2 }}</u> : {{
                                                                        log.field_2
                                                                    }} <br>
                                                            </span>
                                                                <span v-if="log.options.parse_desc.field_3">
                                                                <u>{{ log.options.parse_desc.field_3 }}</u> : {{
                                                                        log.field_3
                                                                    }} <br>
                                                            </span>
                                                                <span v-if="log.options.parse_desc.field_4">
                                                                <u>{{ log.options.parse_desc.field_4 }}</u> : {{
                                                                        log.field_4
                                                                    }}
                                                            </span>
                                                            </div>
                                                        </div>
                                                        <div v-if="log.photo_link">
                                                            <img :src="log.photo_link" @click="showImg(log)"
                                                                 style="width: 100px; border-radius: 3px; border: 1px solid #c2c2c2; padding: 2px;">
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade bd-example-modal-lg" id="accModal" tabindex="-1" role="dialog"
             aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content py-5">
                    <div class="modal-body text-center">
                        <h5>Setujui {{ acc_count }} data {{ acc_name }}?</h5>
                        <button class="btn btn-success" @click="accAll">
                            <span v-if="disable" class="spinner-border spinner-border-sm" role="status"
                                  aria-hidden="true"></span>
                            <span v-if="!disable">Setujui!</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="modal-img" tabindex="-1" role="dialog"
             aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <img :src="dataRaw.photo_link" alt="">
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            base_url: '',
            disable: false,
            is_loading: false,
            residents: [],
            resident_id: '',
            acc_count: 0,
            acc_name: '',
            logs: [],
            dataRaw:{
                photo_link: null,
            }
        }
    },
    methods: {
        loadResidentList() {
            axios.get('/cmsd/resident-log-list')
                .then(({data}) => {
                    this.residents = data.result;
                })
        },
        selectResident(data) {
            this.resident_id = data.student.id
            this.acc_count = data.pending
            this.acc_name = data.student.name
        },
        loadData(resident_id) {
            this.is_loading = true;
            axios.get('/cmsd/resident-log-list/' + resident_id)
                .then(({data}) => {
                    this.logs = data.result
                    this.is_loading = false;
                }).catch(() => {
                this.is_loading = false;
            })
        },
        accModal() {
            $('#accModal').modal('show');
        },
        accAll() {
            this.disable = true;
            axios.post('/cmsd/resident-log-list/' + this.resident_id)
                .then(({data}) => {
                    this.disable = false;
                    this.popGlobalSuccess({});
                    $('#accModal').modal('hide');
                    this.loadResidentList();
                    this.logs = [];
                    this.acc_count = 0;
                }).catch(() => {
                this.disable = false;
            })
        },
        showImg(data){
            this.dataRaw.photo_link = data.photo_link;
            $('#modal-img').modal('show');
        }
    },
    created() {
        this.loadResidentList();
    },
    watch: {
        'resident_id': function (val) {
            if (val) {
                this.loadData(val)
            }
        }
    }

}
</script>

<style scoped>
.list-group {
    border-radius: 0;
}

.table-custom td, .table-custom th {
    padding: 5px;
    font-size: 14px;
}
</style>
