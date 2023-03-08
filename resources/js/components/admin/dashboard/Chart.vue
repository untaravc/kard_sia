<!--<template>-->
<!--    <div>-->
<!--        <vue-loader :active.sync="is_loading" :can-cancel="false" loader="dots" :is-full-page="false"></vue-loader>-->
<!--        <line-chart  :chart-data="datacollection" :height="120"></line-chart>-->
<!--    </div>-->
<!--</template>-->

<!--<script>-->
<!--    import LineChart from './LineChart'-->
<!--    export default {-->
<!--        components: {-->
<!--            LineChart-->
<!--        },-->
<!--        data(){-->
<!--            return {-->
<!--                is_loading:false,-->
<!--                datacollection: {-->
<!--                    labels: [],-->
<!--                    datasets: [-->
<!--                        {-->
<!--                            label: 'Masuk',-->
<!--                            backgroundColor: '#faf8ff00',-->
<!--                            borderColor: '#27cb2d',-->
<!--                            data: []-->
<!--                        },-->
<!--                        {-->
<!--                            label: 'Tidak masuk',-->
<!--                            backgroundColor: '#faf8ff00',-->
<!--                            borderColor: '#e3d92a',-->
<!--                            data: []-->
<!--                        },-->
<!--                        {-->
<!--                            label: 'Belum absen',-->
<!--                            backgroundColor: '#faf8ff00',-->
<!--                            borderColor: 'rgba(112,112,112,0.74)',-->
<!--                            data: []-->
<!--                        },-->
<!--                    ]-->
<!--                },-->

<!--            }-->
<!--        },-->
<!--        methods: {-->
<!--            fillLabel(){-->
<!--                this.is_loading = true;-->
<!--                axios.get('/sadmin/dashboard-chart')-->
<!--                    .then((data)=>{-->
<!--                        var ln = Object.keys(data.data).length;-->
<!--                        for(var i = 1; i<=ln; i++){-->
<!--                            this.datacollection.labels.push(data.data[i].date.slice(-2));-->
<!--                            if (data.data[i].presences != undefined){-->
<!--                                this.datacollection.datasets[0].data.push(-->
<!--                                    parseInt(data.data[i].presences.on)-->
<!--                                );-->
<!--                                this.datacollection.datasets[1].data.push(-->
<!--                                    parseInt(data.data[i].presences.off)-->
<!--                                );-->
<!--                                this.datacollection.datasets[2].data.push(-->
<!--                                    parseInt(data.data[i].residents) - -->
<!--                                    parseInt(data.data[i].presences.total)-->
<!--                                );-->
<!--                            } else{-->
<!--                                this.datacollection.datasets[0].data.push(0);-->
<!--                                this.datacollection.datasets[1].data.push(0);-->
<!--                                this.datacollection.datasets[2].data.push(0);-->
<!--                            }-->
<!--                        }-->
<!--                        this.is_loading = false;-->
<!--                    }).catch(()=>{-->
<!--                        this.is_loading = false;-->
<!--                })-->
<!--            }-->
<!--        },-->
<!--        created () {-->
<!--            this.fillLabel()-->
<!--        },-->
<!--        mounted() {-->
<!--        },-->
<!--        watch:{-->
<!--            'datacollection.labels': function (val) {-->
<!--                if(val){-->
<!--                    Fire.$emit('loadChart');-->
<!--                }-->
<!--            }-->
<!--        }-->
<!--    }-->
<!--</script>-->

<!--<style lang="css">-->
<!--    .small {-->
<!--        max-width: 800px;-->
<!--        /* max-height: 500px; */-->
<!--        margin:  50px auto;-->
<!--    }-->
<!--</style>-->

<template>
    <div class="small">
        <line-chart :chart-data="datacollection"></line-chart>
        <button @click="fillData()">Randomize</button>
    </div>
</template>

<script>
import LineChart from './LineChart'

export default {
    components: {
        LineChart
    },
    data () {
        return {
            datacollection: null
        }
    },
    mounted () {
        this.fillData()
    },
    methods: {
        fillData () {
            this.datacollection = {
                labels: [this.getRandomInt(), this.getRandomInt()],
                datasets: [
                    {
                        label: 'Data One',
                        backgroundColor: '#f87979',
                        data: [this.getRandomInt(), this.getRandomInt()]
                    }, {
                        label: 'Data One',
                        backgroundColor: '#f87979',
                        data: [this.getRandomInt(), this.getRandomInt()]
                    }
                ]
            }
        },
        getRandomInt () {
            return Math.floor(Math.random() * (50 - 5 + 1)) + 5
        }
    }
}
</script>

<style>
.small {
    max-width: 600px;
    margin:  150px auto;
}
</style>
