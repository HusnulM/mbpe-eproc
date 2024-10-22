<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use DB;

class StockExport implements FromCollection, WithHeadings, WithMapping
{
    protected $req;

    function __construct($req) {
        $this->req = $req;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = DB::table('v_inv_summary_stock_withvalue');

        $query->orderBy('material','asc');
        $query->orderBy('whsid','asc');
        return $query->get();
    }

    public function map($row): array{
        $fields = [
            $row->material,
            $row->matdesc,
            $row->whsname,
            $row->quantity,
            $row->unit,
            $row->total_value
        ];

        return $fields;
    }

    public function headings(): array
    {
        return [
                "Material",
                "Deskripsi",
                "Warehouse",
                "Quantity",
                "Unit",
                "Total Value"
        ];
    }
}
