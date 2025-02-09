<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use DataTables, Auth, DB;
use Validator,Redirect,Response;
use PDF;

class ApproveGrpoController extends Controller
{
    public function index()
    {
        return view('transaksi.approvegrpo.approvelist');
    }

    public function detailGrPO($id){
        $checkData = DB::table('t_inv01')->where('id', $id)->first();
        if($checkData){
            $items = DB::table('v_detail_approval_grpo')
                        ->where('docnum', $checkData->docnum)
                        ->where('docyear', $checkData->docyear)
                        ->get();

            $approvals = DB::table('v_grpo_approval')
                        ->where('docnum', $checkData->docnum)
                        ->where('docyear', $checkData->docyear)
                        ->orderBy('approver_level','asc')
                        // ->orderBy('piditem','asc')
                        ->get();

            $isApprovedbyUser = DB::table('v_grpo_approval')
                        ->where('docnum', $checkData->docnum)
                        ->where('docyear', $checkData->docyear)
                        ->where('approver', Auth::user()->id)
                        ->where('is_active', 'Y')
                        ->first();
            // return $items;
            return view('transaksi.approvegrpo.approvedetails',
                [
                    'header'           => $checkData,
                    'items'            => $items,
                    'approvals'        => $approvals,
                    'isApprovedbyUser' => $isApprovedbyUser,
                ]);

        }else{
            return "Data not found";
        }
    }

    public function listOpenGRPO(Request $request){
        $query = DB::table('v_grpo_approval')
                 ->where('approver',Auth::user()->id)
                 ->where('is_active','Y')
                 ->where('approval_status','N')
                 ->orderBy('id', 'DESC');
        return DataTables::queryBuilder($query)
        ->editColumn('postdate', function ($query){
            return [
                'postdate' => \Carbon\Carbon::parse($query->postdate)->format('d-m-Y')
             ];
        })
        ->editColumn('docdate', function ($query){
            return [
                'docdate' => \Carbon\Carbon::parse($query->docdate)->format('d-m-Y')
             ];
        })
        ->toJson();
    }

    public function getNextApproval($dcnNum, $docyear){
        $userLevel = DB::table('t_movement_approval')
                    ->where('docnum', $dcnNum)
                    ->where('docyear', $docyear)
                    ->where('approver', Auth::user()->id)
                    ->first();

        $nextApproval = DB::table('t_movement_approval')
                        ->where('docnum', $dcnNum)
                        ->where('docyear', $docyear)
                        ->where('approver_level', '>', $userLevel->approver_level)
                        ->orderBy('approver_level', 'ASC')
                        ->first();

        // return $userLevel;
        if($nextApproval){
            return $nextApproval->approver_level;
        }else{
            return null;
        }
    }

    public function save(Request $req){
        // return $req;
        DB::beginTransaction();
        try{
            $check = DB::table('t_inv01')->where('id', $req->docid)->first();
            // $ptaNumber = $req->$dcnNum;
            // $check = DB::table('t_inv01')
            //          ->where('docnum', $dcnNum)
            //          ->where('docyear', $docyear)
            //          ->first();
            // return $check->approval_status;
            if($check){
                if($check->approval_status === "A"){
                    $result = array(
                        'msgtype' => '400',
                        'message' => 'Receipt PO : '. $check->docnum . ' tidak bisa di proses karena sudah di approve'
                    );
                    return $result;
                }
            }else{
                $result = array(
                    'msgtype' => '400',
                    'message' => 'Receive PO : '. $check->docnum . ' tidak ditemukan'
                );
                return $result;
            }
            $userAppLevel = DB::table('t_movement_approval')
                            ->select('approver_level','approval_status')
                            ->where('docnum', $check->docnum)
                            ->where('docyear', $check->docyear)
                            ->where('approver', Auth::user()->id)
                            ->first();
            // return $userAppLevel;
            if($userAppLevel->approval_status === "A"){
                $result = array(
                    'msgtype' => '400',
                    'message' => 'Receive PO : '. $check->docnum . ' tidak bisa di proses karena sudah di approve'
                );
                return $result;
            }


            if($req->action === "R"){
                DB::table('t_movement_approval')
                    ->where('docnum', $check->docnum)
                    ->where('docyear', $check->docyear)
                    // ->where('approver_level',$userAppLevel->approver_level)
                    ->update([
                        'approval_status' => 'R',
                        'approval_remark' => $req->approvernote,
                        'approved_by'     => Auth::user()->username,
                        'approval_date'   => getLocalDatabaseDateTime()
                ]);

                DB::table('t_movement_approval')
                ->where('docnum', $check->docnum)
                ->where('docyear', $check->docyear)
                    ->update([
                        'is_active' => 'N'
                    ]);

                DB::table('t_inv01')
                ->where('docnum', $check->docnum)
                ->where('docyear', $check->docyear)
                ->update([
                    'approval_status'   => 'R'
                ]);

                $grItems = DB::table('t_inv02_app')
                    ->where('docnum', $check->docnum)
                    ->where('docyear', $check->docyear)
                    ->get();

                foreach($grItems as $row){
                    $poItem = DB::table('t_po02')
                              ->where('ponum', $row->ponum)
                              ->where('poitem', $row->poitem)
                              ->first();
                    if($poItem){
                        $poItem->grqty = $poItem->grqty - $row->quantity;
                        if($poItem->grqty == 0){
                            DB::table('t_po02')
                              ->where('ponum', $row->ponum)
                              ->where('poitem', $row->poitem)
                              ->update([
                                    'grqty'    => 0,
                                    'grstatus' => 'O'
                              ]);
                        }else{
                            DB::table('t_po02')
                              ->where('ponum', $row->ponum)
                              ->where('poitem', $row->poitem)
                              ->update([
                                    'grqty' => $poItem->grqty,
                                    'grstatus' => 'O'
                              ]);
                        }
                    }
                }

                // DB::table('t_inv02_app')
                //     ->where('docnum', $check->docnum)
                //     ->where('docyear', $check->docyear)
                //     ->delete();

                DB::commit();
                $result = array(
                    'msgtype' => '200',
                    'message' => 'Receive PO : '. $check->docnum . ' berhasil di reject'
                );
                return $result;
            }else{
                DB::table('t_movement_approval')
                    ->where('docnum', $check->docnum)
                    ->where('docyear', $check->docyear)
                    ->where('approver_level',$userAppLevel->approver_level)
                    ->update([
                        'approval_status' => $req->action,
                        'approval_remark' => $req->approvernote,
                        'approved_by'     => Auth::user()->username,
                        'approval_date'   => getLocalDatabaseDateTime()
                ]);

                $nextApprover = $this->getNextApproval($check->docnum, $check->docyear);
                if($nextApprover  != null){
                    DB::table('t_movement_approval')
                    ->where('docnum', $check->docnum)
                    ->where('docyear', $check->docyear)
                    ->where('approver_level', $nextApprover)
                    ->update([
                        'is_active' => 'Y'
                    ]);
                }

                $checkIsFullApprove = DB::table('t_movement_approval')
                                        ->where('docnum', $check->docnum)
                                        ->where('docyear', $check->docyear)
                                        ->where('approval_status', '!=', 'A')
                                        ->get();
                if(sizeof($checkIsFullApprove) > 0){
                    // go to next approver
                }else{
                    //Full Approve
                    DB::table('t_inv01')
                    ->where('docnum', $check->docnum)
                    ->where('docyear', $check->docyear)
                    ->update([
                        'approval_status'   => 'A'
                    ]);

                    DB::select('call sp_InsertApprovedGrPO("'. $check->docnum .'", "'. $check->docyear .'")');

                    $grItems = DB::table('t_inv02_app')
                    ->where('docnum', $check->docnum)
                    ->where('docyear', $check->docyear)
                    ->get();

                    foreach($grItems as $row){
                        DB::table('t_inv_batch_stock')->insert([
                            'material'     => $row->material,
                            'whscode'      => $row->whscode,
                            'batchnum'     => $row->batch_number,
                            'quantity'     => $row->quantity,
                            'unit'         => $row->unit,
                            'last_udpate'  => getLocalDatabaseDateTime()
                        ]);

                        DB::table('t_inv_stock')->insert([
                            'material'     => $row->material,
                            'whscode'      => $row->whscode,
                            'batchnum'     => $row->batch_number,
                            'quantity'     => $row->quantity,
                            'unit'         => $row->unit,
                            'last_udpate'  => getLocalDatabaseDateTime()
                        ]);
                    }
                }

                DB::commit();
                $result = array(
                    'msgtype' => '200',
                    'message' => 'Receive PO : '. $check->docnum . ' berhasil di approve'
                );
                return $result;
            }
        }
        catch(\Exception $e){
            DB::rollBack();
            // dd($e);
            $result = array(
                'msgtype' => '500',
                'message' => $e->getMessage()
            );
            return $result;
        }
    }

    public function saveApproveItems(Request $req){
        DB::beginTransaction();
        try{

            $ptaNumber = $req->pidnumber;

            DB::commit();
            $result = array(
                'msgtype' => '200',
                'message' => 'Stock Opnam : '. $ptaNumber . ' berhasil di approve'
            );
            return $result;
        }
        catch(\Exception $e){
            DB::rollBack();
            $result = array(
                'msgtype' => '500',
                'message' => $e->getMessage()
            );
            return $result;
        }
    }

    public function postPIDDocument($pidNumber){
        DB::beginTransaction();
        try{
            $pidData = DB::table('v_stock_opname_detail')
                ->where('pidnumber', $pidNumber)
                ->orWhere('id', $pidNumber)
                ->get();

            // dd($_POST);
            $postDate = date('Y-m-d');
            $bulan  = date('m');
            $tahun  = date('Y');
            $prefix = 'PID';
            $ptaNumber = generateNextNumber($prefix, 'PID', $tahun, $bulan, '');

            DB::table('t_inv01')->insert([
                'docnum'            => $ptaNumber,
                'docyear'           => $tahun,
                'docdate'           => $postDate,
                'postdate'          => $postDate,
                'received_by'       => Auth::user()->username,
                'movement_code'     => '661',
                'remark'            => 'Stock Opnam '. $pidNumber,
                'refdoc'            => $pidNumber,
                'createdon'         => getLocalDatabaseDateTime(),
                'createdby'         => Auth::user()->email ?? Auth::user()->username
            ]);


            $count = 0;
            foreach ($pidData as $index => $row) {
                // Kosongin Existing Stock
                DB::table('t_inv_stock')
                    ->where('material', $row->material)
                    ->where('whscode', $row->whsid)
                    ->update([
                        'quantity' => 0
                    ]);

                DB::table('t_inv_batch_stock')
                    ->where('material', $row->material)
                    ->where('whscode', $row->whsid)
                    ->update([
                        'quantity' => 0
                    ]);
                // dd($row);
                $batchNumber = generateBatchNumber();
                $count = $count + 1;
                $insertData = array();
                $excelData = array(
                    'docnum'       => $ptaNumber,
                    'docyear'      => $tahun,
                    'docitem'      => $count,
                    'movement_code'=> '661',
                    'material'     => $row->material,
                    'matdesc'      => $row->matdesc,
                    'batch_number' => $batchNumber,
                    'quantity'     => $row->actual_qty,
                    'unit'         => $row->matunit,
                    'unit_price'   => $row->unit_price,
                    'total_price'  => $row->total_price,
                    'whscode'      => $row->whsid,
                    'shkzg'        => '+',
                    'createdon'    => getLocalDatabaseDateTime(),
                    'createdby'    => Auth::user()->email ?? Auth::user()->username

                );
                array_push($insertData, $excelData);
                insertOrUpdate($insertData,'t_inv02');

                DB::table('t_inv_batch_stock')->insert([
                    'material'     => $row->material,
                    'whscode'      => $row->whsid,
                    'batchnum'     => $batchNumber,
                    'quantity'     => $row->actual_qty,
                    'unit'         => $row->matunit,
                    'last_udpate'  => getLocalDatabaseDateTime()
                ]);

                DB::table('t_inv_stock')->insert([
                    'material'     => $row->material,
                    'whscode'      => $row->whsid,
                    'batchnum'     => $batchNumber,
                    'quantity'     => $row->actual_qty,
                    'unit'         => $row->matunit,
                    'last_udpate'  => getLocalDatabaseDateTime()
                ]);
            }

            DB::commit();

            $result = array(
                'msgtype' => '200',
                'message' => 'Success'
            );
            return $result;
        }catch(\Exception $e){
            DB::rollBack();
            // dd($e);
            $result = array(
                'msgtype' => '500',
                'message' => $e->getMessage()
            );
            return $result;
        }
    }

    // Buat Transaksi Negatif untuk existing Stock supaya stock Balance
    public function postOldDocument($pidNumber){
        DB::beginTransaction();
        try{
            $pidData = DB::table('v_stock_opname_detail')
                ->where('pidnumber', $pidNumber)
                ->orWhere('id', $pidNumber)
                ->get();

            // dd($_POST);
            $postDate = date('Y-m-d');
            $bulan    = date('m');
            $tahun    = date('Y');
            $prefix   = 'ISSUEPID';
            $ptaNumber = generateNextNumber($prefix, 'ISSUEPID', $tahun, $bulan, '');

            // Create Inventory Movement Negatif untuk meng 0 kan stock Lama
            foreach ($pidData as $index => $row) {
                $oldItems = DB::table('t_inv_stock')
                            ->where('material', $row->material)
                            ->where('whscode', $row->whsid)
                            ->where('quantity', '>', 0)
                            ->get();
                $count = 0;
                $insertData = array();
                foreach($oldItems as $olddata => $old){
                    $count = $count + 1;
                    $excelData = array(
                        'docnum'       => $ptaNumber,
                        'docyear'      => $tahun,
                        'docitem'      => $count,
                        'movement_code'=> '201',
                        'material'     => $row->material,
                        'matdesc'      => $row->matdesc,
                        'batch_number' => $old->batchnum ?? 'BATCHOPNAM',
                        'quantity'     => $old->quantity ?? 0,
                        'unit'         => $row->matunit,
                        'unit_price'   => $row->unit_price ?? 0,
                        'total_price'  => $row->total_price ?? 0,
                        'whscode'      => $row->whsid,
                        'shkzg'        => '-',
                        'createdon'    => getLocalDatabaseDateTime(),
                        'createdby'    => Auth::user()->email ?? Auth::user()->username

                    );
                    array_push($insertData, $excelData);
                }
                if(sizeof($insertData) > 0){
                    insertOrUpdate($insertData,'t_inv02');
                    DB::commit();
                }
            }

            DB::table('t_inv01')->insert([
                'docnum'            => $ptaNumber,
                'docyear'           => $tahun,
                'docdate'           => $postDate,
                'postdate'          => $postDate,
                'received_by'       => Auth::user()->username,
                'movement_code'     => '201',
                'remark'            => 'Stock Opnam Stok Lama '. $pidNumber,
                'refdoc'            => $pidNumber,
                'createdon'         => getLocalDatabaseDateTime(),
                'createdby'         => Auth::user()->email ?? Auth::user()->username
            ]);
            DB::commit();

            $result = array(
                'msgtype' => '200',
                'message' => 'Success'
            );
            return $result;
        }catch(\Exception $e){
            DB::rollBack();
            // dd($e);
            $result = array(
                'msgtype' => '500',
                'message' => $e->getMessage()
            );
            return $result;
        }
    }
}
