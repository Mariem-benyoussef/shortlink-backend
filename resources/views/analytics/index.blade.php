<h1>Google Analytics GA4 Report</h1>

@if (isset($data) && count($data) > 0)
    <table border="1">
        <thead>
            <tr>
                <th>Date</th>
                <th>Utilisateurs</th>
                <th>Sessions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $row)
                <tr>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['users'] }}</td>
                    <td>{{ $row['sessions'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p>Aucune donnée disponible.</p>
@endif