<style>
    table, td, th {
        border: 1px solid grey;
    }

    table {
        border-collapse: collapse;
        border-color: gainsboro;
    }
</style>
<table>
    <tr>
        <th colspan="12">Resume Agenda kegiatan Ilmiah Program Studi</th>
    </tr>
    <tr>
        <td>downloaded :</td>
        <td>{{date('Y-m-d H:i:s')}}</td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <th>Tgl Pelaksanaan</th>
        <th>Nama</th>
        <th>Judul</th>
        <th>Pembicara</th>
        <th>Mulai</th>
        <th>Selesai</th>
        <th>Tempat</th>
        <th>Penguji</th>
        <th>Pembimbing</th>
        <th>Pengampu</th>
        <th>Catatan</th>
    </tr>
    @foreach($dataContent as $data)
        @php
            $penguji = count($data['penguji']);
            $pembimbing = count($data['pembimbing']);
            $pengampu = count($data['pengampu']);

            $max = max($penguji, $pembimbing, $pengampu);
            if($max === 0){
                $max = 1;
            }
        @endphp
        <tr>
            <td>{{substr($data['start_date'], 0, 10)}}</td>
            <td>{{$data['name']}}</td>
            <td>{{$data['title']}}</td>
            <td>{{$data['speaker']}}</td>
            <td>{{substr($data['start_date'], 11, 5)}}</td>
            <td>{{substr($data['end_date'], 11, 5)}}</td>
            <td>{{substr($data['place'], 0, 20)}}</td>
            <td>{{isset($data['penguji']) && isset($data['penguji'][0]) ? $data['penguji'][0]['name'] : ''}}</td>
            <td>{{isset($data['pembimbing']) && isset($data['pembimbing'][0]) ? $data['pembimbing'][0]['name'] : ''}}</td>
            <td>{{isset($data['pengampu']) && isset($data['pengampu'][0]) ? $data['pengampu'][0]['name'] : ''}}</td>
            <td>{{substr($data['note'], 0, 30)}}</td>
        </tr>
        @for($i = 1; $i < $max; $i++)
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>{{isset($data['penguji']) && isset($data['penguji'][$i]) ? $data['penguji'][$i]['name'] : ''}}</td>
                <td>{{isset($data['pembimbing']) && isset($data['pembimbing'][$i]) ? $data['pembimbing'][$i]['name'] : ''}}</td>
                <td>{{isset($data['pengampu']) && isset($data['pengampu'][$i]) ? $data['pengampu'][$i]['name'] : ''}}</td>
                <td></td>
            </tr>
        @endfor
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    @endforeach
</table>