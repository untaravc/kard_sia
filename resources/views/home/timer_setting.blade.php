<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Timer Setting</title>
</head>
<body>
<form action="/timer_setting" method="POST">
    @csrf
    <div>
        Room
        <input type="text" name="name" value="{{request('r')}}">
    </div>
    <div>
        Start
        <input type="datetime-local" name="start_time" value="{{$setting['start_time']}}">
    </div>
    <div>
        In Room
        <input type="number" name="in_room_minute" value="{{$setting['in_room_minute']}}">
    </div>
    <div>
        Transition
        <input type="number" name="transition_minute" value="{{$setting['transition_minute']}}">
    </div>
    <button type="submit">Save</button>
</form>
</body>
</html>
