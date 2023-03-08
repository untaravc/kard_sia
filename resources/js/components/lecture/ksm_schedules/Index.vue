<template>
    <div class="content-wrapper">
        <div class="content">
            <div class="row">
                <div class="col-12">
                    <div class="card mt-2">
                        <div class="card-header text-center">
                            <b>Jadwal Tindakan</b>
                        </div>
                        <div class="card-body">
                            <div class="row my-2">
                                <div class="col">
                                    <span v-if="dataContent.lecture">{{dataContent.lecture.name}}</span>
                                </div>
                                <div class="col">
                                    <input type="month" v-model="filter.period" @change="loadDataContent" class="form-control">
                                </div>
                                <div class="col">

                                </div>
                            </div>
                            <vue-loader :active.sync="is_loading" :can-cancel="false" loader="dots" :is-full-page="false"></vue-loader>
                            <div class="row" v-for="(week, w) in dataContent.data">
                                <div class="col-md-20 px-1 fixed-col" v-for="(day, d) in week" v-if="d < 5">
                                    <div class="card" style="min-height: 200px">
                                        <div class="card-body p-1">
                                            <div class="text-center py-1" :class="'footer-' + d">
                                                <span class="badge badge-light"> {{day.date | formatDay}}</span>
                                            </div>
                                            <div class="p-1">
                                                <div @click="detailModal(sch)" class="badge pointer" :class="sch.action_theme" style="width: 100%" v-if="day.schedules" v-for="sch in day.schedules">{{sch.name}}</div>
                                            </div>
                                        </div>
                                        <div class="card-footer text-center p-1">
                                            <span class="badge-light badge-cs"  @click="addModal(day.date)">
                                                <i class="fa fa-plus"></i> Tambah
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-body text-center">
                        <i>{{dataDetail.schedule | formatDayTime}}</i>
                        <br>
                        <span>{{dataDetail.name}}</span>
                        <br>
                        <span class="badge" :class="dataDetail.action_theme">{{dataDetail.action}}</span>
                        <br>
                        <b v-if="dataDetail.lecture">{{dataDetail.lecture.name}}</b>
                        <p>{{dataDetail.desc}}</p>

                        <button @click="deleteSchedule(dataDetail.id)" v-if="dataDetail.mine" class="btn btn-light border-secondary" style="width: 100%">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="editAddModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Baru</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <label>Tanggal</label>
                                <VueCtkDateTimePicker
                                    :no-label="true"
                                    id="schedule"
                                    v-model="form.schedule"
                                    format="YYYY-MM-DD HH:mm"
                                    minute-interval="5"
                                    :class="{ 'is-invalid': form.errors.has('schedule') }"
                                    locale="id"/>
                                <has-error :form="form" field="schedule"></has-error>
                            </div>
                            <div class="col-12">
                                <label>Nama/Inisial Pasien</label>
                                <input type="text" class="form-control"
                                       :class="{ 'is-invalid': form.errors.has('name') }"v-model="form.name">
                                <has-error :form="form" field="name"></has-error>
                            </div>
                            <div class="col-12">
                                <label>Tindakan</label>
                                <select class="form-control"
                                        :class="{ 'is-invalid': form.errors.has('action') }" v-model="form.action">
                                    <option value="struktural">Struktural</option>
                                    <option value="koroner">Koroner</option>
                                    <option value="vaskuler">Vaskuler</option>
                                    <option value="aritmia">Aritmia</option>
                                    <option value="pediatric">Pediatric</option>
                                </select>
                                <has-error :form="form" field="action"></has-error>
                            </div>
                            <div class="col-12">
                                <label>Deskripsi</label>
                                <textarea class="form-control" v-model="form.desc" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success":disabled="disable" @click="createData">
                            <span v-if="disable" class="spinner-border spinner-border-sm"
                                  role="status" aria-hidden="true"></span>
                            Tambah
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
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
            base_url: '/cmsd/ksm-schedules',
            editMode: false,
            is_loading: false,
            disable: false,
            dataContent: {},
            dataDetail: {},
            form: new form({
                id: '',
                schedule:'',
                action:'',
                name:'',
                desc:'',
            }),
            filter: new form({
                period: '',
                student_id: this.$route.params.id
            })
        }
    },
    methods: {
        loadDataContent(page = 1) {
            this.filter.page = page;
            this.is_loading = true;
            axios.get(this.base_url, {params: this.filter})
                .then(({data}) => {
                    this.dataContent = data;
                    this.is_loading = false;
                })
        },
        addModal(date){
            this.form.reset()
            this.form.schedule = date;
            $('#editAddModal').modal('show');
        },
        createData(){
            this.disable = true;
            this.form.post(this.base_url)
            .then(({data})=>{
                if(data.status){
                    $('#editAddModal').modal('hide');
                    this.form.reset();
                    this.loadDataContent();
                }
                this.disable = false;
            }).catch(()=>{
                this.disable = false;
            })
        },
        editModal(data){
            this.form.fill(data);
            $('#editAddModal').modal('show');
        },
        detailModal(data){
            this.dataDetail = data;
            $('#detailModal').modal('show');
        },
        deleteSchedule(id){
            axios.delete(this.base_url + '/' + id)
            .then(({data})=>{
                if(data.status){
                    $('#detailModal').modal('hide');
                    this.loadDataContent();
                }
            })
        }
    },
    created() {
        this.loadDataContent();
    }
}
</script>
<style>
.fixed-col{
    min-width: 180px;
}

@media (min-width: 768px){
    .col-md-20{
        flex: 0 0 20%;
        max-width: 20%;
    }
}

.footer-0{
    background-image: linear-gradient(to right, #cdd9f6, #afc3f1);;
}

.footer-1{
    background-image: linear-gradient(to right,#afc3f1, #94b0ef);;
}

.footer-2{
    background-image: linear-gradient(to right,#94b0ef, #7c9fee);;
}

.footer-3{
    background-image: linear-gradient(to right,#7c9fee, #648eec);;
}

.footer-4{
    background-image: linear-gradient(to right,#648eec, #4f7ee7);;
}

.pointer{
    cursor: pointer;
}
</style>
