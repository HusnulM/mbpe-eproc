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

class MaterialExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithPreCalculateFormulas
{
    use Exportable, RegistersEventListeners;

    protected $req;

    function __construct($req) {
        $this->req = $req;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = DB::table('v_material')->orderBy('material');

        $query->orderBy('id');
        return $query->get();
    }

    public function map($row): array{
        $fields = [
            $row->material,
            $row->matdesc,
            $row->mattypedesc,
            $row->matunit
        ];

        return $fields;
    }

    public function headings(): array
    {
        return [
                "Material",
                "Description",
                "Category",
                "Unit",
        ];
    }

    public static function afterSheet(AfterSheet $event)
    {
        $cellRange = 'A1:D1'; // All headers
        $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(11);
        $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setBold(true);

        $highestRow = $event->sheet->getDelegate()->getHighestRow();
        $event->sheet->getDelegate()->getStyle('A1:A'.$highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
    }
}
