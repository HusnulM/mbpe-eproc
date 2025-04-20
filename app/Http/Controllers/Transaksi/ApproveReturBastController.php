<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use DataTables, Auth, DB;
use Validator,Redirect,Response;
use PDF;

class ApproveReturBastController extends Controller
{
    public function index()
    {
        return view('transaksi.approvertrbast.index');
    }

    public function detailReturBAST($id){
        // dd($id);
        $checkData = DB::table('v_retur_bast01')->where('id', $id)->first();
        if($checkData){
            // return $checkData;
            $items = DB::table('v_retur_bast02')
                        ->where('nota_retur', $checkData->nota_retur)
                        ->where('tahun', $checkData->tahun)
                        ->get();
            // return $items;
            $approvals = DB::table('v_retur_approval')
                        ->where('nota_retur', $checkData->nota_retur)
                        ->where('tahun', $checkData->tahun)
                        ->orderBy('approver_level','asc')
                        ->get();

            $isApprovedbyUser = DB::table('v_retur_approval')
                        ->where('nota_retur', $checkData->nota_retur)
                        ->where('tahun', $checkData->tahun)
                        ->where('approver', Auth::user()->id)
                        ->where('is_active', 'Y')
                        ->first();

            return view('transaksi.approvertrbast.details',
                [
                    'header'           => $checkData,
                    'items'            => $items,
                    'approvals'        => $approvals,
                    'isApprovedbyUser' => $isApprovedbyUser,
                ]);

        }else{
            return "data not found";
        }
    }

    public function openReturBastList(Request $request){
        $query = DB::table('v_retur_approval')
                 ->where('approver',Auth::user()->id)
                 ->where('is_active','Y')
                 ->where('approval_status','N')
                 ->orderBy('id', 'DESC');
        return DataTables::queryBuilder($query)
        ->editColumn('tgl_retur', function ($query){
            return [
                'tgl_retur' => \Carbon\Carbon::parse($query->tgl_retur)->format('d-m-Y')
             ];
        })
        ->toJson();
    }

    public function getNextApproval($dcnNum, $dcnYear){
        $userLevel = DB::table('t_retur_bast_approval')
                    ->where('nota_retur', $dcnNum)
                    ->where('tahun', $dcnYear)
                    ->where('approver', Auth::user()->id)
                    ->first();

        $nextApproval = DB::table('t_retur_bast_approval')
                        ->where('nota_retur', $dcnNum)
                        ->where('tahun', $dcnYear)
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
            // $returHeader = DB::table('t_retur_bast')->where('id', $req->docid)->first();
            // $ptaNumber = $returHeader->nota_retur;
            // $ptaNumber = $returHeader->nota_retur;
            $check = DB::table('t_retur_bast')
                     ->where('id', $req->docid)
                     ->first();
            // return $check->approval_status;
            if($check){
                if($check->approval_status === "A"){
                    $result = array(
                        'msgtype' => '400',
                        'message' => 'RETUR BAST : '. $check->nota_retur . ' tidak bisa di proses karena sudah di approve'
                    );
                    return $result;
                }
            }else{
                $result = array(
                    'msgtype' => '400',
                    'message' => 'RETUR BAST tidak ditemukan'
                );
                return $result;
            }
            $userAppLevel = DB::table('t_retur_bast_approval')
                            ->select('approver_level','approval_status')
                            ->where('nota_retur', $check->nota_retur)
                            ->where('tahun', $check->tahun)
                            ->where('approver', Auth::user()->id)
                            ->first();
            // return $userAppLevel;
            if($userAppLevel->approval_status === "A"){
                $result = array(
                    'msgtype' => '400',
                    'message' => 'RETUR BAST : '. $check->nota_retur . ' tidak bisa di proses karena sudah di approve'
                );
                return $result;
            }

            if($req->action === "R"){
                DB::table('t_retur_bast_approval')
                    ->where('nota_retur', $check->nota_retur)
                    ->where('tahun', $check->tahun)
                    ->update([
                        'approval_status' => 'R',
                        'approval_remark' => $req->approvernote,
                        'approved_by'     => Auth::user()->username,
                        'approval_date'   => getLocalDatabaseDateTime()
                ]);

                DB::table('t_retur_bast_approval')
                    ->where('nota_retur', $check->nota_retur)
                    ->where('tahun', $check->tahun)
                    ->update([
                        'is_active' => 'N'
                    ]);

                DB::table('t_retur_bast')
                ->where('nota_retur', $check->nota_retur)
                ->where('tahun', $check->tahun)
                ->update([
                    'approval_status'   => 'R'
                ]);

                DB::commit();
                $result = array(
                    'msgtype' => '200',
                    'message' => 'Retur BAST : '. $check->nota_retur . ' berhasil di reject'
                );
                return $result;
            }else{
                DB::table('t_retur_bast_approval')
                    ->where('nota_retur', $check->nota_retur)
                    ->where('tahun', $check->tahun)
                    ->where('approver_level',$userAppLevel->approver_level)
                    ->update([
                        'approval_status' => $req->action,
                        'approval_remark' => $req->approvernote,
                        'approved_by'     => Auth::user()->username,
                        'approval_date'   => getLocalDatabaseDateTime()
                ]);

                $nextApprover = $this->getNextApproval($check->nota_retur, $check->tahun);
                if($nextApprover  != null){
                    DB::table('t_retur_bast_approval')
                    ->where('nota_retur', $check->nota_retur)
                    ->where('tahun', $check->tahun)
                    ->where('approver_level', $nextApprover)
                    ->update([
                        'is_active' => 'Y'
                    ]);
                }

                $checkIsFullApprove = DB::table('t_retur_bast_approval')
                                            ->where('nota_retur', $check->nota_retur)
                                            ->where('tahun', $check->tahun)
                                          ->where('approval_status', '!=', 'A')
                                          ->get();
                if(sizeof($checkIsFullApprove) > 0){
                    // go to next approver
                }else{
                    //Full Approve
                    DB::table('t_retur_bast')
                    ->where('nota_retur', $check->nota_retur)
                    ->where('tahun', $check->tahun)
                    ->update([
                        'approval_status'   => 'A'
                    ]);

                    DB::table('t_inv01')->insert([
                        'docnum'            => $check->nota_retur,
                        'docyear'           => $check->tahun,
                        'docdate'           => date('Y-m-d'),
                        'postdate'          => date('Y-m-d'),
                        'received_by'       => $check->createdby,
                        'movement_code'     => '561',
                        'remark'            => $check->remark,
                        'createdon'         => getLocalDatabaseDateTime(),
                        'createdby'         => Auth::user()->email ?? Auth::user()->username
                    ]);

                    $returItems = DB::table('v_retur_bast02')
                                ->where('nota_retur', $check->nota_retur)
                                ->where('tahun', $check->tahun)
                                ->get();
                    $count = 0;
                    $insertData = array();
                    foreach($returItems as $row){
                        $batchNumber = generateBatchNumber();
                        $qty    = $row->quantity;
                        $qty    = str_replace(',','',$qty);

                        $issuedPrice = DB::table('t_inv02')
                                        ->where('wonum', $row->refdoc)
                                        ->where('woitem', $row->refdocitem)
                                        ->where('movement_code','201')
                                        ->orderBy('createdon', 'DESC')
                                        ->first();

                        $count = $count + 1;
                        $data = array(
                            'docnum'       => $check->nota_retur,
                            'docyear'      => $check->tahun,
                            'docitem'      => $count,
                            'movement_code'=> '561',
                            'material'     => $row->material,
                            'matdesc'      => $row->matdesc,
                            'batch_number' => $batchNumber,
                            'quantity'     => $qty,
                            'unit'         => $row->unit,
                            'unit_price'   => $issuedPrice->unit_price ?? 0,
                            'total_price'  => $issuedPrice->unit_price * $qty ?? 0,
                            'no_bast'      => $row->no_bast,
                            'bast_item'    => $row->bast_item ?? null,
                            'whscode'      => $row->whscode,
                            'shkzg'        => '+',
                            'remark'       => $row->remark,
                            // 'budget_code'  => $kodebudget[$i],
                            'createdon'    => getLocalDatabaseDateTime(),
                            'createdby'    => Auth::user()->email ?? Auth::user()->username
                        );
                        array_push($insertData, $data);

                        DB::table('t_inv_batch_stock')->insert([
                            'material'     => $row->material,
                            'whscode'      => $row->whscode,
                            'batchnum'     => $batchNumber,
                            'quantity'     => $qty,
                            'unit'         => $row->unit,
                            'last_udpate'  => getLocalDatabaseDateTime()
                        ]);

                        DB::table('t_inv_stock')->insert([
                            'material'     => $row->material,
                            'whscode'      => $row->whscode,
                            'batchnum'     => $batchNumber,
                            'quantity'     => $qty,
                            'unit'         => $row->unit,
                            'last_udpate'  => getLocalDatabaseDateTime()
                        ]);

                        $BastItem = DB::table('t_bast02')
                        ->where('no_bast', $row->no_bast)
                        ->where('id', $row->bast_item)->first();
                        if($BastItem){
                            $retQty = $BastItem->retur_qty + $qty;
                            DB::table('t_bast02')
                            ->where('no_bast', $row->no_bast)
                            ->where('id', $row->bast_item)
                            ->update([
                                'retur_qty'    => $retQty
                            ]);
                        }
                    }
                }
                DB::commit();
                $result = array(
                    'msgtype' => '200',
                    'message' => 'RETUR BAST : '. $check->nota_retur . ' berhasil di approve'
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
}
