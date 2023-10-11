<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sia Presensi</title>
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
    </style>
</head>
<body>
<div id="app">
    <div class="card-container">
        <span class="pro">Online</span>
        <div id="display-photo">
            <div class="round photo-container" id="bg-change"
                 style="background-image: url('https://randomuser.me/api/portraits/women/79.jpg');"></div>
        </div>
        <h3>Eli Sugigi</h3>
        <small>eli@sugigi.com</small>
        <p>Presensi Harian Residen Kardiologi<br/> 26 Feb 2021</p>
        <form action="" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="buttons">
                <input type="file" id="input-photo" accept="image/*" name="photo"
                       style="display: none" capture="camera">
                <button class="primary ghost" id="input-button">
                    Ambil Foto
                </button>
                <button class="primary" type="submit">
                    Presensi
                </button>
            </div>
        </form>
        <div class="skills" style="text-align: center">
            <h6>Location</h6>
            <span>-7.029383939, 110.0939049</span> <br>
            <span>630 m dari lokasi</span>
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
        });

    </script>
</div>
</body>
</html>
