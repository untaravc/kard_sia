<template>
    <div>
        
    </div>
</template>


<script>
export default {
    data() {
        return {
            base_url: '/sadmin/timeline',
        }
    },
    methods: {
        locatorButtonPressed() {
            navigator.geolocation.getCurrentPosition(
                position => {
                    console.log(position.coords.latitude);
                    console.log(position.coords.longitude);
                },
                error => {
                    console.log(error.message);
                },
            )
        }
    },
    created() {
        this.locatorButtonPressed();
    }
}
</script>