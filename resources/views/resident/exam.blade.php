<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css"
          integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">

    <title>Cardio Proctor</title>
    <style>
        .block {
            position: fixed;
            width: 100vw;
            height: 100vh;
            background-color: rgba(76, 76, 76, 0.42);
            display: none;
            padding: 0;
            margin: 0;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .display-cover {
            padding: 5px;
            border-radius: 5px;
            border: 1px solid grey;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo01"
            aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
        <a class="navbar-brand" href="#">CardioProctor</a>
    </div>
</nav>
<div class="container">
    <div class="row">
        <div class="col-md-9">
            <div class="text-center">
                <iframe src="{{$exam['link']}}"
                        width="100%" style="height: 92vh" frameborder="0" marginheight="0" marginwidth="0">
                </iframe>
            </div>
        </div>
        <div class="col-md-3">
            <div class="display-cover">
                <div class="controls text-center">
                    <button class="btn btn-secondary play" title="Play">
                        Buka Kamera
                    </button>
                    {{--                    <button class="btn btn-info d-none" title="Pause"></button>--}}
                </div>
                <video style="width: 100%" autoplay></video>
                <canvas class="d-none"></canvas>
                <div class="video-options d-none">
                    <select name="" id="" class="custom-select">
                        <option value="">Select camera</option>
                    </select>
                </div>
            </div>
            <div class="display-cover mt-2 text-center">
                <b>Time : <span id="timer"></span></b>
            </div>
            <button class="btn btn-success mt-2" id="finish-test" style="width: 100%">
                Finish Test
            </button>
            <div class="display-cover mt-2">
                <b>Petunjuk</b>
                <br>
                Klik "Finish Test" setelah "Submit" pekerjaan.
            </div>
            <div class="display-cover mt-2 d-none" style="border: 1px solid red" id="note">

            </div>
        </div>
        <div class="block">
            <div style="height: 100vh;" class="container d-flex justify-content-center align-items-center">
                <div class="text-center">
                    <h4 class="text-white">Mendeteksi pergerakan layar</h4>
                    <button class="btn btn-warning" id="reload-page">Mulai Ulang</button>
                </div>
            </div>
        </div>
    </div>
</div>
    <!-- Optional JavaScript; choose one of the two! -->
<script>
    // let start_at = '2022-03-02 08:28:22';
    let start_at = "{{ date('Y-m-d H:i:s', strtotime($student_exam['timer'] . '+' . $exam['duration'] . ' minutes')) }}";
    let e_token = "{{ $exam['token'] }}";
</script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
            integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
            crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns"
            crossorigin="anonymous"></script>

    {{--Camera--}}
    <script src="/js/main/exam.js"></script>
</body>
</html>