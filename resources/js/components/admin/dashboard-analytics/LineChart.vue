<script>
import {Line, mixins} from 'vue-chartjs'
const {reactiveProp} = mixins

export default {
    extends: Line,
    mixins: [reactiveProp],
    props: ['options'],
    data(){
        return {
            gradient: null,
            gradient2: null
        }
    },
    mounted() {
        this.gradient = this.$refs.canvas.getContext('2d').createLinearGradient(0, 0, 0, 450)
        this.gradient2 = this.$refs.canvas.getContext('2d').createLinearGradient(0, 0, 0, 450)

        this.gradient.addColorStop(0, 'rgba(77,103,248,0.8)')
        this.gradient.addColorStop(0.25, 'rgb(77,103,248,0.3)');
        this.gradient.addColorStop(0.5, 'rgb(77,103,248,0.1)');

        this.gradient2.addColorStop(0, 'rgba(248,77,77,0.3)')
        this.gradient2.addColorStop(0.25, 'rgba(248,77,77,0.2)');
        this.gradient2.addColorStop(0.5, 'rgba(248,77,77,0.1)');

        Fire.$on('loadChart', () => {
            this.chartData.datasets[0].backgroundColor = this.gradient;
            this.chartData.datasets[1].backgroundColor = this.gradient2;
            this.renderChart(this.chartData, this.options);
        });
    }
}
</script>