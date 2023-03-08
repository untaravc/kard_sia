<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sia Presensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
    <style>
        @import url('https://fonts.googleapis.com/css?family=Montserrat');

        * {
            box-sizing: border-box;
        }

        body {
            background-color: #e9e9e9;
            font-family: Montserrat, sans-serif;
            display: flex;
            align-items: center;
            justify-content: start;
            flex-direction: column;
            min-height: 100vh;
            margin: 10px 0;
        }

        h3 {
            margin: 10px 0;
        }

        h6 {
            margin: 5px 0;
            text-transform: uppercase;
        }

        p {
            font-size: 14px;
            line-height: 21px;
        }

        .card-container {
            background-color: #073c64;
            border-radius: 5px;
            box-shadow: 0 10px 20px -10px rgba(0, 0, 0, 0.75);
            color: #B3B8CD;
            padding-top: 30px;
            position: relative;
            width: 350px;
            max-width: 100%;
            text-align: center;
        }

        .card-container .pro {
            color: #231E39;
            background-color: #FEBB0B;
            border-radius: 3px;
            font-size: 14px;
            font-weight: bold;
            padding: 3px 7px;
            position: absolute;
            top: 30px;
            left: 30px;
        }

        .card-container .round {
            border: 1px solid #03BFCB;
            border-radius: 50%;
            padding: 7px;
        }

        button.primary {
            background-color: #03BFCB;
            border: 1px solid #03BFCB;
            border-radius: 3px;
            color: #231E39;
            font-family: Montserrat, sans-serif;
            font-weight: 500;
            padding: 10px 25px;
        }

        button.primary.ghost {
            background-color: transparent;
            color: #02899C;
        }

        .skills {
            background-color: #073c64;
            text-align: left;
            padding: 15px;
            margin-top: 30px;
        }

        .skills ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
        }

        .skills ul li {
            border: 1px solid #073c64;
            border-radius: 2px;
            display: inline-block;
            font-size: 12px;
            margin: 0 7px 7px 0;
            padding: 7px;
        }

        footer {
            background-color: #222;
            color: #fff;
            font-size: 14px;
            bottom: 0;
            position: fixed;
            left: 0;
            right: 0;
            text-align: center;
            z-index: 999;
        }

        footer p {
            margin: 10px 0;
        }

        footer i {
            color: red;
        }

        footer a {
            color: #3c97bf;
            text-decoration: none;
        }

        .photo-container {
            background-repeat: no-repeat;
            height: 100px;
            width: 100px;
            margin-right: auto;
            margin-left: auto;
            background-position: center;
            background-size: cover;
        }

        .desc-container{
            margin: 15px 30px;
        }

        .submit-btn{
            min-width: 120px;
        }

        .form-control{
            display: block;
            width: 100%;
            height: calc(2.25rem + 2px);
            padding: .375rem .75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #495057;
            background-color: #e3e6ec;
            background-clip: padding-box;
            border: 1px solid #b3b9cd;
            border-radius: .25rem;
            box-shadow: inset 0 0 0 transparent;
            transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
        }

        #reload{
            color: orange;
            text-decoration: underline;
            cursor: pointer;
            margin-top: 30px;
        }
    </style>
</head>
<body>
<div id="app">
    <div class="card-container">
        <div id="display-photo">
            <div class="round photo-container" id="bg-change"
                 style="background-image: url('https://dummyimage.com/100x100/efefef/2431e3.jpg');"></div>
        </div>
        <h3>{{$user['name']}}</h3>
        <small>{{$user['email']}}</small>
        <p>Presensi Harian Residen Kardiologi<br/> {{date('d M Y')}}</p>
            <div class="buttons">
                - Can only be accessed on Android OS and iOS -
            </div>

        <div class="skills" style="text-align: center">
{{--            <h6>Location</h6>--}}
{{--            <span id="lat">Pastikan GPS kamu menyala :)</span><span id="lng"></span> <br>--}}
{{--            <span id="distance"></span>--}}
{{--            <span id="distance_fail"></span> <br>--}}
{{--            <div id="reload">RELOAD</div>--}}
        </div>
    </div>
    {{--    <input id="fileInputElement" name="uploaded_file" type="file" multiple />--}}

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
            integrity="sha256-4+XzXVhsDmqanXGHaHvgh1gMQKX40OUvDEBTu8JcmNs=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/exif-js"></script>
    <script src="/js/ImageUploader.js"></script>
    <script>
        $("#input-button").on("click", function (e) {
            e.preventDefault();
            $("#input-photo").trigger("click")
        });

        $(".submit-btn").click(function (e) {
            e.preventDefault();
           $('#presensi').addClass('d-none');
           $('.this-spinner').removeClass('d-none');
           $('.loader-text').removeClass('d-none');

            window.navigator.geolocation
                .getCurrentPosition(function (val){
                    let current_latitude = val.coords.latitude;
                    let current_longitude = val.coords.longitude;
                    let distance = Math.floor(calcCrow(-7.7684412,110.3721119,current_latitude,current_longitude) * 1000 );
                    let accuracy = val.coords.accuracy;

                    $('#input-lat').val(current_latitude);
                    $('#input-lng').val(current_longitude);
                    $('#input-distance').val(distance);
                    $('#input-accuracy').val(accuracy);

                    $('#presence-form').submit();
                }, console.log);
        });

        var uploader = new ImageUploader({
            inputElement: document.getElementById('input-photo'),
            uploadUrl: '/api/image',
            onProgress: function (event) {
                console.log('progress')
                // $('#progress').text('Completed ' + event.done + ' files of ' + event.total + ' total.');
                // $('#progressbar').progressbar({ value: (event.done / event.total) * 100 })
            },
            onFileComplete: function (event, file) {
                console.log('file')
                // $('#fileProgress').append('Finished file ' + file.fileName + ' with response from server ' + event.target.status + '<br />');
            },
            onComplete: function (event) {
                console.log('complete')
                // $('#progress').text('Completed all ' + event.done + ' files!');
                // $('#progressbar').progressbar({ value: (event.done / event.total) * 100 })
            },
            maxWidth: 100,
            maxHeight: 100,
            maxSize: 0.4,
            quality: 0.50,
            timeout: 5000,
            scaleRatio: 0.3,
            autoRotate: false,
        });

        $(document).ready(function() {
            getLocation();
        });

        $('#reload').click(function (e){
            getLocation();
            $('#reload').text('RELOADED');
            setTimeout(function (){
                $('#reload').text('RELOAD');
            }, 3000);
        })

        function getLocation(){
            window.navigator.geolocation
                .getCurrentPosition(function (val){
                    let current_latitude = val.coords.latitude;
                    let current_longitude = val.coords.longitude;
                    // sardjito -7.7684412,110.3721119
                    let distance = Math.floor(calcCrow(-7.7684412,110.3721119,current_latitude,current_longitude) * 1000 );
                    let accuracy = val.coords.accuracy;

                    $('.skills #lat').text(current_latitude + ', ')
                    $('.skills #lng').text(current_longitude + ', ')

                    if(accuracy < 500){
                        $('.skills #distance_fail').text('')
                        $('.skills #distance').text(distance + ' m dari RSS')
                    }else{
                        $('.skills #distance').text('')
                        $('.skills #distance_fail').html('Akurasi GPS rendah, pastikan GPS menyala <a href="https://goo.gl/maps/wyZdgjDcozagsdq78" style="color: #fabb00">Periksa disini</a>')
                    }
                }, console.log);
        }

        function calcCrow(lat1, lon1, lat2, lon2)
        {
            var R = 6371; // km
            var dLat = toRad(lat2 - lat1);
            var dLon = toRad(lon2 - lon1);
            var lat1 = toRad(lat1);
            var lat2 = toRad(lat2);

            var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.sin(dLon/2) * Math.sin(dLon/2) * Math.cos(lat1) * Math.cos(lat2);
            var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            var d = R * c;
            return d;
        }

        // Converts numeric degrees to radians
        function toRad(Value)
        {
            return Value * Math.PI / 180;
        }
    </script>
</div>
</body>
</html>
