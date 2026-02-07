<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\DB;

class RestaurantsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $restaurants;

    public function __construct($restaurants)
    {
        $this->restaurants = $restaurants;
    }
    public function collection()
    {
        return $this->restaurants;
    }

    public function headings(): array
    {
        return [
            'Restaurant Name',
            'Phone',
            'Location',
            'Zone',
            'Status',
            'Open',
            'Business Model',
            'Wallet Amount',
            'Created At'
        ];
    }


    public function map($r): array
    {
        // 1️⃣ Zone ID → Zone Name map
        $zoneMap = DB::table('zone')
            ->pluck('name', 'id')
            ->toArray();

        // 🌍 Zone Name
        $zoneName = $zoneMap[$r->zoneId] ?? 'Not Assigned';

        // 📦 Business Model (from JSON column subscription_plan)
        $businessModel = 'N/A';
        if (!empty($r->subscription_plan)) {
            $plan = is_string($r->subscription_plan)
                ? json_decode($r->subscription_plan, true)
                : $r->subscription_plan;

            if (is_array($plan) && isset($plan['name'])) {
                $businessModel = $plan['name'];
            }
        }

        return [
            $r->title,
            $r->phonenumber,
            $r->location,
            $zoneName,
            ($r->reststatus ? 'Active' : 'Inactive'),
            ($r->isOpen ? 'Open' : 'Closed'),
            $businessModel,
            $r->walletAmount,
            $r->createdAt
        ];
    }
}
