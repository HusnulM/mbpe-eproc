<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithPreCalculateFormulas;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use DB;

class StockOpnameExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithPreCalculateFormulas
{
    use Exportable, RegistersEventListeners;

    protected $req;

    private $totalMaterialValue = 0;
    private $totalRows = 0;


    function __construct($req) {
        $this->req = $req;
        // $this->totalRows = $totalRows;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $req = $this->req;
        $query = DB::table('v_stock_opname_detail');

        if(isset($req->datefrom) && isset($req->dateto)){
            $query->whereBetween('piddate', [$req->datefrom, $req->dateto]);
        }elseif(isset($req->datefrom)){
            $query->where('piddate', $req->datefrom);
        }elseif(isset($req->dateto)){
            $query->where('piddate', $req->dateto);
        }
        $query->orderBy('id');

        return $query->get();
    }

    public function map($row): array{
        $fields = [
            $row->pidnumber,
            $row->piddate,
            $row->pidnote,
            $row->piduser,
            $row->material,
            $row->matdesc,
            $row->actual_qty,
            $row->quantity,
            $row->actual_qty - $row->quantity,
            $row->matunit,
            $row->whsname,
            $row->unit_price,
            $row->total_price
        ];

        return $fields;
    }

    public function headings(): array
    {
        return [
                "Opname Number",
                "Opname Date",
                "Remark",
                "Created By",
                "Material",
                "Deskripsi",
                "Actual Quantity",
                "System Quantity",
                "Selisih Quantity",
                "Unit",
                "Warehouse",
                "Unit Price",
                "Total Price"
        ];
    }

}
