
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>SIA KARDIOLOGI</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="/css/app.css">
    <style>
        body{
            font-family: 'docs-Roboto', Helvetica, Arial, sans-serif;
        }
    </style>
</head>
<body class="layout-top-nav">
<div class="wrapper" id="app">

    <div class="content-wrapper" style="min-height: 815px; background-color: #01438529">
        <div class="content pt-3">
            <div class="container" style="max-width: 720px">
                <router-view></router-view>
            </div>
        </div>

    </div>

</div>
<!-- ./wrapper -->
<script>
    @if(Auth::guard()->user()->role == "superadmin")
        window.mewnesia = "superadmin";
    @else
        window.mewnesia = "perki";
    @endif
</script>
<!-- jQuery -->
@include('js')

</body>
</html>
