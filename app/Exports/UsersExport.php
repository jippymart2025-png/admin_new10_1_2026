<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class UsersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
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
            'Name',
            'Email',
            'Phone',
            'Zone',
            'Active',
            'Created At'
        ];
    }

    public function map($user): array
    {
        return [
            trim(($user->firstName ?? '') . ' ' . ($user->lastName ?? '')),
            $user->email ?? '',
            $user->phoneNumber ?? '',
            $user->zone_name ?? 'Not Assigned',
            ($user->active == 1 || $user->isActive == 1) ? 'Active' : 'Inactive',
            $user->createdAt
                ? Carbon::parse($user->createdAt)->format('M d, Y h:i A')
                : ''
        ];
    }
}
