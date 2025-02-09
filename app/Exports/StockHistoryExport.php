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

class StockHistoryExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithPreCalculateFormulas
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
        $req = $_POST;
        $whsCode = 0;
        if($req['Warehouse'] != 0){
            // $whsCode = $req['Warehouse'];
            $materials = DB::table('v_material_movements_2')
                        ->where('whscode', $req['Warehouse'])
                        ->orderBy('whscode', 'ASC')
                        ->orderBy('material', 'ASC')
                        ->get();
        }else{
            $materials = DB::table('v_material_movements_2')
                        ->orderBy('whscode', 'ASC')
                        ->orderBy('material', 'ASC')
                        ->get();
        }

        $strDate = date('Y-m-d');
        if(isset($req['datefrom'])){
            $strDate = $req['datefrom'];
        }

        $endDate = date('Y-m-d');
        if(isset($req['dateto'])){
            $endDate = $req['dateto'];
        }

        $beginQty = DB::table('v_inv_movement')
                    ->select(DB::raw('material'), DB::raw('whscode'), DB::raw('sum(quantity) as begin_qty'),
                             DB::raw('sum(amount_val) as begin_val'))
                    ->where('postdate', '<', $strDate)
                    ->groupBy(DB::raw('material'), DB::raw('whscode'))
                    ->get();

        $query = DB::select('call spGetStockHistory(
            "'. $strDate .'",
            "'. $endDate .'",
            "'. $whsCode .'")');

            $mtMat = array();
            foreach ($query as $sg) {
                $mtMat[] = $sg->material;
            }
            $mtMat = array_unique($mtMat);

            $ftWhs = array();
            foreach ($query as $sg) {
                $ftWhs[] = $sg->whscode;
            }
            $ftWhs = array_unique($ftWhs);
            // return $materials;
            $stocks = array();
            foreach($materials as $key => $row){
                // return $row;
                // dd($row);
                $bQty = 0;
                $bVal = 0;
                if(in_array($row->material, $mtMat, TRUE)){
                    if(in_array($row->whscode, $ftWhs, TRUE)){
                        // return $query;
                        foreach($query as $mat => $mrow){

                            $bQty = 0;
                            $bVal = 0;
                            if($row->material === $mrow->material && $row->whscode === $mrow->whscode){
                                // dd($mrow);
                                // echo $row->material. ' - ' . $mrow->material;
                                // dd($beginQty);
                                foreach($beginQty as $bqty => $mtqy){
                                    if($mtqy->material === $mrow->material && $mtqy->whscode === $mrow->whscode){
                                        $bQty = $bQty + $mtqy->begin_qty;
                                        $bVal = $bVal + $mtqy->begin_val;
                                    }
                                }
                                $data = array(
                                    'id'        => $row->id,
                                    'material'  => $row->material,
                                    'matdesc'   => $row->matdesc,
                                    'begin_qty' => $bQty,
                                    'qty_in'    => (int)$mrow->qty_in,
                                    'qty_out'   => (int)$mrow->qty_out,
                                    'begin_val' => $bVal,
                                    'val_in'    => (float)$mrow->val_in,
                                    'val_out'   => (float)$mrow->val_out,
                                    'whscode'   => $mrow->whscode,
                                    'whsname'   => $mrow->whsname,
                                    'unit'      => $mrow->unit,
                                    'avg_price' => $row->avg_price,
                                );
                                // dd($data);
                                array_push($stocks, $data);
                            }
                        }
                    }
                }else{

                    $bQty = 0;
                    $bVal = 0;
                    // dd($beginQty);
                    foreach($beginQty as $bqty => $mtqy){
                        // dd('a');
                        if($mtqy->material === $row->material && $mtqy->whscode === $row->whscode){
                            // dd($mtqy);
                            $bQty = $bQty + $mtqy->begin_qty;
                            $bVal = $bVal + $mtqy->begin_val;
                        }
                    }
                    // dd($data);
                    $data = array(
                        'id'        => $row->id,
                        'material'  => $row->material,
                        'matdesc'   => $row->matdesc,
                        'begin_qty' => $bQty,
                        'qty_in'    => (int)0,
                        'qty_out'   => (int)0,
                        'begin_val' => $bVal,
                        'val_in'    => (float)0,
                        'val_out'   => (float)0,
                        'whscode'   => $row->whscode,
                        'whsname'   => $row->whsname,
                        'unit'      => $row->unit,
                        'avg_price' => $row->avg_price,
                    );
                    // dd($data);
                    array_push($stocks, $data);
                }
            }

        $this->totalRows = sizeof($stocks);

        $stocks = collect($stocks)->sortBy('whscode')->values();


        setExcelRows($this->totalRows);

        // $this->totalMaterialValue = number_format($totalValue, 0, '.', '');



        return $stocks;
    }

    public function map($row): array{
        // dd($row);
        $fields = [
            $row['material'],
            $row['matdesc'],
            $row['whsname'],
            $row['begin_qty'],
            $row['qty_in'],
            $row['qty_out'],
            $row['begin_qty'] + $row['qty_in'] + $row['qty_out'],
            $row['unit'],
            number_format($row['begin_val'], 0, '.', ''),
            number_format($row['val_in'], 0, '.', ''),
            number_format($row['val_out'], 0, '.', ''),
            number_format($row['begin_val'] + $row['val_in'] + $row['val_out'], 0, '.', '')
        ];

        return $fields;
    }

    public function headings(): array
    {
        return [
                "Material",
                "Deskripsi",
                "Warehouse",
                "Begin Qty",
                "IN",
                "OUT",
                "End Qty",
                "Unit",
                "Begin Value",
                "Value In",
                "Value Out",
                "End Value"
        ];
    }

    public static function afterSheet(AfterSheet $event)
    {
        $cellRange = 'A1:L1'; // All headers
        $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(11);
        $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setBold(true);

        $highestRow = $event->sheet->getDelegate()->getHighestRow();

        $event->sheet->getDelegate()->getStyle('A1:A'.$highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);



        $sumCell = '=ROUND(SUM(L2:L'.$highestRow.'),2)';

        $highestRow = $highestRow + 1;
        $event->sheet->appendRows([
            ['Grand Total', '', '', '','', '', '', '','', '', '', $sumCell]
        ], $event);

        $event->sheet->mergeCells('A'.$highestRow.':K'.$highestRow);

        $cellRange = 'A'.$highestRow.':K'.$highestRow;
        $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(11);
        $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setBold(true);
        // $event->sheet->getDelegate()->getStyle($cellRange)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_RIGHT);
        $event->sheet->getDelegate()->getStyle($cellRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $event->sheet->getDelegate()->getStyle('L'.$highestRow)->getFont()->setBold(true);

    }
}
