<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Form Option</title>
</head>
<body>
<table>
    <tr>
        <th>No</th>
        <th>Stase</th>
        <th>Logbook</th>
    </tr>
    @foreach($form_options as $form_option)
    <tr>
        <td>{{$loop->iteration}}</td>
        <td>
            {{$form_option['stase']['name']}}
        </td>
        <td>
            {{$form_option['name']}}
        </td>
    </tr>
    @endforeach

</table>
</body>
</html>
