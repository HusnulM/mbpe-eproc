<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

use Carbon\Carbon;
use DataTables, Auth, DB;

class AdjustStockImport implements ToCollection, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try{
            $movementCode = null;
            $shkzg        = null;
            $batchAdj     = null;

            $tgl   = substr($_POST['tglupload'], 8, 2);
            $bulan = substr($_POST['tglupload'], 5, 2);
            $tahun = substr($_POST['tglupload'], 0, 4);
            // $ptaNumber = generateGRPONumber($tahun, $bulan);
            if($_POST['adjtype'] === 'IN'){
                $ptaNumber = generateNextNumber('ADJIN', 'ADJIN', $tahun, $bulan, '');
                $movementCode = '561';
                $shkzg        = '+';
                $batchAdj     = 'BATCH_ADJ_IN';
            }else{
                $ptaNumber = generateNextNumber('ADJOUT', 'ADJOUT', $tahun, $bulan, '');
                $movementCode = '201';
                $shkzg        = '-';
                $batchAdj     = 'BATCH_ADJ_OUT';
            }


            DB::table('t_inv01')->insert([
                'docnum'            => $ptaNumber,
                'docyear'           => $tahun,
                'docdate'           => $_POST['tglupload'],
                'postdate'          => $_POST['tglupload'],
                'received_by'       => Auth::user()->username,
                'movement_code'     => $movementCode,
                'remark'            => $_POST['remark'],
                'createdon'         => getLocalDatabaseDateTime(),
                'createdby'         => Auth::user()->email ?? Auth::user()->username
            ]);

            $count = 0;
            $insertData = array();
            foreach ($rows as $index => $row) {
                // dd($row);
                if($row){
                    $matName = '';
                    $matUnit = null;
                    $material = DB::table('t_material')->where('material',strval($row['material']))->first();
                    if($material){
                        $matName = $material->matdesc;
                        $matUnit = $material->matunit;
                    }else{
                        $matName = $row['material_desc'];
                        $matUnit = $row['unit'];
                    }

                    $batchNumber = $batchAdj;
                    $count = $count + 1;
                    $excelData = array(
                        'docnum'       => $ptaNumber,
                        'docyear'      => $tahun,
                        'docitem'      => $count,
                        'movement_code'=> $movementCode,
                        'material'     => strval($row['material']),
                        'matdesc'      => $matName,
                        'batch_number' => $batchNumber,
                        'quantity'     => $row['quantity'],
                        'unit'         => $matUnit,
                        'unit_price'   => $row['unit_price'],
                        'total_price'  => $row['quantity']*$row['unit_price'],
                        'whscode'      => $row['warehouse'],
                        'shkzg'        => $shkzg,
                        'createdon'    => getLocalDatabaseDateTime(),
                        'createdby'    => Auth::user()->email ?? Auth::user()->username

                    );
                    array_push($insertData, $excelData);
                }
            }
            if(sizeof($insertData) > 0){
                insertOrUpdate($insertData,'t_inv02');
                DB::commit();
            }else{
                DB::rollBack();
            }

            return true;
        }catch(\Exception $e){
            DB::rollBack();
            dd($e);
            return false;
        }
    }
}
