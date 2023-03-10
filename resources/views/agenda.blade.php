<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sia Presensi</title>
    <style>
        @import url("https://fonts.googleapis.com/css?family=Montserrat:400,400i,700");

        body {
            font-size: 16px;
            color: #404040;
            font-family: Montserrat, sans-serif;
            background-image: linear-gradient(to bottom right, #002398 0% 65%, #2b56bf 95% 100%);
            background-position: center;
            background-attachment: fixed;
            margin: 0;
            padding: 2rem 0;
            display: grid;
            place-items: center;
            box-sizing: border-box;
        }

        .card {
            background-color: #fff;
            max-width: 360px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border-radius: 2rem;
            box-shadow: 0px 1rem 1.5rem rgba(0, 0, 0, 0.5);
        }

        .card .banner {
            background-image: url("https://sia.kardiologi-fkkmk.com/assets/images/fkugm.jpg");
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            height: 11rem;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            box-sizing: border-box;
        }

        .card .banner svg {
            background-color: #fff;
            width: 8rem;
            height: 8rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.3);
            border-radius: 50%;
            transform: translateY(50%);
            transition: transform 200ms cubic-bezier(0.18, 0.89, 0.32, 1.28);
        }

        .card .banner svg:hover {
            transform: translateY(50%) scale(1.3);
        }

        .card .menu {
            width: 100%;
            height: 5.5rem;
            padding: 1rem;
            display: flex;
            align-items: flex-start;
            justify-content: flex-end;
            position: relative;
            box-sizing: border-box;
        }

        .card .menu .opener {
            width: 2.5rem;
            height: 2.5rem;
            position: relative;
            border-radius: 50%;
            transition: background-color 100ms ease-in-out;
        }

        .card .menu .opener:hover {
            background-color: #f2f2f2;
        }

        .card .menu .opener span {
            background-color: #404040;
            width: 0.4rem;
            height: 0.4rem;
            position: absolute;
            top: 0;
            left: calc(50% - 0.2rem);
            border-radius: 50%;
        }

        .card .menu .opener span:nth-child(1) {
            top: 0.45rem;
        }

        .card .menu .opener span:nth-child(2) {
            top: 1.05rem;
        }

        .card .menu .opener span:nth-child(3) {
            top: 1.65rem;
        }

        .card h2.name {
            text-align: center;
            padding: 0 2rem 0.5rem;
            margin: 0;
        }

        .card .title {
            color: #a0a0a0;
            font-size: 0.85rem;
            text-align: center;
            padding: 0 2rem 1.2rem;
        }

        .card .actions {
            padding: 0 2rem 1.2rem;
            display: flex;
            flex-direction: column;
            order: 99;
        }

        .card .actions .follow-info {
            padding: 0 0 1rem;
            display: flex;
        }

        .card .actions .follow-info h2 {
            text-align: center;
            width: 50%;
            margin: 0;
            box-sizing: border-box;
        }

        .card .actions .follow-info h2 a {
            text-decoration: none;
            padding: 0.8rem;
            display: flex;
            flex-direction: column;
            border-radius: 0.8rem;
            transition: background-color 100ms ease-in-out;
        }

        .card .actions .follow-info h2 a span {
            color: #1c9eff;
            font-weight: bold;
            transform-origin: bottom;
            transform: scaleY(1.3);
            transition: color 100ms ease-in-out;
        }

        .card .actions .follow-info h2 a small {
            color: #afafaf;
            font-size: 0.85rem;
            font-weight: normal;
        }

        .card .actions .follow-info h2 a:hover {
            background-color: #f2f2f2;
        }

        .card .actions .follow-info h2 a:hover span {
            color: #007ad6;
        }

        .card .actions .follow-btn button {
            color: inherit;
            font: inherit;
            font-weight: bold;
            cursor: pointer;
            background-color: #ffd01a;
            width: 100%;
            border: none;
            padding: 1rem;
            outline: none;
            box-sizing: border-box;
            border-radius: 1.5rem/50%;
            transition: background-color 100ms ease-in-out, transform 200ms cubic-bezier(0.18, 0.89, 0.32, 1.28);
        }

        .card .actions .follow-btn button:hover {
            background-color: #efb10a;
            transform: scale(1.1);
        }

        .card .actions .follow-btn button:active {
            background-color: #e8a200;
            transform: scale(1);
        }

        .card .desc {
            text-align: center;
            padding: 0 2rem 2.5rem;
            order: 100;
        }

    </style>
</head>
<body>

<div class="card">
    <div class="banner">
        <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
             x="0px" y="0px"
             viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
        <path style="fill:#F2F2F2;" d="M512,434.016v77.013H0v-77.013c0-20.373,12.48-38.731,31.456-46.08l107.957-42.453l84.267,92.597
            l26.443-53.867v-0.107L256,372.043l5.984,12.064l26.453,53.973l84.149-92.597l108.064,42.453
            C499.52,395.403,512,413.643,512,434.016"/>
            <path style="fill:#F8B64C;" d="M331.52,329.483h0.107v-43.84c-5.6,8.491-12.107,17.109-19.669,25.728l-10.453,11.52
            c-9.6,10.443-23.253,16.533-37.44,16.533h-16c-14.187,0-27.851-6.08-37.44-16.533l-10.453-11.52
            c-7.648-8.693-14.229-17.429-19.851-26.005v44.043l0.171,0.085l65.813,37.12l9.707,5.44l9.707-5.44L331.52,329.483z"/>
            <g style="opacity:0.1;">
                <g>
                    <path style="fill:#40596B;" d="M229.024,335.52c-0.064-0.021-0.128-0.043-0.192-0.064c-0.107-0.043-0.224-0.075-0.331-0.107
                    c-0.107-0.053-0.224-0.064-0.373-0.117l40.224,29.877l63.168-35.627h0.064l0.043-43.616c-0.107,0.16-14.997,19.669-19.669,25.483
                    l-10.453,11.531c-9.6,10.443-23.253,16.533-37.44,16.533h-16c-3.232,0-6.4-0.352-9.504-1.013
                    C235.275,337.771,232.107,336.789,229.024,335.52"/>
                </g>
            </g>
            <g>
                <path style="fill:#F8B64C;" d="M181.141,196.309c1.664,19.147-7.627,35.605-20.747,36.736
                c-13.141,1.152-25.141-13.451-26.837-32.597c-1.664-19.147,7.627-35.584,20.768-36.736
                C167.456,162.56,179.477,177.152,181.141,196.309"/>
                <path style="fill:#F8B64C;" d="M378.539,200.469c-1.664,19.147-13.685,33.739-26.816,32.587
                c-13.141-1.152-22.443-17.589-20.747-36.736c1.664-19.147,13.685-33.749,26.816-32.597
                C370.933,164.853,380.224,181.312,378.539,200.469"/>
            </g>
            <path style="fill:#FFD15C;" d="M364.853,196.373c-2.016,21.44-12.171,68.683-52.896,114.976l-10.453,11.52
            c-9.6,10.453-23.253,16.544-37.44,16.544h-16c-14.187,0-27.851-6.08-37.44-16.544l-10.453-11.52
            c-40.747-46.293-50.773-93.547-52.917-114.976c-2.88-29.547-1.707-67.093,0-76.8c9.707-56.853,58.357-92.16,108.811-92.16
            c50.443,0,99.083,35.307,108.789,92.16C366.56,129.28,367.733,166.837,364.853,196.373"/>
            <path style="fill:#40596B;" d="M172.896,54.368c0,0,0.747-71.819,105.163-48.875c0,0,51.808,16.277,85.141,64.427
            c0,0,31.275,45.419,14.272,83.627c-5.525,12.405-11.456,26.603-10.773,38.005c0,0-5.792-24.203-9.248-44.373
            c-2.891-16.896-10.165-32.725-21.333-45.717l-0.331-0.395c-18.08-20.939-46.379-30.731-73.141-23.829
            c-12.768,3.296-27.296,9.728-43.275,21.131c0,0-43.499-29.195-65.003,21.909c0,0-9.579,37.355-8.224,66.859
            C146.144,187.125,86.272,61.771,172.896,54.368"/>
            <path style="fill:#ACB3BA;" d="M398.603,375.349c-0.213-1.813-0.629-18.443-4.043-25.163c-5.771-11.317-16.107-18.88-28.587-20.8
            l-34.347-6.933v7.04h-0.107l-4.917,2.773l37.664,7.68c9.067,1.387,16.651,6.827,20.693,15.029
            c2.667,5.227,3.755,10.891,2.891,16.437l-0.224,2.773c-45.12,39.147-57.92,74.453-58.453,76.053
            c-3.392,10.133-3.189,18.133,0.544,23.893c4.171,6.507,10.976,7.253,11.829,7.36l1.067-10.667c-0.107,0-2.667-0.437-4.043-2.667
            c-1.707-2.784-1.504-7.893,0.747-14.411c0.107-0.224,6.4-17.589,25.6-41.077c5.333-2.667,11.296-3.189,17.067-1.493
            c5.547,1.707,10.229,5.451,13.12,10.443c2.123,30.837-2.56,48.971-2.667,49.077c-2.037,6.613-4.811,10.987-7.893,12.16
            c-2.453,1.077-4.704-0.107-4.704-0.213l-2.667,4.683l-2.773,4.619c0.437,0.32,3.733,2.133,8.107,2.133
            c1.813,0,3.755-0.32,5.771-1.173c6.4-2.443,11.317-8.853,14.496-19.093C403.211,472.213,413.12,434.976,398.603,375.349
             M384.437,402.976c-3.211-0.96-6.624-1.376-9.835-1.269c4.608-5.024,9.707-10.133,15.477-15.349
            c1.6,7.456,2.773,14.603,3.627,21.333C390.933,405.643,387.829,404.053,384.437,402.976"/>
            <g>
                <path style="fill:#F8B64C;" d="M350.933,478.037c-0.853,4.288-5.024,7.083-9.312,6.24c-4.267-0.843-1.227-16.384,3.061-15.541
                C348.971,469.579,351.776,473.749,350.933,478.037"/>
                <path style="fill:#F8B64C;" d="M368.224,483.371c-1.813,3.979-0.085,8.672,3.893,10.496c3.979,1.824,10.581-12.565,6.603-14.4
                C374.773,477.643,370.059,479.392,368.224,483.371"/>
            </g>
            <path style="fill:#ACB3BA;" d="M180.48,329.483l-0.171-0.085v-6.955l-23.061,4.747c-24.608,5.067-45.312,20.768-56.853,43.061
            c-11.52,22.315-12.352,48.299-2.251,71.296l9.771-4.288c-8.789-20.032-8.107-42.656,1.963-62.101
            c10.037-19.413,28.085-33.099,49.504-37.504l26.059-5.365L180.48,329.483z"/>
            <g>
                <polygon style="fill:#CDD6E0;" points="372.587,345.483 293.877,448.736 256,372.043 265.707,366.603 331.52,329.483
                331.627,329.483 331.733,329.376 	"/>
                <polygon style="fill:#CDD6E0;" points="139.413,345.483 218.251,448.736 256,372.043 246.293,366.603 180.48,329.483
                180.267,329.376 	"/>
            </g>
            <polygon style="fill:#334A5E;" points="218.251,511.029 239.787,461.963 230.496,448.309 223.691,438.069 250.123,384.107
            256,372.043 261.984,384.107 261.984,384.213 288.437,438.069 281.504,448.213 272.213,461.963 293.877,511.029 "/>
            <polygon style="fill:#2A4356;" points="288.437,438.069 281.504,448.213 256.107,396.256 230.496,448.309 223.691,438.069
            250.123,384.107 256,372.043 261.984,384.107 261.984,384.213 "/>
            <path style="fill:#FF7058;" d="M135.104,458.251c0,13.333-10.837,24.16-24.171,24.16s-24.149-10.827-24.149-24.16
            c0-13.333,10.816-24.149,24.149-24.149S135.104,444.917,135.104,458.251"/>
            <path style="fill:#F2F2F2;" d="M127.168,458.251c0,8.96-7.253,16.224-16.213,16.224c-8.981,0-16.245-7.275-16.245-16.224
            s7.275-16.224,16.245-16.224C119.915,442.027,127.168,449.291,127.168,458.251"/>
            <path style="fill:#ACB3BA;" d="M122.315,458.251c0,6.293-5.088,11.371-11.349,11.371c-6.293,0-11.392-5.088-11.392-11.371
            c0-6.272,5.109-11.36,11.392-11.36C117.227,446.891,122.315,451.979,122.315,458.251"/>
            <g>
            </g>
            <g>
            </g>
            <g>
            </g>
            <g>
            </g>
            <g>
            </g>
            <g>
            </g>
            <g>
            </g>
            <g>
            </g>
            <g>
            </g>
            <g>
            </g>
            <g>
            </g>
            <g>
            </g>
            <g>
            </g>
            <g>
            </g>
            <g>
            </g>
        </svg>
    </div>
    <div class="menu">
        {{--        <div class="opener"><span></span><span></span><span></span></div>--}}
    </div>
    <h2 class="name">{{$user->name}}</h2>
    <div class="title">Online Presence</div>
    <div class="actions">
        <form action="" method="POST">
            @csrf
            <div class="follow-info">
                <h2><a href="#"><span>{{$attends}}</span><small>Attended This Event</small></a></h2>
                <h2><a href="#"><span>{{$records}}</span><small>Your Attendance Records</small></a></h2>
            </div>
            <div class="follow-btn text-center">
                @if($available)
                <button type="submit">Present</button>
                @else
                <p style="text-align: center">Ready in a Few Minutes</p>
                @endif
            </div>
        </form>
    </div>
    <div class="desc">
        <b>{{$act->name}}</b> <br>
        {{$act->title}} <br>
        <i style="color: grey">{{$act->speaker}}</i> <br>
        <small>{{date('D, d M H:i', strtotime($act->start_date))}}-{{$act->end_date ? date('H:i', strtotime($act->end_date)) : ' selesai'}} WIB</small>
    </div>

</div>
</body>
</html>
