<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Foods</title>

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

<h2>Foods</h2>

<table>
    <thead>
    <tr>
        <th>Food Name</th>
        <th>Restaurant</th>
        <th>Category</th>
        <th>Price</th>
        <th>Merchant Price</th>
        <th>Discount Price</th>
        <th>Type</th>
        <th>Available</th>
        <th>Published</th>
        <th>Date</th>
    </tr>
    </thead>
    <tbody>
    @forelse($foods as $food)
        <tr>
            <td>{{ $food->name ?? '' }}</td>
            <td>{{ $food->restaurant_name ?? '' }}</td>
            <td>{{ $food->category_name ?? '' }}</td>
            <td>{{ $food->price ?? '' }}</td>
            <td>{{ $food->merchant_price ?? '' }}</td>
            <td>{{ $food->disPrice ?? '' }}</td>
            <td>{{ ($food->nonveg ?? false) ? 'Non-Veg' : 'Veg' }}</td>
            <td>{{ ($food->isAvailable ?? false) ? 'Yes' : 'No' }}</td>
            <td>{{ ($food->publish ?? false) ? 'Yes' : 'No' }}</td>
            <td>{{ $food->createdAt }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="10" style="text-align: center; padding: 20px;">
                No foods found
            </td>
        </tr>
    @endforelse
    </tbody>
</table>

</body>
</html>
