<script>
    import { Line, mixins } from 'vue-chartjs'
    const { reactiveProp } = mixins;

    export default {
        extends: Line,
        mixins: [reactiveProp],
        props: ['options'],
        mounted () {
            Fire.$on('loadChart', () => {
                this.renderChart(this.chartData, this.options);
            });
            window.setTimeout(()=>{

            }, 2000);
        }
    }
</script>
