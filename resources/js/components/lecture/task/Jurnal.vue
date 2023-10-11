<template>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark"><i class="fa fa-chalkboard-teacher"></i> Lembar Penilaian Jurnal
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
                            <h1 class="card-title">Poin Penilaian</h1>
                        </div>
                        <div class="card-body table-responsive">
                            <div class="form-group" v-for="(data, i) in dataDetail.stase_task_log_point" :key="i" >
                                <label>{{i+1}}. {{data.task_detail.name}} ({{data.score}})</label><br>
                                <div class="form-check">
                                    <input class="form-check-input" checked="checked" type="radio" :name="`name-stl`+data.id" :id="`stlp1`+data.id" v-model="form.no[i]" :value="data.id+`,75`">
                                    <label class="form-check-label" :for="`stlp1`+data.id">
                                        Kurang (75)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" :name="`name-stl`+data.id"  :id="`stlp2`+data.id" v-model="form.no[i]" :value="data.id+`,80`">
                                    <label class="form-check-label" :for="`stlp2`+data.id">
                                        Cukup (80)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" :name="`name-stl`+data.id"  :id="`stlp3`+data.id" v-model="form.no[i]" :value="data.id+`,85`">
                                    <label class="form-check-label" :for="`stlp3`+data.id">
                                        Sesuai Harapan (85)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" :name="`name-stl`+data.id" :id="`stlp4`+data.id" v-model="form.no[i]" :value="data.id+`,90`">
                                    <label class="form-check-label" :for="`stlp4`+data.id">
                                        Diatas Harapan (90)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" :name="`name-stl`+data.id" :id="`stlp5`+data.id" v-model="form.no[i]" :value="data.id+`,95`">
                                    <label class="form-check-label" :for="`stlp5`+data.id">
                                        Istimewa (95)
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-success" @click="submitData()">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
    input[type=radio] {
        /*border: 0px;*/
        width:  1em;
        height: 1em;
    }
</style>

<script>
    export default {
        data(){
            return {
                base_url: '/cmsd/task-detail/jurnal',
                dataDetail:'',
                form: new form({
                    id:'',
                    no:[],
                }),
                params:{
                    open_stase_task_id : this.$route.params.open_stase_task_id,
                }
            }
        },
        methods:{
            loadData(){
                axios.get('/cmsd/task-detail/jurnal', {params: this.params})
                    .then( ({data}) => {
                        this.dataDetail = data;
                        this.form.id = data.id
                    })
            },
            submitData(){
                axios.post('/cmsd/stase-task-logs/'+this.form.id, {params: this.form})
                .then(({data})=>{
                    console.log(data)
                }).catch(({response})=>{
                    console.log(response)
                })
            },
            popSuccessSwal(){
                Swal.fire({
                    position: 'top-end',
                    icon: 'success',
                    title: 'Success',
                    showConfirmButton: false,
                    timer: 1000,
                })
            },
        },
        created(){
            this.$Progress.start();
            this.loadData();
            Fire.$on('ModalSuccess', () => {
                this.loadDataContent();
                this.popSuccessSwal();
                $('#editAddModal').modal('hide');
                this.$Progress.finish();
            })
        },
        mounted() {
            this.$Progress.finish()
        },
        components: {
        }
    }
</script>
