<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sia Presensi</title>
</head>
<body>
<div class="container">
    <button>Notify me!</button>
</div>
<script>
    function showNotification(){
        const notification = new Notification('msg', {
            body: 'Hai',
        });
    }

    if(Notification.permission === 'granted'){
        showNotification();
    }else if(Notification.permission !== 'denied'){
        Notification.requestPermission().then(permission => {
            if(permission === 'granted'){
                showNotification()
            }
        });
    }
</script>
</body>
</html>
