<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Users</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        h2 {
            text-align: center;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background-color: #2c3e50;
            color: #fff;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<h2>Users</h2>

<table>
    <thead>
    <tr>
        <th>User Info</th>
        <th>Email</th>
        <th>Phone Number</th>
        <th>Zone</th>
        <th>Date</th>
    </tr>
    </thead>
    <tbody>
    @forelse($users as $user)
        <tr>
            <td>{{ trim(($user->firstName ?? '') . ' ' . ($user->lastName ?? '')) }}</td>
            <td>{{ $user->email ?? '' }}</td>
            <td>{{ $user->phoneNumber ?? '' }}</td>
            <td>{{ $user->zone_name ?? 'Not Assigned' }}</td>
            <td>{{ $user->createdAt ? \Carbon\Carbon::parse($user->createdAt)->format('M d, Y h:i A') : '' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="5" style="text-align: center; padding: 20px;">No users found</td>
        </tr>
    @endforelse
    </tbody>
</table>

</body>
</html>
