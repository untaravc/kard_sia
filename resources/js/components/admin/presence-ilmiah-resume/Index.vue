<template>
    <div class="content-wrapper">
        <div class="content p-3">
            <div class="row">
                <div class="col-12">
                    <div class="card p-3">
                        <h5>Resume Presensi Ilmiah</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <input type="month" class="form-control form-control-sm" v-model="filter.date" @change="loadDataContent">
                            </div>
                            <div class="col-md-3">
                                <select class="form-control form-control-sm"
                                       @change="loadDataContent" v-model="filter.key">
                                    <option value="laporan jaga">Laporan Jaga</option>
                                    <option value="presensi">Presensi Harian</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <b>Total : {{filter.activity_count}}</b>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <vue-loader :active.sync="loading" :can-cancel="false" loader="dots"
                                        :is-full-page="false"></vue-loader>
                            <div class="col-md-6">
                                <table style="width: 100%; font-size: 12px">
                                    <tr>
                                        <th style="width: 40px">No</th>
                                        <th >Nama</th>
                                        <th style="width: 40%">Ilmiah</th>
                                    </tr>
                                    <tr style="border-bottom: 1px solid lightgray" v-for="(student, s) in data_content" v-if="s < (data_content.length / 2)">
                                        <td>{{s + 1}}</td>
                                        <td>{{student.name}}</td>
                                        <td class="text-right">
                                            {{student.lapjag}}
                                            <br>
                                            <div class="progress progress-sm">
                                                <div class="progress-bar bg-green" role="progressbar" v-if="(student.lapjag / filter.activity_count) > 0.79"
                                                     aria-valuenow="57" aria-valuemin="0"
                                                     aria-valuemax="100" :style="'width: '+ student.lapjag / filter.activity_count * 100 +'%'">
                                                </div>
                                                <div class="progress-bar bg-warning" role="progressbar" v-if="(student.lapjag / filter.activity_count) < 0.79"
                                                     aria-valuenow="57" aria-valuemin="0"
                                                     aria-valuemax="100" :style="'width: '+ student.lapjag / filter.activity_count * 100 +'%'">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-md-6">
                                <table style="width: 100%; font-size: 12px">
                                    <tr>
                                        <th style="width: 40px">No</th>
                                        <th >Nama</th>
                                        <th style="width: 40%">Ilmiah</th>
                                    </tr>
                                    <tr style="border-bottom: 1px solid lightgray" v-for="(student, s) in data_content" v-if="(s+1) > (data_content.length / 2)" v>
                                        <td>{{s + 1}}</td>
                                        <td>{{student.name}}</td>
                                        <td class="text-right">
                                            {{student.lapjag}}
                                            <br>
                                            <div class="progress progress-sm">
                                                <div class="progress-bar bg-green" role="progressbar" v-if="(student.lapjag / filter.activity_count) > 0.79"
                                                     aria-valuenow="57" aria-valuemin="0"
                                                     aria-valuemax="100" :style="'width: '+ student.lapjag / filter.activity_count * 100 +'%'">
                                                </div>
                                                <div class="progress-bar bg-warning" role="progressbar" v-if="(student.lapjag / filter.activity_count) < 0.79"
                                                     aria-valuenow="57" aria-valuemin="0"
                                                     aria-valuemax="100" :style="'width: '+ student.lapjag / filter.activity_count * 100 +'%'">
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
</template>


<script>
export default {
    data() {
        return {
            base_url: '/sadmin/presence-ilmiah-resume',
            loading: false,
            data_content: {},
            filter: new form({
                date: '',
                key: '',
                activity_count: '',
            })
        }
    },
    methods: {
        loadDataContent() {
            this.loading = true;
            axios.get(this.base_url, {params: this.filter})
                .then(({data}) => {
                    this.loading = false;
                    this.data_content = data.result;
                    this.filter.fill(data.query)
                }).catch(() => {
                this.loading = false;
            })
        }
    },
    created() {
        this.loadDataContent();
    }
}
</script>

<style scoped>
.progress-sm {
    height: 5px;
}
</style>
