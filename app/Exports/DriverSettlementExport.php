<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DriverSettlementExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $rows;

    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Order ID',
            'Date',
            'Driver Name',
            'Phone',
            'Zone',
            'Delivery Charge',
            'Tip Amount',
            'Total Earning',
            'Transaction ID',
            'Status',
            'Incentives',
            'Deductions',
            'Comments',
        ];
    }

    public function map($row): array
    {
        // Format date
        $date = '-';
        if ($row->createdAt) {
            try {
                if (is_numeric($row->createdAt)) {
                    $ts = strlen((string)$row->createdAt) > 10 ? (int)$row->createdAt / 1000 : (int)$row->createdAt;
                    $date = Carbon::createFromTimestamp($ts)->format('Y-m-d');
                } else {
                    $date = Carbon::parse($row->createdAt)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $date = '-';
            }
        }

        $transactionId = $row->transaction_id ?? '';
        $paymentStatus = $row->payment_status ?? 'Pending';
        $incentives = (float)($row->incentives ?? 0);
        $deductions = (float)($row->deductions ?? 0);
        $paymentComments = $row->payment_comments ?? '';

        return [
            $row->order_id,
            $date,
            $row->driver_name,
            $row->phone,
            $row->zone,
            number_format($row->delivery_charge, 2),
            number_format($row->tip_amount, 2),
            number_format($row->total_earning, 2),
            $transactionId,
            $paymentStatus,
            number_format($incentives, 2),
            number_format($deductions, 2),
            $paymentComments,
        ];
    }
}

