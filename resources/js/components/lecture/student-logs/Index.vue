<template>
    <div class="content-wrapper">
        <div class="content mt-2">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body table-responsive p-0">
                            <table class="table table-head-fixed">
                                <tr>
                                    <th style="width: 30px">No</th>
                                    <th style="width: 15%">Residen</th>
                                    <th style="width: 15%">Dosen</th>
                                    <th style="width: 15%">Stase</th>
                                    <th>Logbook</th>
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
                                               placeholder="Cari dosen" v-model="filter.lecture">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" @keyup.enter="loadDataContent"
                                               placeholder="Cari stase" v-model="filter.stase">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" @keyup.enter="loadDataContent"
                                               placeholder="Cari data" v-model="filter.q">
                                    </td>
                                </tr>
                                <tr v-for="(data, i) in dataContent.data" :key="data.id">
                                    <td class="d-none d-md-table-cell">
                                        <span :title="data.id">
                                            {{ dataContent.per_page * (dataContent.current_page - 1) + i + 1 }}
                                        </span>
                                    </td>
                                    <td>
                                        <small v-if="data.student"><b>{{ data.student.name }}</b></small>
                                        <div><small>{{ data.date | formatDate }}</small></div>
                                    </td>
                                    <td>
                                        <small v-if="data.lecture">{{ data.lecture.name }}</small>
                                        <div>
                                            <span v-if="data.status === 1" class="badge badge-success">verified</span>
                                            <span v-if="data.status === 0" class="badge badge-secondary">pending</span>
                                        </div>
                                    </td>
                                    <td>
                                        <small v-if="data.stase && data.options">
                                            {{ data.stase.name }}:
                                            <i>{{ data.options.name }}</i>
                                        </small>
                                    </td>
                                    <td>
                                        <div style="font-size: small" v-if="data.options && data.options.parse_desc">
                                            <span v-if="data.options.parse_desc.field_1">
                                                <u>{{ data.options.parse_desc.field_1 }}</u>: {{ data.field_1 }} <br>
                                            </span>
                                            <span v-if="data.options.parse_desc.field_2">
                                                <u>{{ data.options.parse_desc.field_2 }}</u> : {{data.field_2 }} <br>
                                            </span>
                                            <span v-if="data.options.parse_desc.field_3">
                                                <u>{{ data.options.parse_desc.field_3 }}</u> : {{data.field_3 }} <br>
                                            </span>
                                            <span v-if="data.options.parse_desc.field_4">
                                                <u>{{ data.options.parse_desc.field_4 }}</u> : {{data.field_4 }}
                                            </span>
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
    </div>
</template>

<script>
export default {
    data() {
        return {
            base_url: '/cmsd/student-logs',
            dataContent: {},
            dataProperties: {},
            img: '',
            filter: new form({
                resident: '',
                lecture: '',
                status: '',
                stase: '',
                page: '',
                q: '',
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
        resetData() {
            this.filter.reset();
            this.loadDataContent();
        },
    },
    created() {
        this.loadDataContent();
    },
}
</script>
