<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Restaurants Report</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #000;
        }

        h2 {
            text-align: center;
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f0f0f0;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            vertical-align: top;
            text-align: left;
            word-break: break-word;
        }

        th {
            font-weight: bold;
        }

        tr:nth-child(even) {
            background: #fafafa;
        }
    </style>
</head>
<body>

<h2>Restaurants Report</h2>

<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Restaurant</th>
        <th>Owner</th>
        <th>Phone</th>
        <th>Zone</th>
        <th>Admin Commission</th>
        <th>Status</th>
        <th>Created At</th>
    </tr>
    </thead>
    <tbody>
    @forelse($restaurants as $index => $restaurant)
        <tr>
            <td>{{ $index + 1 }}</td>

            <td>
                {{ $restaurant->title ?? 'N/A' }}
            </td>

            <td>
                {{ $restaurant->authorName ?? 'N/A' }}
            </td>

            <td>
                {{ $restaurant->phonenumber ?? '' }}
            </td>

            <td>
                {{ $restaurant->zone_name ?? 'Not Assigned' }}
            </td>

            <td>
                @php
                    $commission = null;
                    if (!empty($restaurant->adminCommission)) {
                        $commissionData = is_string($restaurant->adminCommission)
                            ? json_decode($restaurant->adminCommission, true)
                            : $restaurant->adminCommission;

                        $commission = $commissionData['fix_commission'] ?? null;
                    }
                @endphp

                {{ $commission !== null ? $commission . '%' : 'N/A' }}
            </td>

            <td>
                {{ ($restaurant->reststatus == 1 || $restaurant->reststatus === true) ? 'Active' : 'Inactive' }}
            </td>

            <td>
                {{ $restaurant->createdAt
                    ? \Carbon\Carbon::parse(trim($restaurant->createdAt, '"'))->format('M d, Y h:i A')
                    : '' }}
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="8" style="text-align:center;padding:15px;">
                No restaurants found
            </td>
        </tr>
    @endforelse
    </tbody>
</table>

</body>
</html>
