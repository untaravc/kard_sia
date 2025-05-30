<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <title>Timer Setting</title>
</head>
<body>
<div class="mx-auto p-3" style="max-width: 500px">
    <div class="shadow card p-3">
    <form action="/timer_setting" method="POST" >
        @csrf
        <div class="form-group mb-3">
            <label for="">Room</label>
            <input type="text" class="form-control form-control-lg text-center" name="name" value="{{request('r')}}">
        </div>
        <div class="form-group mb-3">
            <label for="">Start</label>
            <input type="datetime-local" class="form-control form-control-lg text-center" name="start_time" value="{{$setting['start_time']}}">
        </div>
        <div class="form-group mb-3">
            <label for="">In Room</label>
            <input type="number" class="form-control form-control-lg text-center" name="in_room_minute" value="{{$setting['in_room_minute']}}">
        </div>
        <div class="form-group mb-3">
            <label for="">Transition</label>
            <input type="number" class="form-control form-control-lg text-center" name="transition_minute"
                   value="{{$setting['transition_minute']}}">
        </div>
        <div class="form-group mb-3">
            <label for="">Status</label>
            <select class="form-control form-control-lg text-center" name="status">
                <option value="1" @if($setting['status'] == 1) selected @endif>Open</option>
                <option value="0" @if($setting['status'] == 0) selected @endif>Close</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%">Save</button>
    </form>
    </div>
</div>
</body>
</html>
