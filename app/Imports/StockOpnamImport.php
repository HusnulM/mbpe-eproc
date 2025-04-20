<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

use Carbon\Carbon;
use DataTables, Auth, DB;

class StockOpnamImport implements ToCollection, WithHeadingRow
{
    protected $req;

    function __construct($req) {
        $this->req = $req;
    }

    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        // return $this->req;
        DB::beginTransaction();
        try{
            if($rows){
                // dd($request);
                $bulan  = date('m');
                $tahun  = date('Y');

                $opnamNumber = $ptaNumber = generateNextNumber('OPNAM', 'OPNAM', $tahun, $bulan, '');

                $stockOpnamID = DB::table('t_stock_opnam01')->insertGetId([
                    'pidnumber'         => $opnamNumber,
                    'piddate'           => $_POST['tglupload'],
                    'pidnote'           => $_POST['remark'],
                    'piduser'           => Auth::user()->username,
                    'whsid'             => $_POST['whscode'],
                    'createdon'         => getLocalDatabaseDateTime(),
                    'createdby'         => Auth::user()->email ?? Auth::user()->username
                ]);

                if(isset($this->req['efile'])){
                    $files = $this->req['efile'];
                    $insertFiles = array();
                    foreach ($files as $efile) {
                        $filename = $efile->getClientOriginalName();
                        $upfiles = array(
                            'doc_object' => 'OPNAM',
                            'doc_number' => $opnamNumber,
                            'efile'      => $filename,
                            'pathfile'   => '/files/OPNAM/'. $filename,
                            'createdon'  => getLocalDatabaseDateTime(),
                            'createdby'  => Auth::user()->username ?? Auth::user()->email
                        );
                        array_push($insertFiles, $upfiles);
                        $efile->move('files/OPNAM/', $filename);
                    }
                    if(sizeof($insertFiles) > 0){
                        insertOrUpdate($insertFiles,'t_attachments');
                    }
                }

                $count = 0;
                $insertData = array();
                foreach ($rows as $index => $row) {
                    if($row['part_number']){
                        $matName = '';
                        $material = DB::table('t_material')->where('material',strval($row['part_number']))->first();
                        if($material){
                            $matName = $material->matdesc;
                        }else{
                            $matName = $row['part_name'];
                        }
                        $count = $count + 1;
                        // DB::table('t_stock_opnam02')->insert([
                        //     'pidnumber'    => $opnamNumber,
                        //     'header_id'    => $stockOpnamID,
                        //     'piditem'      => $count,
                        //     'material'     => strval($row['part_number']),
                        //     'matdesc'      => $matName,
                        //     'actual_qty'   => $row['actual_stock'],
                        //     'matunit'      => $row['uom'],
                        //     'unit_price'   => $row['harga_satuan'],
                        //     'total_price'  => $row['actual_stock'] * $row['harga_satuan']
                        // ]);
                        $excelData = array(
                            'pidnumber'    => $opnamNumber,
                            'header_id'    => $stockOpnamID,
                            'piditem'      => $count,
                            'material'     => strval($row['part_number']),
                            'matdesc'      => $matName,
                            'actual_qty'   => $row['actual_stock'],
                            'matunit'      => $row['uom'],
                            'unit_price'   => $row['harga_satuan'],
                            'total_price'  => $row['actual_stock'] * $row['harga_satuan']
                        );
                        array_push($insertData, $excelData);
                    }
                }

                if(sizeof($insertData) > 0){
                    insertOrUpdate($insertData,'t_stock_opnam02');

                    $approval = DB::table('v_workflow_budget')->where('object', 'OPNAM')->where('requester', Auth::user()->id)->get();
                    if(sizeof($approval) > 0){
                        $insertApproval = array();
                        foreach($approval as $row){
                            $is_active = 'N';
                            if($row->approver_level == 1){
                                $is_active = 'Y';
                            }
                            $approvals = array(
                                'pidnumber'         => $opnamNumber,
                                'approver_level'    => $row->approver_level,
                                'approver'          => $row->approver,
                                'requester'         => Auth::user()->id,
                                'is_active'         => $is_active,
                                'createdon'         => getLocalDatabaseDateTime()
                            );
                            array_push($insertApproval, $approvals);
                        }
                        insertOrUpdate($insertApproval,'t_opnam_approval');

                    }else{
                        DB::table('t_stock_opnam01')->where('id', $stockOpnamID)
                        ->update([
                            'approval_status' => 'A'
                        ]);
                    }

                    DB::commit();

                    setOpnamNumber($opnamNumber);
                    return true;
                }else{
                    DB::rollBack();
                    return false;
                }
            }else{
                DB::rollBack();
                return false;
            }
        }catch(\Exception $e){
            dd($e);
            DB::rollBack();
            return false;
            // return Redirect::to("/transaksi/withdraw")->withError($e->getMessage());
        }
    }
}
