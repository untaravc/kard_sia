<template>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark"><i class="fa fa-project-diagram"></i> Stase Residen
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
                            <h1 class="card-title">Data Stase</h1>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <div class="row p-2">
                                <div class="col-md-2 col-6">
                                    Tampilkan
                                    <select class="form-control" v-model="filter.per_page">
                                        <option value="5">5</option>
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="70">70</option>
                                    </select>
                                </div>
                                <div class="col-md-2 col-6">
                                    Tahap
                                    <select v-model="filter.section" class="form-control">
                                        <option value="tahap_1">Tahap 1</option>
                                        <option value="tahap_2">Tahap 2</option>
                                        <option value="tahap_3">Tahap 3</option>
                                        <option value="referat">Referat</option>
                                    </select>
                                </div>
                                <div class="col-md-2 col-6">
                                    Stase
                                    <select v-model="filter.stase_id" class="form-control">
                                        <option v-for="stase in data_raw.stases" :value="stase.id">{{stase.name}}</option>
                                    </select>
                                </div>
                                <div class="col-md-2 col-6">
                                    Tahun
                                    <select class="form-control" v-model="filter.year">
                                        <option :value="year - 6">{{year - 6}}</option>
                                        <option :value="year - 5">{{year - 5}}</option>
                                        <option :value="year - 4">{{year - 4}}</option>
                                        <option :value="year - 3">{{year - 3}}</option>
                                        <option :value="year - 2">{{year - 2}}</option>
                                        <option :value="year - 1">{{year - 1}}</option>
                                        <option :value="year">{{year}}</option>
                                        <option :value="year + 1">{{year + 1}}</option>
                                    </select>
                                </div>
                                <div class="col-md-2 col-6">
                                    Bulan
                                    <input type="month" v-model="filter.month" class="form-control">
                                </div>
                                <div class="col-md-2 col-6">
                                    Aksi <br>
                                    <button class="btn btn-sm btn-secondary" @click="resetFilter">Reset Filter</button>
                                </div>
                            </div>
                            <vue-loader :active.sync="page_loader" :can-cancel="false" loader="dots" :is-full-page="false"></vue-loader>
                            <table class="table plots-table table-striped">
                                <tr v-if="data_content.data && data_content.data[0]">
                                    <th width="50px" class="brdr pd headnumber">No</th>
                                    <th width="300px" class="brdr pd headcol">Nama</th>
                                    <th width="80px" class="text-center pd brdr"
                                        v-for="(date, e) in data_content.data[0]['weeks']">
                                        <small>{{date.start_str}}</small>
                                    </th>
                                </tr>
                                <tr v-for="(data, d) in data_content.data" :class="((d + 1) % 3) === 0 ? 'bg-strip' : ''">
                                    <td class="brdr pd headnumber">{{d+1}}</td>
                                    <td class="brdr pd headcol">
                                        <b>{{data.name | truncate(20)}}</b> ({{data.year}}) <br>
                                        <small v-if="data.stase_logs_active && data.stase_logs_active.stase">{{data.stase_logs_active.stase.name}}</small>
                                    </td>
                                    <td class="text-center brdr" v-for="(week, w) in data.weeks"
                                        :style="week.stase ? 'background-color:' + week.stase.stase.color : ''">
                                        <small class="modalable" :title="week.stase.stase.name" :style="'color:' + week.stase.stase.font_color" v-if="week.stase && week.stase.stase">
                                            <span v-if="week.stase.stase.alias">{{week.stase.stase.alias}}</span>
                                            <span v-if="!week.stase.stase.alias">{{week.stase.stase.name}}</span>
                                        </small>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data(){
            return {
                base_url: '/sadmin/stase-plots',
                editMode : false,
                page_loader : true,
                data_content: {},
                data_detail: '',
                year: new Date().getFullYear(),
                data_raw:{
                    stases:{},
                },
                filter: new form({
                    year:'',
                    name:null,
                    stase:null,
                    section:null,
                    per_page:null,
                    month:null,
                    stase_id:null,
                })
            }
        },
        methods:{
            loadDataContent(){
                this.page_loader = true;
                axios.get(this.base_url, {params: this.filter})
                    .then( ({data}) => {
                        this.data_content = data;
                        this.page_loader = false;
                    }).catch(()=>{
                        this.page_loader = false;
                })
            },
            loadStase(){
                axios.get('/sadmin/get-stases')
                .then(({data})=>{
                    this.data_raw.stases = data;
                })
            },
            resetFilter(){
                this.filter.reset();
                this.loadDataContent();
            }
        },
        created(){
            this.loadDataContent();
            this.loadStase();
        },
        watch:{
            'filter.per_page': function (){
                this.loadDataContent();
            },
            'filter.year': function (){
                this.loadDataContent();
            },
            'filter.section': function (){
                this.loadDataContent();
            },
            'filter.month': function (){
                this.loadDataContent();
            },
            'filter.stase_id': function (){
                this.loadDataContent();
            }
        }
    }
</script>
<style>
.plots-table {
    table-layout: fixed;
}

.modalable{
    cursor: pointer;
}

.brdr {
    border-right: 1px solid #dcdcdc;
}
.bg-strip{
    background-color: #f6f6f6;
}
</style>
