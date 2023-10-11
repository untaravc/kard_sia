<template>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Dashboard</h1>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <div class="content">
            <div class="row">
                <div class="col-12">
                    <div class="row">
                        <div class="col-lg-3 col-sm-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-green"><i class="fa fa-user-graduate"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Residen (aktif)</span>
                                    <span class="info-box-number">{{cards.student}} ({{cards.student_active}})</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-sm-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fa fa-chalkboard-teacher"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Dosen</span>
                                    <span class="info-box-number">{{cards.lecture}} ({{cards.lecture_active}})</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fa fa-book-reader"></i></span>

                                <div class="info-box-content">
                                    <span class="info-box-text">Jadwal Ujian</span>
                                    <span class="info-box-number">{{cards.open_exam}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-secondary"><i class="fa fa-running"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Aktivitas</span>
                                    <span class="info-box-number">{{cards.activities}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
<!--                <div class="col-md-8">-->
<!--                    <div class="card">-->
<!--                        <div class="card-header">-->
<!--                            <h1 class="card-title">Presensi</h1>-->
<!--                        </div>-->
<!--                        <div class="card-body">-->
<!--                            <dashboard-chart></dashboard-chart>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h1 class="card-title">Stase</h1>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4 col-md-6 col-12" v-for="(data, d) in dataContent" :key="d">
                                    <div v-if="data.desc === 'tahap_1'" class="position-relative kotak border border-success p-3 mb-3" style="height: 150px">
                                        <div class="ribbon-wrapper ribbon-md">
                                            <div class="ribbon bg-secondary">
                                                Tahap 1
                                            </div>
                                        </div>
                                        <p style="height: 50px" class="mb-0">{{data.name}}</p>
                                        <small class="pb-1" v-for="(item, i) in data.stase_log_ongoing" :key="i">{{item.student.name}}<br></small>
                                    </div>
                                    <div v-if="data.desc === 'tahap_2'" class="position-relative kotak border border-info p-3 mb-3" style="height: 150px">
                                        <div class="ribbon-wrapper ribbon-md">
                                            <div class="ribbon bg-info">
                                                Tahap 2
                                            </div>
                                        </div>
                                        <p style="height: 50px" class="mb-0">{{data.name}}</p>
                                        <small v-for="(item, i) in data.stase_log_ongoing" :key="i">
                                            <span v-if="item.student">{{item.student.name}}</span><br></small>
                                    </div>
                                    <div v-if="data.desc === 'tahap_3'" class="position-relative kotak border border-danger p-3 mb-3" style="height: 150px">
                                        <div class="ribbon-wrapper ribbon-md">
                                            <div class="ribbon bg-danger">
                                                Tahap 3
                                            </div>
                                        </div>
                                        <p style="height: 50px" class="mb-0">{{data.name}}</p>
                                        <small v-for="(item, i) in data.stase_log_ongoing" :key="i">
                                            <span v-if="item.student">{{item.student.name}}</span><br></small>
                                    </div>
                                    <div v-if="data.desc === 'referat'" class="position-relative kotak border border-warning p-3 mb-3" style="height: 150px">
                                        <div class="ribbon-wrapper ribbon-md">
                                            <div class="ribbon bg-warning">
                                                Referat
                                            </div>
                                        </div>
                                        <p style="height: 50px" class="mb-0">{{data.name}}</p>
                                        <small v-for="(item, i) in data.stase_log_ongoing" :key="i">
                                            <span v-if="item.student">{{item.student.name}}</span><br></small>
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
        data(){
          return {
              dataContent:[],
              activities:{},
              cards:{},
              login:{
                  email:'unt@mail.com',
                  password: '123456'
              }
          }
        },
        methods:{
            loadData(){
                axios.get('/sadmin/dashboard-stase')
                .then((response)=>{
                    this.dataContent = response.data
                })
            },
            loadCard(){
                axios.get('/sadmin/dashboard-card')
                    .then((res)=>{
                        this.cards = res.data
                    })
            },
            loadAct(){
                axios.get('/sadmin/dashboard-activities')
                    .then((res)=>{
                        this.activities = res.data
                    })
            },
        },
        created(){
            if( this.$gate.isPerki() ){
                this.$router.push("/cmss/perki-page")
            }
            this.loadData();
            this.loadCard();
            // this.loadAct();
        },
        mounted() {
        }
    }
</script>
<style>
    .kotak{
        overflow-y: auto;
        overflow-x: hidden;
    }
</style>
