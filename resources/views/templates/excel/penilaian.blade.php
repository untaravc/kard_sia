<table>
    <tr>
        <th colspan="10">Daftar Tugas PPDS Jantung dan Pembuluh Darah</th>
    </tr>
    <tr><td>downloaded : {{date('Y-m-d H:i:s')}}</td></tr>
    <tr><td></td></tr>
    <tr>
        <th>Tgl Pelaksanaan</th>
        <th>Tgl Penilaian</th>
        <th>Nama PPDS</th>
        <th>Nama Staff</th>
        <th>Blok / Topik / Matakuliah</th>
        <th>Tugas stase	Aktivitas Induk</th>
        <th>Nama Aktivitas</th>
        <th>Satuan</th>
        <th>Waktu</th>
        <th>Judul</th>
        <th>Nilai</th>
    </tr>
    @foreach($dataContent as $data)
    <tr>
        <td>
            @if(isset($data['plan']))
                {{substr($data['plan'], 0, 10)}}
            @endif
        </td>
        <td>
            @if(isset($data['date']))
            {{substr($data['date'], 0, 10)}}
            @endif
        </td>
        <td>
            @if(isset($data['student']))
                {{$data['student']['name']}}
            @endif
        </td>
        <td>
            @if(isset($data['lecture']))
                {{$data['lecture']['name']}}
            @endif
        </td>
        <td>
            @if(isset($data['stase']))
                {{$data['stase']['name']}}
            @endif
        </td>
        <td>
            @if(isset($data['staseTask']))
                {{$data['staseTask']['name']}}
            @endif
        </td>
        <td></td>
        <td></td>
        <td></td>
        <td>
            @if(isset($data['title']))
                {{preg_replace('/[^a-zA-Z0-9_ -]/s',' ',$data['title'])}}
            @endif
        </td>
        <td>
            @if(isset($data['point_average']))
                {{$data['point_average']}}
            @endif
        </td>
    </tr>
    @endforeach
</table>
