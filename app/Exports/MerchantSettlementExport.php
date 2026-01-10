<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


class MerchantSettlementExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
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
            'Merchant',
            'Items',
            'Merchant Price',
            'Promotion Price',
            'Commission %',
            'GST %',
            'Settlement Amount',
            'Transaction ID',
            'Status',
            'Comments',
        ];
    }

    public function map($row): array
    {
        $items = $this->formatItems($row->products);
        $merchantPrice = (float) ($row->merchant_price ?? 0);
        $promotionPrice = (float) ($row->promotion_price ?? 0);
        $commission = (float) ($row->commission ?? 0);
        $gstStatus = (int) ($row->gst ?? 0);
        $isGstAccepted = $gstStatus === 1;
        $gstPercent = $isGstAccepted ? 5 : 0;
        $settlementAmount = (float) ($row->settlement_amount ?? 0);
        $transactionId = $row->transaction_id ?? '';
        $paymentStatus = $row->payment_status ?? 'Pending';
        $paymentComments = $row->payment_comments ?? '';

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

        return [
            $row->id,
            $date,
            $row->vendor_name,
            $items,
            number_format($merchantPrice, 2),
            number_format($promotionPrice, 2),
            $commission > 0 ? number_format($commission, 1) . '%' : '0%',
            $gstPercent . '%',
            number_format($settlementAmount, 2),
            $transactionId,
            $paymentStatus,
            $paymentComments,
        ];
    }

    private function formatItems($products)
    {
        if (!$products) return 'N/A';

        $items = json_decode($products, true);
        if (!is_array($items)) return 'N/A';

        return collect($items)->map(function ($item) {
            $name = $item['name'] ?? 'Item';
            $qty  = $item['quantity'] ?? 1;
            $price = $item['price'] ?? 0;

            return "{$name} x{$qty} (₹" . ($price * $qty) . ")";
        })->implode(' + ');
    }
}
