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
    @foreach($dataContent as $i => $item)
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
