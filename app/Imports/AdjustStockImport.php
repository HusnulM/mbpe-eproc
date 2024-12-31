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
            $tgl   = substr($_POST['tglupload'], 8, 2);
            $bulan = substr($_POST['tglupload'], 5, 2);
            $tahun = substr($_POST['tglupload'], 0, 4);
            // $ptaNumber = generateGRPONumber($tahun, $bulan);

            $ptaNumber = generateNextNumber('ADJOUT', 'ADJOUT', $tahun, $bulan, '');

            DB::table('t_inv01')->insert([
                'docnum'            => $ptaNumber,
                'docyear'           => $tahun,
                'docdate'           => $_POST['tglupload'],
                'postdate'          => $_POST['tglupload'],
                'received_by'       => Auth::user()->username,
                'movement_code'     => '201',
                'remark'            => $_POST['remark'],
                'createdon'         => getLocalDatabaseDateTime(),
                'createdby'         => Auth::user()->email ?? Auth::user()->username
            ]);

            $count = 0;
            $insertData = array();
            foreach ($rows as $index => $row) {
                // dd($row);
                $matName = '';
                $material = DB::table('t_material')->where('material',strval($row['material']))->first();
                if($material){
                    $matName = $material->matdesc;
                }else{
                    $matName = $row['material_desc'];
                }

                $batchNumber = 'ADJQTYOUT';
                $count = $count + 1;
                $excelData = array(
                    'docnum'       => $ptaNumber,
                    'docyear'      => $tahun,
                    'docitem'      => $count,
                    'movement_code'=> '201',
                    'material'     => $row['material'],
                    'matdesc'      => $matName,
                    'batch_number' => $batchNumber,
                    'quantity'     => $row['quantity'],
                    'unit'         => $row['unit'],
                    'unit_price'   => $row['unit_price'],
                    'total_price'  => $row['quantity']*$row['unit_price'],
                    'whscode'      => $row['warehouse'],
                    'shkzg'        => '-',
                    'createdon'    => getLocalDatabaseDateTime(),
                    'createdby'    => Auth::user()->email ?? Auth::user()->username

                );
                array_push($insertData, $excelData);
            }
            insertOrUpdate($insertData,'t_inv02');
            DB::commit();

            return true;
        }catch(\Exception $e){
            DB::rollBack();
            return false;
        }
    }
}
