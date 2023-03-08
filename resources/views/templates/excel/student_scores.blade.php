<table>
    <tr>
        <th colspan="5">Daftar Tugas PPDS Jantung dan Pembuluh Darah</th>
    </tr>
    <tr><td>{{date('Y-m-d H:i:s')}}</td></tr>
    <tr><td></td></tr>
    @foreach($data as $tahap)
        <tr>
            <td colspan="5">
                <b style="font-size: 20px; text-align: center; color: red">{{$tahap['tahap']}}</b>
            </td>
        </tr>

        @foreach($tahap['stase'] as $i => $stase)
            <tr>
                <td><b>{{$stase['name']}}</b></td>
            </tr>
            <tr>
                <td><i>Tugas Stase</i></td>
                <td><i>Nama PPDS</i></td>
                <td><i>Tanggal Pelaksanaan</i></td>
                <td><i>Pembimbing</i></td>
                <td><i>Nilai</i></td>
            </tr>
            @foreach($stase['task'] as $task)
                @if(count($task['logs']) == 0)
                    <tr>
                        <td>{{$task['name']}}</td>
                        <td></td>
                        <td><i>No data.</i></td>
                        <td></td>
                        <td></td>
                    </tr>
                @else
                    @foreach($task['logs'] as $log)
                        <tr>
                            <td>{{$task['name']}}</td>
                            <td>{{$student['name']}}</td>
                            <td>{{$log['date'] ?? 'Belum ada penilaian online'}}</td>
                            <td>{{$log['lecture']['name']}}</td>
                            <td>{{$log['point_average']}}</td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
            <tr><td></td></tr>
            <tr><td></td></tr>
        @endforeach
    @endforeach
</table>