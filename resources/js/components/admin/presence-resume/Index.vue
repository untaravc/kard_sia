<template>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-md-8">
                        <h1><i class="fa fa-file-alt"></i> Resume Presensi </h1>
                    </div>
                    <div class="col-md-4">
                        <input type="date" style="max-width: 180px" v-model="filter.date" class="form-control float-right">
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <div class="content mt-2">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <loading :active.sync="isLoading" :can-cancel="false" loader="dots" :is-full-page="false"></loading>
                        <div class="card-header">
                            Belum Presensi (<b v-if="dataContent.no_presence">{{dataContent.no_presence_count}}</b>)
                        </div>
                        <div class="card-body">
                            <small>
                                <table>
                                    <tr v-for="(non, n) in dataContent.no_presence">
                                        <td>{{non.name | truncate(30)}}</td>
                                        <td><i class="text-black-50" v-if="non.last_presence">last {{non.last_presence.checkin | formatDate}}</i></td>
                                    </tr>
                                </table>
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <loading :active.sync="isLoading" :can-cancel="false" loader="dots" :is-full-page="false"></loading>
                        <div class="card-header">
                            Sudah Presensi (<b v-if="dataContent.presence">{{dataContent.presence_count}}</b>)
                        </div>
                        <div class="card-body table-responsive">
                            <div class="row">
                                <div class="col-md-6">
                                    <small>
                                        <table>
                                            <tr v-if="p < (dataContent.presence.length / 2) " v-for="(pres, p) in dataContent.presence">
                                                <td>
                                                    <presence-label :pres="pres"></presence-label>
                                                    {{pres.name | truncate(22)}}
                                                </td>
                                                <td>
                                                    <i v-if="pres.presence">{{pres.presence.checkin | formatTime}}</i>
                                                    <i v-if="pres.presence && pres.presence.checkout">- {{pres.presence.checkout | formatTime}}</i>
                                                    <i v-if="pres.presence && !pres.presence.checkout">- ...</i>
                                                </td>
                                                <td>
                                                    <span class="text-black-50" v-if="pres.presence">{{pres.presence.duration}}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <small>
                                        <table>
                                            <tr v-if="(p + 0.1) > (dataContent.presence.length / 2) "  v-for="(pres, p) in dataContent.presence">
                                                <td>
                                                    <presence-label :pres="pres"></presence-label>
                                                    {{pres.name | truncate(22)}}
                                                </td>
                                                <td>
                                                    <i v-if="pres.presence">{{pres.presence.checkin | formatTime}}</i>
                                                    <i v-if="pres.presence && pres.presence.checkout">- {{pres.presence.checkout | formatTime}}</i>
                                                    <i v-if="pres.presence && !pres.presence.checkout">- ...</i>
                                                </td>
                                                <td>
                                                    <span class="text-black-50" v-if="pres.presence">{{pres.presence.duration}}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </small>
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
            base_url: '/daily_resume',
            dataContent: {},
            isLoading: false,
            filter: new form({
                date: '',
            })
        }
    },
    methods: {
        loadDataContent(page = 1) {
            this.filter.page = page;
            this.isLoading = true;
            axios.get(this.base_url, {params: this.filter})
                .then(({data}) => {
                    this.isLoading = false;
                    this.dataContent = data.data;
                    this.filter.date = data.date;
                }).catch(()=>{
                this.isLoading = false;
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
    watch: {
        'filter.date': function (val) {
            if (val.length > 3 || val.length === 0) {
                this.loadDataContent();
            }
        },
    }
}
</script>
