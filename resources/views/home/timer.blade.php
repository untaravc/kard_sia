<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Timer</title>
    <script src="https://unpkg.com/vue@3"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <style>
        .counter-box {
            height: 400px;
            width: 400px;
            font-weight: 700;
        }

        .counter-hour {
            background-color: #000091;
            color: #fff;
        }

        .counter-minute {
            background-color: #c05300;
            color: #fff;
        }

        .message {
            width: 70vw;
            height: 100%;
            vertical-align: center;
            background-color: #730101;
            color: #fff;
            font-size: 30px;
        }

        .green {
            background-color: greenyellow;
        }
    </style>
</head>
<body>
<div id="app" style="overflow: hidden">
    <div class="row" :class="green_mode ? 'green' : ''">
        <div class="col-12">
            <div style="height: 10vh; text-align: center"
                 class="d-flex justify-content-around align-items-center">
                <div class="d-flex align-items-center justify-content-center message">
                    <b style="line-height: 1em">
                        DIMULAI
                        <br>
                        <span style="color: #f1f100">@{{ timer.start_at.substring(11,16) }}</span>
                    </b>
                </div>
                <div class="d-flex align-items-center justify-content-center message">
                    <b style="line-height: 1em">
                        SAAT INI <br>
                        <span style="color: #f1f100">@{{ timer.this_time.substring(11,16) }}</span>
                    </b>
                </div>
                <div class="d-flex align-items-center justify-content-center message">
                    <b style="line-height: 1em">
                        PESERTA KE
                        <br>
                        <span style="color: #f1f100">@{{ timer.order }}</span>
                    </b>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div style="height: 70vh;" class="d-flex justify-content-center align-items-center">
                <div class="counter-hour mx-2 counter-box d-flex justify-content-center align-items-center">
                    <div class="text-center">
                        <div style="font-size: 300px; line-height: 1em">
                            @{{time_countdown.minute}}
                        </div>
                        <div style="font-size: 60px">menit</div>
                    </div>
                </div>
                <div class="counter-minute mx-2 counter-box d-flex justify-content-center align-items-center">
                    <div class="text-center">
                        <div style="font-size: 300px; line-height: 1em">
                            @{{ time_countdown.second }}
                        </div>
                        <div style="font-size: 60px">detik</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div style="height: 10vh; text-align: center"
                 class="d-flex justify-content-center align-items-center">
                <div class="d-flex align-items-center justify-content-center message">
                    <b>@{{ timer.text }}</b>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div style="height: 10vh; text-align: center"
                 class="d-flex justify-content-center align-items-center">
                <button @click="play" class="btn btn-danger">
                    <span v-if="!sound">Turn on Sound</span>
                    <span v-if="sound">Check Sound</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script
    src="https://code.jquery.com/jquery-3.6.0.slim.min.js"
    integrity="sha256-u7e5khyithlIdTpu22PHhENmPcRdFiHRjhAuHcs05RI="
    crossorigin="anonymous"></script>
<script>
    const {createApp} = Vue
    createApp({
        data() {
            return {
                green_mode: false,
                beep_started: false,
                beepFunction: null,
                sound: false,
                timer: {
                    start_at: '',
                    text: '',
                    this_time: '',
                    order: '',
                    countdown: '',
                    diff_min: '',
                    transition_time: true,
                    reminder: 121,
                },
            }
        },
        methods: {
            loadTimer() {
                let r = this.getParameterByName('r')
                axios.get('/timer-data?r='+r)
                    .then(({data}) => {
                        this.timer = data.result;
                        this.checkTransition()
                    })
            },
            checkTransition() {
                let status = this.timer.transition_time;
                if (status) {
                    if (!this.beep_started) {
                        this.beepFunction = setInterval(() => {
                            this.green_mode = !this.green_mode;
                        }, 400)
                        this.beep_started = true;
                    }
                } else {
                    clearInterval(this.beepFunction);
                    this.beep_started = false;
                    this.green_mode = false
                }
            },
            play(type = 'start') {
                let audio = '';
                switch (type) {
                    case 'finish':
                        audio = new Audio('/assets/sound/finish.mp3');
                        break;
                    case 'min2':
                        audio = new Audio('/assets/sound/two_minute.mp3');
                        break;
                    default:
                        audio = new Audio('/assets/sound/start.mp3');
                }
                audio.play();
                this.sound = true;
            },
            getParameterByName(name, url = window.location.href) {
                name = name.replace(/[\[\]]/g, '\\$&');
                var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
                    results = regex.exec(url);
                if (!results) return null;
                if (!results[2]) return '';
                return decodeURIComponent(results[2].replace(/\+/g, ' '));
            }
        },
        created() {
            this.loadTimer();
            setInterval(() => {
                this.timer.countdown--
                this.checkTransition();

                if (this.timer.countdown === 2 && !this.timer.transition_time) {
                    this.play('finish')
                }
                if (this.timer.countdown === this.timer.reminder) {
                    this.play('min2')
                }
            }, 1000)

            setInterval(() => {
                this.loadTimer();
            }, 5000)
        },
        mounted() {
            setTimeout(() => {
                if (!this.sound) {
                    alert('Klik tombol "sound" untuk mengkatifkan suara!')
                }
            }, 3000)
        },
        computed: {
            time_countdown() {
                let minutes = this.timer.countdown;

                let status = this.timer.transition_time;
                if(status){
                    return {
                        minute: '00',
                        second: '00'
                    }
                }

                return {
                    minute: Math.floor(minutes / 60),
                    second: minutes % 60
                }
            }
        },
    }).mount('#app')
</script>
</body>
</html>
