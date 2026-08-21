<table>
    <tr>
        <th colspan="7">Scoring History - {{ $lecture_name }}</th>
    </tr>
    <tr><td>{{ date('Y-m-d H:i:s') }}</td></tr>
    <tr><td></td></tr>
    <tr>
        <th>Date</th>
        <th>Student</th>
        <th>Stase</th>
        <th>Task</th>
        <th>Score</th>
        <th>Symbol</th>
        <th>Status</th>
    </tr>
    @forelse($rows as $row)
        <tr>
            <td>{{ $row['date'] }}</td>
            <td>{{ $row['student'] }}</td>
            <td>{{ $row['stase'] }}</td>
            <td>{{ $row['task'] }}</td>
            <td>{{ $row['point_average'] }}</td>
            <td>{{ $row['symbol'] }}</td>
            <td>{{ $row['status'] }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="7">No data.</td>
        </tr>
    @endforelse
</table>
