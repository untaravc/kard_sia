<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Logbook Stase {{$stase->name}}</title>
</head>
<body>
<style>
    table {
        border-collapse: collapse;
        page-break-inside: auto;
    }

    table, th, td {
        border: 1px solid;
    }

    td{
        padding: 3px;
    }

    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
    thead {
        display: table-header-group;
    }
    tfoot {
        display: table-footer-group;
    }
</style>
<b>Stase: {{$stase->name}}</b>
<table style="width: 100%">
    <tr>
        <th>No</th>
        <th>Stase</th>
        <th>Residen</th>
        <th>SPV</th>
        <th>Tanggal</th>
        <th>Inisial pasien</th>
        <th>Diagnosis</th>
        <th>No. RM</th>
    </tr>
    @foreach($logbook as $i => $item)
        <tr>
            <td>
                {{ $i + 1 }}
            </td>
            <td>
                @if($item['stase'])
                    {{$item['stase']['name']}}
                @endif
            </td>
            <td>
                @if($item['student'])
                    {{$item['student']['name']}}
                @endif
            </td>
            <td>
                @if($item['lecture'])
                    {{$item['lecture']['name']}}
                @endif
            </td>
            <td>
                {{date('d F Y', strtotime($item['date']))}}
            </td>
            <td>
                {{$item['field_1']}}
            </td>
            <td>
                {{$item['field_2']}}
            </td>
            <td>
                {{$item['field_3']}}
            </td>
        </tr>
    @endforeach
</table>
</body>
</html>

