<table>
    <tr>
        <th>No</th>
        <th>Nama</th>
        <th>Judul</th>
        <th>Tempat</th>
        <th>Tanggal</th>
        <th>Pembicara</th>
        <th>Pembimbing</th>
        <th>Penguji</th>
        <th>Pengampu</th>
    </tr>
    @foreach($data_content as $key => $content)
        <tr>
            <td>{{$key+1}}</td>
            <td>{{$content->name}}</td>
            <td>{{$content->title}}</td>
            <td>{{$content->place}}</td>
            <td>{{$content->start_date}}</td>
            <td>{{$content->speaker}}</td>
            <td>
                @if(count($content->pembimbing) > 0)
                    @foreach($content->pembimbing as $pemb)
                        {{$pemb->name_alt}},
                    @endforeach
                @endif
            </td>
            <td>
                @if(count($content->penguji) > 0)
                    @foreach($content->penguji as $pemb)
                        {{$pemb->name_alt}},
                    @endforeach
                @endif
            </td>
            <td>
            @if(count($content->pengampu) > 0)
                @foreach($content->pengampu as $pemb)
                    {{$pemb->name_alt}},
                @endforeach
            @endif
            </td>
        </tr>
    @endforeach
</table>
