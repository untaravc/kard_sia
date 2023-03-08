<template>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Download Page</h1>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <div class="content">
            <div class="row">
                <div class="col-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 text-center">
                                    <h5>Penilaian</h5>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Dari:</label>
                                        <input type="date" class="form-control" v-model="penilaian_filter.start_date">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Ke:</label>
                                        <input type="date" class="form-control" v-model="penilaian_filter.end_date">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <a target="_blank"
                                       :href="`/sadmin/download/penilaian?start_date=` + penilaian_filter.start_date + '&end_date=' + penilaian_filter.end_date"
                                       class="btn btn-success" style="width: 100%">
                                        <i class="fa fa-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 text-center">
                                    <h5>Agenda</h5>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Dari:</label>
                                        <input type="date" class="form-control" v-model="activities_filter.start_date">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Ke:</label>
                                        <input type="date" class="form-control" v-model="activities_filter.end_date">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <a target="_blank"
                                       :href="`/sadmin/download/activities?start_date=` + activities_filter.start_date + '&end_date=' + activities_filter.end_date"
                                       class="btn btn-success" style="width: 100%">
                                        <i class="fa fa-download"></i> Download
                                    </a>
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
    data() {
        return {
            penilaian_filter: {
                start_date: '',
                end_date: '',
            },
            activities_filter: {
                start_date: '',
                end_date: '',
            }
        }
    },
    methods: {},
    created() {

    },
    mounted() {
    }
}
</script>
