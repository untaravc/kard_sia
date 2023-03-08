<template>

</template>


<script>
export default {
    data() {
        return {
            base_url: '/sadmin/task-details',
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