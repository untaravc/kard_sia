<template>
    <div class="text-center">
        <h5>Grafik Pengguna Presensi Harian</h5>
        <b>Residen Kardiologi dan Kedokteran Vaskular - FKKMK UGM</b>
        <line-chart class="mt-3" ref="canvas" :chart-data="dataCollection" :options="options" :height="150"></line-chart>
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
            dataCollection: {
                labels: [],
                datasets: [
                    {
                        label: 'Presensi',
                        borderColor: '#282be360',
                        borderWidth: 1,
                        data: [],
                    },
                    {
                        label: 'Tidak Presensi',
                        borderColor: 'rgba(255,0,37,0.38)',
                        borderWidth: 1,
                        data: [],
                    },
                ],

            },
            options:{
                legend:{
                    display: true,
                    position: 'bottom',
                    labels:{
                        boxWidth: 10,
                        padding: 10
                    },
                    rtl: false,
                },
            },
            loaded: false,
            is_loading: false,
        }
    },
    created() {
        this.loadData();
    },
    methods: {
        loadData(){
            axios.get('/sadmin/dashboard-chart')
            .then(({data})=>{
                let ln = Object.keys(data).length;
                for(let i = 1; i<=ln; i++){
                    this.dataCollection.labels.push(data[i].date.slice(-2));
                    if (data[i].presences != undefined){
                        this.dataCollection.datasets[0].data.push(
                            parseInt(data[i].presences.total)
                        );

                        this.dataCollection.datasets[1].data.push(
                            parseInt(data[i].residents - data[i].presences.total)
                        );
                    } else{

                    }
                }
                this.is_loading = false;
            })
        },
    },
    watch:{
        'dataCollection.labels': function (val) {
            if(val){
                Fire.$emit('loadChart');
            }
        }
    }
}
</script>

<style>
.small {
    margin: 0;
}
</style>