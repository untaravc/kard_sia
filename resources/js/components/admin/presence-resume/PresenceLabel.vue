<template>
    <span>
    <i v-if="pres.presence
        && pres.presence.status === 'on'
        && pres.presence.checkin_data
        && pres.presence.checkin_data.accuracy < 200
        && pres.presence.checkin_data.accuracy !== null
        && pres.presence.checkin_data.distance < 500"
       class="fa fa-check-circle text-success"></i>

    <span v-if="pres.presence
        && pres.presence.status === 'on'
        && pres.presence.checkin_data.accuracy < 200
        && pres.presence.checkin_data.distance > 499"
          title="Study from home."
          class="badge badge-secondary">sfh</span>
    <span v-if="pres.presence
        && pres.presence.status === 'out'"
          class="badge badge-warning">duty</span>
    <span v-if="pres.presence
        && pres.presence.status === 'off'"
          class="badge badge-danger">izin</span>

    <i v-if="pres.presence
        && pres.presence.checkin_data
        && (pres.presence.checkin_data.accuracy == null
            || pres.presence.checkin_data.distance == null)"
       title="Gagal mendapatkan lokasi."
       class="fa fa-question-circle"></i>

    <i v-if="pres.presence
        && pres.presence.checkin_data
        && pres.presence.checkin_data.accuracy > 199"
       title="Low accuracy"
       class="fa fa-mobile-alt"></i>


    </span>
</template>
<script>
export default {
    props: ['pres']
}
</script>