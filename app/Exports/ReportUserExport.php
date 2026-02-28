<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReportUserExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $users;

    public function __construct($users)
    {
        $this->users = $users;
    }

    public function collection()
    {
        return $this->users;
    }

    public function headings(): array
    {
        return [
            'User ID',
            'Name',
            'Email',
            'Phone',
            'Zone',
            'Address',
            'Order Count',
            'Last Order Date'
        ];
    }

    public function map($user): array
    {
        return [
            $user->user_id,
            trim(($user->firstName ?? '') . ' ' . ($user->lastName ?? '')),
            $user->email ?? '',
            $user->phoneNumber ?? '',
            $user->zone_name ?? '-',
            $user->address ?? '-',
            $user->order_count ?? 0,
            $user->last_order_date ?? '-',
        ];
    }
}

