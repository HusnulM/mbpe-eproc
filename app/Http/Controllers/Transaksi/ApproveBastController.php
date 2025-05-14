<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use DataTables, Auth, DB;
use Validator,Redirect,Response;
use PDF;

class ApproveBastController extends Controller
{
    public function index()
    {
        return view('transaksi.approvebast.index');
    }

    public function detailBAST($id){
        // dd($id);
        $checkData = DB::table('v_bast_01')->where('id', $id)->first();
        if($checkData){
            $items = DB::table('t_bast02')
                        ->where('bast_id', $id)
                        ->get();

            $approvals = DB::table('v_bast_approval')
                        ->where('no_bast', $checkData->no_bast)
                        ->orderBy('approver_level','asc')
                        ->get();



            $isApprovedbyUser = DB::table('v_bast_approval')
                        ->where('no_bast',  $checkData->no_bast)
                        ->where('approver', Auth::user()->id)
                        ->where('is_active', 'Y')
                        ->first();

            return view('transaksi.approvebast.details',
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

    public function openBastList(Request $request){
        $query = DB::table('v_bast_approval')
                 ->where('approver',Auth::user()->id)
                 ->where('is_active','Y')
                 ->where('approval_status','N')
                 ->orderBy('id', 'DESC');
        return DataTables::queryBuilder($query)
        ->editColumn('tanggal_bast', function ($query){
            return [
                'tanggal_bast' => \Carbon\Carbon::parse($query->tanggal_bast)->format('d-m-Y')
             ];
        })
        ->toJson();
    }

    public function getNextApproval($dcnNum){
        $userLevel = DB::table('t_bast_approval')
                    ->where('no_bast', $dcnNum)
                    ->where('approver', Auth::user()->id)
                    ->first();

        $nextApproval = DB::table('t_bast_approval')
                        ->where('no_bast', $dcnNum)
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
            $ptaNumber = $req->no_bast;
            $check = DB::table('t_bast01')
                     ->where('no_bast', $ptaNumber)
                     ->first();
            // return $check->approval_status;
            if($check){
                if($check->approval_status === "A"){
                    $result = array(
                        'msgtype' => '400',
                        'message' => 'BAST : '. $ptaNumber . ' tidak bisa di proses karena sudah di approve'
                    );
                    return $result;
                }
            }else{
                $result = array(
                    'msgtype' => '400',
                    'message' => 'BAST : '. $ptaNumber . ' tidak ditemukan'
                );
                return $result;
            }
            $userAppLevel = DB::table('t_bast_approval')
                            ->select('approver_level','approval_status')
                            ->where('no_bast', $ptaNumber)
                            ->where('approver', Auth::user()->id)
                            ->first();
            // return $userAppLevel;
            if($userAppLevel->approval_status === "A"){
                $result = array(
                    'msgtype' => '400',
                    'message' => 'BAST : '. $ptaNumber . ' tidak bisa di proses karena sudah di approve'
                );
                return $result;
            }


            if($req->action === "R"){
                DB::table('t_bast_approval')
                    ->where('no_bast', $ptaNumber)
                    // ->where('approver_level',$userAppLevel->approver_level)
                    ->update([
                        'approval_status' => 'R',
                        'approval_remark' => $req->approvernote,
                        'approved_by'     => Auth::user()->username,
                        'approval_date'   => getLocalDatabaseDateTime()
                ]);

                DB::table('t_bast_approval')
                    ->where('no_bast', $ptaNumber)
                    ->update([
                        'is_active' => 'N'
                    ]);

                DB::table('t_bast01')->where('no_bast', $ptaNumber)->update([
                    'approval_status'   => 'R'
                ]);

                // Update PBJ BAST Qty
                $bastItems = DB::table('t_bast02')
                                 ->where('bast_id', $check->id)
                                 ->get();
                foreach($bastItems as $row){
                    $pbjItem = DB::table('t_pbj02')
                              ->where('pbjnumber', $row->refdoc)
                              ->where('pbjitem', $row->refdocitem)
                              ->first();
                    if($pbjItem){
                        $pbjItem->realized_qty = $pbjItem->realized_qty - $row->quantity;
                        if($pbjItem->realized_qty == 0){
                            DB::table('t_pbj02')
                            ->where('pbjnumber', $row->refdoc)
                            ->where('pbjitem', $row->refdocitem)
                            ->update([
                                  'realized_qty'    => 0,
                                  'bast_created'    => 'N'
                            ]);
                        }else{
                            DB::table('t_pbj02')
                            ->where('pbjnumber', $row->refdoc)
                            ->where('pbjitem', $row->refdocitem)
                            ->update([
                                  'realized_qty'    => $pbjItem->realized_qty,
                                  'bast_created'    => 'N'
                            ]);
                        }
                    }
                }
                DB::commit();
                $result = array(
                    'msgtype' => '200',
                    'message' => 'BAST : '. $ptaNumber . ' berhasil di reject'
                );
                return $result;
            }else{
                DB::table('t_bast_approval')
                    ->where('no_bast', $ptaNumber)
                    ->where('approver_level',$userAppLevel->approver_level)
                    ->update([
                        'approval_status' => $req->action,
                        'approval_remark' => $req->approvernote,
                        'approved_by'     => Auth::user()->username,
                        'approval_date'   => getLocalDatabaseDateTime()
                ]);

                $nextApprover = $this->getNextApproval($ptaNumber);
                if($nextApprover  != null){
                    DB::table('t_bast_approval')
                    ->where('no_bast', $ptaNumber)
                    ->where('approver_level', $nextApprover)
                    ->update([
                        'is_active' => 'Y'
                    ]);
                }

                $checkIsFullApprove = DB::table('t_bast_approval')
                                          ->where('no_bast', $ptaNumber)
                                          ->where('approval_status', '!=', 'A')
                                          ->get();
                if(sizeof($checkIsFullApprove) > 0){
                    // go to next approver
                }else{
                    //Full Approve
                    DB::table('t_bast01')->where('no_bast', $ptaNumber)->update([
                        'approval_status'   => 'A'
                    ]);

                    $bastItems = DB::table('t_bast02')
                                 ->where('bast_id', $check->id)
                                 ->get();

                    $issueDoc = generateIssueNumber(date('Y'), date('m'));
                    DB::table('t_inv01')->insert([
                        'docnum'   => $issueDoc,
                        'docyear'  => date('Y'),
                        'docdate'  => date('Y-m-d'),
                        'postdate' => date('Y-m-d'),
                        'movement_code' => '201',
                        'remark'        => 'Issued BAST',
                        'createdon'     => date('Y-m-d H:m:s'),
                        'createdby'     => Auth::user()->email ?? Auth::user()->username
                    ]);

                    $warehouseID = 1;
                    foreach($bastItems as $row){
                        $pbjdtl = DB::table('t_pbj02')
                            ->where('pbjnumber', $row->refdoc)
                            ->where('pbjitem',   $row->refdocitem)->first();
                        if($pbjdtl){
                            $warehouseID = $pbjdtl->whscode;
                        }

                        $matdesc = str_replace('"','\"',$row->matdesc);
                        $matCode = str_replace('"','\"',$row->material);

                        DB::select('call spIssueMaterialWithBatchFIFO(
                            "'. $row->material .'",
                            "'. $warehouseID .'",
                            "'. $row->quantity .'",
                            "'. $issueDoc .'",
                            "'. date('Y') .'",
                            "201",
                            "'. $matdesc .'",
                            "'. $row->unit .'",
                            "-",
                            "'. $row->refdoc .'",
                            "'. $row->refdocitem .'",
                            "'. Auth::user()->email .'",
                            "'. $row->no_bast .'",
                            "'. $row->bast_id .'")');
                    }
                }

                DB::commit();
                $result = array(
                    'msgtype' => '200',
                    'message' => 'BAST : '. $ptaNumber . ' berhasil di approve'
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
