<?php

namespace App\Exports;

use App\Models\User;
use App\Models\ImportExport\ImportExport;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomersExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    protected ImportExport $importExport;

    public function __construct(ImportExport $importExport)
    {
        $this->importExport = $importExport;
    }

    public function collection(): Collection
    {
        return User::with(['customerGroup'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Phone',
            'Username',
            'Customer Group',
            'Marketing Opt-in',
            'Total Orders',
            'Lifetime Value (KES)',
            'Average Order Value (KES)',
            'First Order Date',
            'Last Order Date',
            'Is Active',
            'Created At',
            'Updated At',
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->id,
            $customer->name,
            $customer->email,
            $customer->phone,
            $customer->username,
            $customer->customerGroup?->name ?? 'Default',
            $customer->marketing_opt_in ? 'Yes' : 'No',
            $customer->total_orders,
            number_format($customer->lifetime_value, 2),
            number_format($customer->average_order_value, 2),
            $customer->first_order_date?->format('Y-m-d H:i:s'),
            $customer->last_order_date?->format('Y-m-d H:i:s'),
            $customer->is_active ? 'Yes' : 'No',
            $customer->created_at?->format('Y-m-d H:i:s'),
            $customer->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
