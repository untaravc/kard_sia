<template>
    <i>
        <span v-if="document.category === 'cv'" class="badge badge-secondary">{{document.category}}</span>
        <span v-if="document.category === 'ktp'" class="badge badge-success">{{document.category}}</span>
        <span v-if="document.category === 'permission'" class="badge badge-danger">{{document.category}}</span>
        <span v-if="document.category === 'written_exam'" class="badge badge-dark">{{document.category}}</span>
        <span v-if="document.category === 'psychology'" class="badge badge-primary">{{document.category}}</span>
        <span v-if="document.category === 'health'" class="badge badge-info">{{document.category}}</span>
        <span v-if="document.category === 'interview'" class="badge badge-warning`">{{document.category}}</span>
        <span v-if="document.category === 'journal_reading'" class="badge badge-pill">{{document.category}}</span>
    </i>
</template>

<script>
export default {
    props:[
        'document',
    ],
    mounted() {

    }
}
</script>
