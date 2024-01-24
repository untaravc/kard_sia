<template>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-8">
                        <h1>Laporan Kegiatan Harian </h1>
                        <div>Departemen Kardiologi dan Kedokteran Vaskular, FK-KMK, UGM</div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center justify-content-end">
                        <input type="date" style="max-width: 180px" v-model="filter.date" class="form-control float-right">
                    </div>
                </div>
            </div>
        </section>

        <div class="content">
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3 rounded-lg" style="box-shadow: none">
                        <div class="p-4 d-flex justify-content-between border-bottom">
                            <div>
                                <div class="text-success text-bold">Sejumlah {{activity_presence}} dari {{students.length}} telah presensi agenda ilmiah.</div>
                                <div class="text-xs">
                                    <span v-for="(not_presence, idx) in activity_not_presence_ids" v-if="idx < 5">{{getStudentName(not_presence)}}, </span>
                                    <span v-if="activity_not_presence > 5">dan {{activity_not_presence - 5}} lainya</span>
                                    <span class="text-danger">tidak mengikuti agenda ilmiah</span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end align-items-center px-1" style="min-width: 55px;">
                                <div class="text-lg font-weight-bold">
                                    <span class="text-danger" v-if="Math.round(activity_presence * 100 / student_count) < 50">{{Math.round(activity_presence * 100 / student_count)}} %</span>
                                    <span class="text-warning" v-else-if="Math.round(activity_presence * 100 / student_count) < 75">{{Math.round(activity_presence * 100 / student_count)}} %</span>
                                    <span class="text-success" v-else>{{Math.round(activity_presence * 100 / student_count)}} %</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body border-bottom" v-for="activity in activities">
                            <div class="d-flex">
                                <div class="bg-warning text-black text-xs px-2 rounded-lg"><b>{{activity.name}}</b></div>
                            </div>
                            <div>
                                <div class="text-black text-sm font-weight-bold">{{activity.title}}</div>
                            </div>
                            <div class="d-flex justify-content-between text-sm">
                                <div>{{activity.place | truncate(40)}}</div>
                                <div>{{activity.start_date | formatDayTime}}</div>
                            </div>
                            <div class="text-sm font-italic pt-2">
                                <div>Hadir: <b>{{activity.activity_students_count}}</b> Residen</div>
                                <div class="bg-success" :style="'height: 3px; width: ' + activity.activity_students_count * 100 / students.length +'%'"></div>
                                <div class="text-xs">
                                    <span v-for="(student, i) in activity.activity_students" v-if="i < 5">
                                        {{getStudentName(student.student_id)}}, </span>
                                    <span v-if="activity.activity_students.length > 5">dan {{activity.activity_students.length - 5}} lainnya</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-3" style="box-shadow: none">
                        <div class="p-3 d-flex justify-content-between">
                            <div>
                                <div class="text-primary font-weight-bold">Kehadiran harian: <b>{{presences.length}}</b> Residen</div>
                                <div class="text-sm">
                                    <span v-for="(stud, idx) in presences" v-if="idx < 5">
                                        {{getStudentName(stud.student_id)}},
                                    </span>
                                    <span v-if="presences.length > 5">
                                        dan {{presences.length - 5}} lainya telah presensi harian.
                                    </span>
                                </div>
                                <div class="bg-success" :style="'height: 3px; width: ' + presences.length * 100 / students.length +'%'"></div>
                                <div class="text-xs">
                                    <span v-for="(student, i) in not_presence_ids" v-if="i < 10">
                                        {{getStudentName(student)}}, </span>
                                    <span v-if="not_presence_ids.length > 10">dan {{not_presence_ids.length - 10}} lainnya <b>tidak presensi harian</b></span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end align-items-center px-1" style="min-width: 55px;">
                                <div class="text-lg font-weight-bold">
                                    <span class="text-danger" v-if="Math.round(presences.length * 100 / student_count) < 50">{{Math.round(presences.length * 100 / student_count)}} %</span>
                                    <span class="text-warning" v-else-if="Math.round(presences.length * 100 / student_count) < 75">{{Math.round(presences.length * 100 / student_count)}} %</span>
                                    <span class="text-success" v-else>{{Math.round(presences.length * 100 / student_count)}} %</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card" style="box-shadow: none">
                        <div class="p-3 d-flex justify-content-between border-bottom">
                            <div>
                                <div>
                                    <div class="text-md text-info font-weight-bold">Pengisian Log Book: Hari Ini</div>
                                </div>
                                <div>
                                    Sejumlah <b>{{log_today.students.length}}</b> residen telah mencatatkan
                                    <b>{{log_today.count}}</b> data logbook hari ini.
                                </div>
                                <div class="bg-success" :style="'height: 3px; width: ' + (log_today.students.length * 100 / student_count) + '%'"></div>
                                <div class="text-xs">
                                    <span v-for="(student, i) in log_today.students" v-if="i < 5">
                                        {{getStudentName(student)}}, </span>
                                    <span v-if="log_today.students.length > 5">dan {{log_today.students.length - 5}} lainnya.</span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end align-items-center px-1" style="min-width: 55px;">
                                <div class="text-lg font-weight-bold">
                                    <span class="text-danger" v-if="Math.round(log_today.students.length * 100 / student_count) < 50">{{Math.round(log_today.students.length * 100 / student_count)}} %</span>
                                    <span class="text-warning" v-else-if="Math.round(log_today.students.length * 100 / student_count) < 75">{{Math.round(log_today.students.length * 100 / student_count)}} %</span>
                                    <span class="text-success" v-else>{{Math.round(log_today.students.length * 100 / student_count)}} %</span>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 d-flex justify-content-between border-bottom">
                            <div>
                                <div>
                                    <div class="text-md text-info font-weight-bold">Pengisian Log Book: 7 hari terakhir</div>
                                </div>
                                <div>
                                    Sejumlah <b>{{log_week.students.length}}</b> residen telah mencatatkan
                                    <b>{{log_week.count}}</b> data logbook 7 hari terakhir.
                                </div>
                                <div class="bg-success" :style="'height: 3px; width: ' + (log_week.students.length * 100 / student_count) + '%'"></div>
                                <div class="text-xs">
                                    <span v-for="(student, i) in log_week.students" v-if="i < 5">
                                        {{getStudentName(student)}}, </span>
                                    <span v-if="log_week.students.length > 5">dan {{log_week.students.length - 5}} lainnya.</span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end align-items-center px-1" style="min-width: 55px;">
                                <div class="text-lg font-weight-bold">
                                    <span class="text-danger" v-if="Math.round(log_week.students.length * 100 / student_count) < 50">{{Math.round(log_week.students.length * 100 / student_count)}} %</span>
                                    <span class="text-warning" v-else-if="Math.round(log_week.students.length * 100 / student_count) < 75">{{Math.round(log_week.students.length * 100 / student_count)}} %</span>
                                    <span class="text-success" v-else>{{Math.round(log_week.students.length * 100 / student_count)}} %</span>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 d-flex justify-content-between border-bottom">
                            <div>
                                <div>
                                    <div class="text-md text-info font-weight-bold">Pengisian Log Book: 30 hari terakhir</div>
                                </div>
                                <div>
                                    Sejumlah <b>{{log_month.students.length}}</b> residen telah mencatatkan
                                    <b>{{log_month.count}}</b> data logbook 30 hari terakhir.
                                </div>
                                <div class="bg-success" :style="'height: 3px; width: ' + (log_month.students.length * 100 / student_count) + '%'"></div>
                                <div class="text-xs">
                                    <span v-for="(student, i) in log_month.students" v-if="i < 5">
                                        {{getStudentName(student)}}, </span>
                                    <span v-if="log_month.students.length > 5">dan {{log_month.students.length - 5}} lainnya.</span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end align-items-center px-1" style="min-width: 55px;">
                                <div class="text-lg font-weight-bold">
                                    <span class="text-danger" v-if="Math.round(log_month.students.length * 100 / student_count) < 50">{{Math.round(log_month.students.length * 100 / student_count)}} %</span>
                                    <span class="text-warning" v-else-if="Math.round(log_month.students.length * 100 / student_count) < 75">{{Math.round(log_month.students.length * 100 / student_count)}} %</span>
                                    <span class="text-success" v-else>{{Math.round(log_month.students.length * 100 / student_count)}} %</span>
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
            base_url: '/sadmin/daily-report',
            dataContent: {},
            activities: [],
            students: [],
            presences: [],
            isLoading: false,
            student_count: 0,
            activity_presence: 0,
            activity_not_presence: 0,
            activity_not_presence_ids: [],
            not_presence_ids: [],
            student_log_today: [],
            student_log_week: [],
            student_log_month: [],
            log_today: {
                students: [],
                count: 0,
            },
            log_week: {
                students: [],
                count: 0,
            },
            log_month: {
                students: [],
                count: 0,
            },
            filter: {
                date: '2023-08-29',
            }
        }
    },
    methods: {
        loadDataContent(page = 1) {
            this.filter.page = page;
            this.isLoading = true;
            axios.get(this.base_url, {params: this.filter})
                .then(({data}) => {
                    this.isLoading = false;
                    this.activities = data.result.activities;
                    this.students = data.result.students;
                    this.presences = data.result.presences;
                    this.student_log_today = data.result.student_log_today;
                    this.student_log_week = data.result.student_log_week;
                    this.student_log_month = data.result.student_log_month;
                    this.student_count = data.result.students.length;

                    this.computePresence()
                    this.computeDaily()
                    this.computeLog()
                }).catch(()=>{
                this.isLoading = false;
            })
        },
        resetData() {
            this.filter.reset();
            this.loadDataContent();
        },
        getStudentName(id){
            let students = this.students;

            let student = students.find(item =>{
                return item.id === id
            })

            if(student){
                return student.name
            }
        },
        computePresence(){
            let student_ids = [];
            this.students.forEach(item=>{
                student_ids.push(item.id)
            })

            this.activities.forEach(act => {
                act.activity_students.forEach(stud => {
                    let index = student_ids.indexOf(stud.student_id);
                    if (index > -1) {
                        student_ids.splice(index, 1);
                    }
                })
            })

            this.activity_not_presence_ids = student_ids
            this.activity_not_presence = student_ids.length
            this.activity_presence = this.student_count - student_ids.length
        },
        computeDaily(){
            let student_ids = [];
            this.students.forEach(item=>{
                student_ids.push(item.id)
            })

            this.presences.forEach(pres => {
                let index = student_ids.indexOf(pres.student_id);
                if (index > -1) {
                    student_ids.splice(index, 1);
                }
            })

            this.not_presence_ids = student_ids
        },
        computeLog(){
            this.log_today.students = []
            this.log_today.count = 0
            this.student_log_today.forEach(item => {
                this.log_today.students.push(item.student_id)
                this.log_today.count += item.total
            })

            this.log_week.students = []
            this.log_week.count = 0
            this.student_log_week.forEach(item => {
                this.log_week.students.push(item.student_id)
                this.log_week.count += item.total
            })

            this.log_month.students = []
            this.log_month.count = 0
            this.student_log_month.forEach(item => {
                this.log_month.students.push(item.student_id)
                this.log_month.count += item.total
            })
        }
    },
    created() {
        const date = new Date();
        const month_number = date.getMonth() + 1
        let month = month_number < 10 ? '0' + month_number : month_number
        let day = date.getDate() < 10 ? '0'+date.getDate() : date.getDate()
        this.filter.date = date.getFullYear() + '-' + month + '-' + day
    },
    watch: {
        'filter.date': function (val) {
            if (val.length > 3 || val.length === 0) {
                this.loadDataContent();
            }
        },
    }
}
</script>
