<template>
    <div class="content-wrapper">
        <div class="content mt-2">
            <div class="row">
                <div class="col-lg-3 col-sm-6">
                    <div class="info-box"><span class="info-box-icon bg-secondary"><i
                        class="fa fa-check"></i></span>
                        <div class="info-box-content"><span class="info-box-text">Log Pending</span> <span
                            class="info-box-number">{{ dataProperties.log_pending }}</span></div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="info-box"><span class="info-box-icon bg-warning"><i
                        class="fa fa-users"></i></span>
                        <div class="info-box-content"><span class="info-box-text">Resident Pending</span> <span
                            class="info-box-number">{{ dataProperties.resident_pending }}</span></div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="info-box"><span class="info-box-icon bg-danger"><i
                        class="fa fa-user-alt"></i></span>
                        <div class="info-box-content"><span class="info-box-text">Lecture Pending</span> <span
                            class="info-box-number">{{ dataProperties.lecture_pending }}</span></div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="info-box"><span class="info-box-icon bg-success"><i
                        class="fa fa-check-double"></i></span>
                        <div class="info-box-content"><span class="info-box-text">Log Verified</span> <span
                            class="info-box-number">{{ dataProperties.log_verified }}</span></div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-body table-responsive p-0">
                            <table class="table table-head-fixed">
                                <tr>
                                    <th style="width: 30px">No</th>
                                    <th style="width: 20%">Residen</th>
                                    <th style="width: 20%">Dosen</th>
                                    <th>Logbook</th>
                                    <th style="width: 15%">Status</th>
                                </tr>
                                <tbody>
                                <tr>
                                    <td class="d-none d-md-table-cell"></td>
                                    <td>
                                        <input type="text" class="form-control" @keyup.enter="loadDataContent"
                                               placeholder="Cari residen" v-model="filter.resident">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" @keyup.enter="loadDataContent"
                                               placeholder="Cari residen" v-model="filter.lecture">
                                    </td>
                                    <td></td>
                                    <td>
                                        <select class="form-control" @change="loadDataContent" v-model="filter.status">
                                            <option value="1">verified</option>
                                            <option value="0">pending</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr v-for="(data, i) in dataContent.data" :key="data.id">
                                    <td class="d-none d-md-table-cell">
                                        <span :title="data.id">
                                            {{ dataContent.per_page * (dataContent.current_page - 1) + i + 1 }}
                                        </span>
                                    </td>
                                    <td>
                                        <span v-if="data.student">{{ data.student.name }}</span>
                                    </td>
                                    <td>
                                        <span v-if="data.lecture">{{ data.lecture.name }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-between align-items-start">
                                            <span>
                                                {{ data.date }} {{ data.type }}
                                            </span>
                                            <div v-if="data.photo_link">
                                                <img :src="data.photo_link"
                                                     style="height: 100px; border-radius: 3px; border: 1px solid #c2c2c2; padding: 2px;">
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span v-if="data.status === 1" class="badge badge-success">verified</span>
                                        <span v-if="data.status === 0" class="badge badge-secondary">pending</span>
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
    </div>
</template>

<script>
export default {
    data() {
        return {
            base_url: '/sadmin/student-logs',
            dataContent: {},
            dataProperties: {},
            img: '',
            filter: new form({
                resident: '',
                lecture: '',
                status: '',
                page: ''
            })
        }
    },
    methods: {
        loadDataContent(page = 1) {
            this.filter.page = page;
            axios.get(this.base_url, {params: this.filter})
                .then(({data}) => {
                    this.dataContent = data;
                })
        },
        loadProperties() {
            axios.get('/sadmin/student-logs-properties')
                .then(({data}) => {
                    this.dataProperties = data;
                })
        },
        resetData() {
            this.filter.reset();
            this.loadDataContent();
        },
    },
    created() {
        this.loadDataContent();
        this.loadProperties();
    },
}
</script>
