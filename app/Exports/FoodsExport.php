<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FoodsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $foods;

    public function __construct($foods)
    {
        $this->foods = $foods;
    }

    public function collection() {

        return $this->foods;

    }

    public function headings(): array
    {
        return [
            'Food Name',
            'Restaurant',
            'Category',
            'Price',
            'Merchant Price',
            'Discount','Type',
            'Available',
            'Published',
            'Created At'
        ];
    }

    public function map($food): array
    {
        return [
            $food->name,
            $food->restaurant_name,
            $food->category_name,
            $food->price,
            $food->merchant_price,
            $food->disPrice,
            $food->nonveg ? 'Non-Veg' : 'Veg',
            $food->isAvailable ? 'Yes' : 'No',
            $food->publish ? 'Yes' : 'No',
            $food->createdAt
        ];
    }
}
