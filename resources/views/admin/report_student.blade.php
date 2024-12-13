<style>
    body,
    table {
        font-size: 10px;
    }

    .table-custom,
    .table-custom td,
    .table-custom th {
        border: 1px solid black;
        padding: 2px;
        font-size: 10px;
    }

    .table-custom {
        width: 100%;
        border-collapse: collapse;
    }

    input.checkbox {
        width: 10px;
        height: 10px;
    }

    .border-bottom-alt {
        border-bottom: 1px solid grey;
    }

    @media print {
        @page {
            size: portrait;
        }

        .pagebreak {
            page-break-before: always;
        }
    }
</style>
<table>
    <tr>
        <td colspan="3">Program Kegiatan Akademik dan Profesi</td>
    </tr>
    <tr>
        <td colspan="3">
            @if ($student)
                {{ $student->name }}
            @endif
        </td>
    </tr>
</table>
<table class="table-custom">
    <tr>
        <th><b>Tahap</b></th>
        <th><b>Nama</b></th>
        <th colspan="2"><b>Tanggal</b></th>
    </tr>
    @for ($i = 0; $i < count($stase_date); $i++)
        <tr>
            <td>
                @if ($i === 0)
                    {{ $stase_date[$i]['desc'] }}
                @else
                    @if ($stase_date[$i - 1]['desc'] != $stase_date[$i]['desc'])
                        {{ $stase_date[$i]['desc'] }}
                    @endif
                @endif
            </td>
            <td>
                {{ $stase_date[$i]['name'] }}
            </td>
            <td>
                @if ($stase_date[$i]['stase_log'] && $stase_date[$i]['stase_log']['start_date'] != '')
                    {{ date('d M Y', strtotime($stase_date[$i]['stase_log']['start_date'])) }}
                @endif
            </td>
            <td>
                @if ($stase_date[$i]['stase_log'] && $stase_date[$i]['stase_log']['end_date'] != '')
                    {{ date('d M Y', strtotime($stase_date[$i]['stase_log']['end_date'])) }}
                @endif
            </td>
        </tr>
    @endfor
</table>
<table>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td><b>Riwayat Nilai</b></td>
    </tr>
</table>
<table class="table-custom">
    @for ($i = 0; $i < count($stases); $i++)
        <tr>
            <th colspan="2" style="text-align: left"><b>{{ $stases[$i]['name'] }}</b></th>
        </tr>
        @foreach ($stases[$i]['staseTasks'] as $task)
            <tr>
                <td>
                    {{ $task['name'] }}
                </td>
                <td>
                    @if ($task['stase_task_log'] && $task['stase_task_log']['title'])
                        @php
                            try {
                                echo strip_tags($task['stase_task_log']['title']);
                            } catch (\Exception $e) {
                            }
                        @endphp
                    @endif
                </td>
                <td>
                    @if ($task['stase_task_log'] && $task['stase_task_log']['lecture'])
                        {{ $task['stase_task_log']['lecture']['name'] }}
                    @endif
                </td>
                <td>
                    @if ($task['stase_task_log'])
                        {{ $task['stase_task_log']['point_average'] }}
                    @endif
                </td>
                <td>
                    @if ($task['stase_task_log'])
                        {{ date('d/m/Y', strtotime($task['stase_task_log']['date'])) }}
                    @endif
                </td>
            </tr>
        @endforeach
    @endfor
</table>
