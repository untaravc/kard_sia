<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ setting('app.name', 'app-name') }}</title>
    {{-- viewport-fit=cover is required for env(safe-area-inset-*) to report
         real values, which the fixed bottom nav relies on to clear the home
         indicator on notched phones. --}}
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#ffffff">
    <link rel="icon" href="/assets/images/logo-blu-sm.png">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
</head>
<body>
    <div id="app"></div>
    <script src="{{ mix('js/app.js') }}"></script>
</body>
</html>
