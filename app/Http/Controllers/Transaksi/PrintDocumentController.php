<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DataTables, Auth, DB;
use Validator,Redirect,Response;
use PDF;

class PrintDocumentController extends Controller
{
    public function prlist(){
        $department = DB::table('t_department')->get();
        return view('transaksi.pr.printlist', ['department' => $department]);
    }

    public function prdetail($id){
        $department = DB::table('t_department')->get();
        $prhdr = DB::table('t_pr01')->where('id', $id)->first();
        if(!$prhdr){
            return Redirect::to("/proc/pr/listpr")->withError("PR tidak ditemukan!");
        }else{
            $prdtl = DB::table('t_pr02')->where('prnum', $prhdr->prnum)->get();
            $attachments = DB::table('t_attachments')->where('doc_object','PR')->where('doc_number', $prhdr->prnum)->get();
            $approvals   = DB::table('v_pr_approval_v2')
                           ->where('prnum', $prhdr->prnum)
                           ->orderBy('approver_level', 'ASC')
                           ->orderBy('pritem', 'ASC')
                           ->get();

            $pbjNumber = DB::table('t_pr02')->where('prnum', $prhdr->prnum)->pluck('pbjnumber');
            $pbjAttachments = DB::table('t_attachments')->where('doc_object','PBJ')
                              ->whereIn('doc_number', $pbjNumber)->get();
            // return $attachments;
            return view('transaksi.pr.prdetail',
                [
                    'department'     => $department,
                    'prhdr'          => $prhdr,
                    'pritem'         => $prdtl,
                    'attachments'    => $attachments,
                    'approvals'      => $approvals,
                    'pbjAttachments' => $pbjAttachments
                ]);
        }
    }

    public function printprlist(Request $req){
        $query = DB::table('v_rpr')->select('id','prnum','prdate','approvestat','requestby','remark','deptname','createdby')->distinct();

        if(isset($req->department)){
            if($req->department !== 'All'){
                $query->where('deptid', $req->department);
            }
        }

        if(isset($req->approvalstat)){
            if($req->approvalstat === "O"){
                $query->where('approvestat', 'O');
            }elseif($req->approvalstat === "A"){
                $query->where('approvestat', 'A');
            }elseif($req->approvalstat === "R"){
                $query->where('approvestat', 'R');
            }
        }

        if(isset($req->datefrom) && isset($req->dateto)){
            $query->whereBetween('prdate', [$req->datefrom, $req->dateto]);
        }elseif(isset($req->datefrom)){
            $query->where('prdate', $req->datefrom);
        }elseif(isset($req->dateto)){
            $query->where('prdate', $req->dateto);
        }

        $checkObjAuth = DB::table('user_object_auth')
                        ->where('object_name', 'ALLOW_DISPLAY_ALL_DEPT')
                        ->where('object_val', 'Y')
                        ->where('userid', Auth::user()->id)
                        ->first();
        if($checkObjAuth){

        }else{
            $query->where('createdby', Auth::user()->email);
        }
        // $query->where('createdby', Auth::user()->email);

        $query->orderBy('id');

        return DataTables::queryBuilder($query)
        ->editColumn('prdate', function ($query){
            return [
                'prdate1' => \Carbon\Carbon::parse($query->prdate)->format('d-m-Y')
             ];
        })
        ->toJson();
    }

    public function printpr($id){
        $prhdr    = DB::table('t_pr01')->where('id', $id)->first();
        $prdtl    = DB::table('v_form_pr_dtl')->where('prnum', $prhdr->prnum)->get();

        $approval = DB::table('t_pr_approvalv2')
                    ->where('prnum', $prhdr->prnum)
                    ->where('approval_status', 'A')
                    ->orderBy('approver_level', 'DESC')
                    ->first();
        if($approval){
            $approveSign = DB::table('users')->where('id', $approval->approver)->first();
        }else{
            $approveSign = null;
        }

        $creatorSign = DB::table('users')->where('email', $prhdr->createdby)->first();

        $pdf = PDF::loadview('transaksi.pr.printpr',
            [
                'prhdr'       => $prhdr,
                'pritem'      => $prdtl,
                'project'     => $prdtl[0]->nmproject,
                'approval'    => $approval,
                'approveSign' => $approveSign,
                'creatorSign' => $creatorSign
            ]);
        // $pdf->setOptions(['isPhpEnabled' => true]);
        $pdf->set_option("enable_php", true);
        return $pdf->stream();
    }

    public function pbjlist(){
        return view('transaksi.pbj.list');
    }

    public function printpbj($id){
        $prhdr = DB::table('t_pbj01')->where('id', $id)->first();
        $prdtl = DB::table('t_pbj02')->where('pbjnumber', $prhdr->pbjnumber)->get();
        $logo = DB::table('general_setting')->where('setting_name', 'COMPANY_LOGO')->first();
        $project = DB::table('t_projects')->where('idproject', $prhdr->idproject)->first();
        if(!$project){
            $project = null;
        }

        $pbjUser = DB::table('users')->where('email', $prhdr->createdby)->first();

        $PBJApprover = DB::table('workflow_budget')
                ->where('object', 'PBJ')
                ->where('requester', $pbjUser->id ?? null)
                ->where('approver_level', 1)
                ->orderBy('approver_level','ASC')
                ->first();
        if($PBJApprover){
            $firstApprover     = DB::table('v_users')->where('id', $PBJApprover->approver)->first();
            $firstApprovalDate = DB::table('v_pbj_approval')
            ->where('approver', $PBJApprover->approver)
            ->where('pbjnumber', $prhdr->pbjnumber)
            ->orderBy('approval_date', 'DESC')
            ->first();
        }else{
            $firstApprover = null;
            $firstApprovalDate = null;
        }

        $PBJApprover = DB::table('workflow_budget')
                ->where('object', 'PBJ')
                ->where('requester', $pbjUser->id ?? null)
                ->where('approver_level', 2)
                ->orderBy('approver_level','ASC')
                ->first();
        if($PBJApprover){
            $secondApprover = DB::table('v_users')->where('id', $PBJApprover->approver)->first();
            $secondApprovalDate = DB::table('v_pbj_approval')
            ->where('approver', $PBJApprover->approver)
            ->where('pbjnumber', $prhdr->pbjnumber)
            ->orderBy('approval_date', 'DESC')
            ->first();
        }else{
            $secondApprover = null;
            $secondApprovalDate = null;
        }

        $PBJApprover = DB::table('workflow_budget')
                ->where('object', 'PBJ')
                ->where('requester', $pbjUser->id ?? null)
                ->where('approver_level', 3)
                ->orderBy('approver_level','ASC')
                ->first();
        if($PBJApprover){
            $thirdApprover = DB::table('v_users')->where('id', $PBJApprover->approver)->first();
            $thirdApprovalDate = DB::table('v_pbj_approval')
            ->where('approver', $PBJApprover->approver)
            ->where('pbjnumber', $prhdr->pbjnumber)
            ->orderBy('approval_date', 'DESC')
            ->first();
        }else{
            $thirdApprover = null;
            $thirdApprovalDate = null;
        }

        // $customPaper = array(0,0,567.00,283.80);
        $pdf = PDF::loadview('transaksi.pbj.printpbj',
            [
                'hdr'     => $prhdr,
                'item'    => $prdtl,
                'logo'    => $logo,
                'project' => $project,
                'firstApprover'  => $firstApprover,
                'secondApprover' => $secondApprover,
                'thirdApprover'  => $thirdApprover,
                'firstApprovalDate'  => $firstApprovalDate,
                'secondApprovalDate' => $secondApprovalDate,
                'thirdApprovalDate'  => $thirdApprovalDate
            ]
            )->setPaper('A5','landscape');
        // $pdf = ('P','mm','A5');
        // $pdf->setOptions(['isPhpEnabled' => true]);
        $pdf->set_option("enable_php", true);
        $pdf->render();
        return $pdf->stream();
    }

    public function polist(){
        $department = DB::table('t_department')->get();
        return view('transaksi.po.printpolist', ['department' => $department]);
    }

    public function printpolist(Request $req){
        $query = DB::table('v_polist2')
        ->select('id','ponum','podat','postat', 'approvestat','vendor','note','vendor_name','deptname', 'totalprice','createdby')
        ->distinct();

        if(isset($req->department)){
            if($req->department !== 'All'){
                $query->where('deptid', $req->department);
            }
        }

        if(isset($req->approvalstat)){
            if($req->approvalstat === "O"){
                $query->where('approvestat', 'O');
            }elseif($req->approvalstat === "A"){
                $query->where('approvestat', 'A');
            }elseif($req->approvalstat === "R"){
                $query->where('approvestat', 'R');
            }
        }

        if(isset($req->datefrom) && isset($req->dateto)){
            $query->whereBetween('podat', [$req->datefrom, $req->dateto]);
        }elseif(isset($req->datefrom)){
            $query->where('podat', $req->datefrom);
        }elseif(isset($req->dateto)){
            $query->where('podat', $req->dateto);
        }

        $checkObjAuth = DB::table('user_object_auth')
                        ->where('object_name', 'ALLOW_DISPLAY_ALL_DEPT')
                        ->where('object_val', 'Y')
                        ->where('userid', Auth::user()->id)
                        ->first();
        if($checkObjAuth){

        }else{
            $query->where('createdby', Auth::user()->email);
        }

        // $query->collate('utf8mb4_unicode_ci');
        $query->orderBy('id');

        return DataTables::queryBuilder($query)
        ->editColumn('podat', function ($query){
            return [
                'podat1' => \Carbon\Carbon::parse($query->podat)->format('d-m-Y')
             ];
        })
        ->editColumn('totalprice', function ($query){
            return [
                'total' => number_format($query->totalprice, 0)
             ];
        })
        ->toJson();
    }

    public function podetail($id){
        $department  = DB::table('t_department')->get();
        $pohdr       = DB::table('v_rpo')->where('id', $id)->first();
        if(!$pohdr){
            return Redirect::to("/proc/po/listpo")->withError("PO tidak ditemukan!");
        }
        $podtl       = DB::table('v_form_po_dtl')->where('ponum', $pohdr->ponum)->get();
        $costs       = DB::table('t_po03')->where('ponum', $pohdr->ponum)->get();
        $attachments = DB::table('t_attachments')->where('doc_object','PO')->where('doc_number', $pohdr->ponum)->get();
        $approvals   = DB::table('v_po_approval_v2')
                        ->where('ponum', $pohdr->ponum)
                        ->orderBy('approver_level', 'ASC')
                        ->orderBy('poitem', 'ASC')
                        ->get();

        $prNumber = DB::table('t_po02')->where('ponum', $pohdr->ponum)->pluck('prnum');
        $prAttachments = DB::table('t_attachments')->where('doc_object','PR')
                              ->whereIn('doc_number', $prNumber)->get();

        $pbjNumber = DB::table('t_pr02')->whereIn('prnum', $prNumber)->pluck('pbjnumber');
        $pbjAttachments = DB::table('t_attachments')->where('doc_object','PBJ')
                              ->whereIn('doc_number', $pbjNumber)->get();

        return view('transaksi.po.podetail',
            [
                'department'    => $department,
                'pohdr'         => $pohdr,
                'poitem'        => $podtl,
                'costs'         => $costs,
                'attachments'   => $attachments,
                'approvals'     => $approvals,
                'prAttachments' => $prAttachments,
                'pbjAttachments' => $pbjAttachments
            ]);
    }

    public function printpo($id){
        $pohdr = DB::table('v_rpo')->where('id', $id)->first();
        if($pohdr->is_posolar === 'Y'){
            $podtl = DB::table('v_po_solar_items')->where('ponum', $pohdr->ponum)->first();
        }else{
            $podtl = DB::table('v_form_po_dtl')
                    ->where('ponum', $pohdr->ponum)
                    ->where('approvestat', '!=', 'R')->get();
        }

        // return $podtl;

        $vendor = DB::table('t_vendor')->where('vendor_code', $pohdr->vendor)->first();
        $userPO = DB::table('users')->where('email', $pohdr->createdby)->first();

        $totalApprover = DB::table('v_po_approval_v2')
                        ->distinct('approver_level')
                        ->where('ponum', $pohdr->ponum)
                        ->count('approver_level');

        // return $totalApprover;
        $POApprover = DB::table('workflow_budget')
                ->where('object', 'PO')
                ->where('requester', $userPO->id)
                ->orderBy('approver_level','ASC')
                ->first();
                // return $POApprover;
        if($POApprover){
            $firstApprover = DB::table('v_users')->where('id', $POApprover->approver)->first();
            $firstApprovalDate = DB::table('v_po_approval_v2')
            ->where('approver_level','1')
            ->where('approval_status', '!=','R')
            ->where('ponum', $pohdr->ponum)
            ->orderBy('approval_date', 'DESC')
            ->first();

            // return $firstApprovalDate;
        }

        $POApprover = DB::table('workflow_budget')
                ->where('object', 'PO')
                ->where('requester', $userPO->id)
                ->where('approver_level','2')
                ->orderBy('approver_level','ASC')
                ->first();

        if($POApprover){
            $secondApprover = DB::table('v_users')->where('id', $POApprover->approver)->first();
            $secondApprovalDate = DB::table('v_po_approval_v2')
            ->where('approver_level','2')
            ->where('approval_status', '!=','R')
            ->where('ponum', $pohdr->ponum)
            ->orderBy('approval_date', 'DESC')
            ->first();
        }

        $POApprover = DB::table('workflow_budget')
                ->where('object', 'PO')
                ->where('requester', $userPO->id)
                ->where('approver_level','3')
                ->orderBy('approver_level','DESC')
                ->first();

        if($POApprover){
            $lastApprover = DB::table('v_users')->where('id', $POApprover->approver)->first();
            $lastApprovalDate = DB::table('v_po_approval_v2')
            ->where('approver_level','3')
            ->where('approval_status', '!=','R')
            ->where('ponum', $pohdr->ponum)
            ->orderBy('approval_date', 'DESC')
            ->first();
        }

        // Level 4
        $POApprover = DB::table('workflow_budget')
                ->where('object', 'PO')
                ->where('requester', $userPO->id)
                ->where('approver_level','4')
                ->orderBy('approver_level','DESC')
                ->first();

        if($POApprover){
            $lastApprover2 = DB::table('v_users')->where('id', $POApprover->approver)->first();
            $lastApprovalDate2 = DB::table('v_po_approval_v2')
            ->where('approver_level','4')
            ->where('approval_status', '!=','R')
            ->where('ponum', $pohdr->ponum)
            ->orderBy('approval_date', 'DESC')
            ->first();
        }

        // $pdf->add_info('Title', 'Print Purchase Order '. $pohdr->ponum);
        if($pohdr->is_posolar === 'Y'){
            $pdf = PDF::loadview('transaksi.po.formposolar',
            [
                'pohdr'          => $pohdr,
                'poitem'         => $podtl,
                'vendor'         => $vendor,
                'firstApprover'  => $firstApprover ?? null,
                'secondApprover' => $secondApprover ?? null,
                'lastApprover'   => $lastApprover ?? null,
                'lastApprover2'  => $lastApprover2 ?? null,
                'firstApprovalDate'  => $firstApprovalDate ?? null,
                'secondApprovalDate' => $secondApprovalDate ?? null,
                'lastApprovalDate'   => $lastApprovalDate ?? null,
                'lastApprovalDate2'  => $lastApprovalDate2 ?? null,
                'totalApprover'      => $totalApprover
            ]);
        }else{
            $pdf = PDF::loadview('transaksi.po.formpo',
            [
                'pohdr'          => $pohdr,
                'poitem'         => $podtl,
                'vendor'         => $vendor,
                'firstApprover'  => $firstApprover ?? null,
                'secondApprover' => $secondApprover ?? null,
                'lastApprover'   => $lastApprover ?? null,
                'lastApprover2'  => $lastApprover2 ?? null,
                'firstApprovalDate'  => $firstApprovalDate ?? null,
                'secondApprovalDate' => $secondApprovalDate ?? null,
                'lastApprovalDate'   => $lastApprovalDate ?? null,
                'lastApprovalDate2'  => $lastApprovalDate2 ?? null,
                'totalApprover'      => $totalApprover
            ]);
        }
        $pdf->set_option("enable_php", true);
        return $pdf->stream();

        // $pdf->setOptions(['isPhpEnabled' => true]);
        // return $pdf->download($pohdr->ponum. '.pdf');
        // $pdf = \App::make('dompdf.wrapper');
        // $pdf->setOptions("isPhpEnabled", true);
        // $pdf->setOptions(['isRemoteEnabled' => true]);
        // $pdf->setProtocol($_SERVER['DOCUMENT_ROOT']);
        // $pdf = PDF::loadview('transaksi.po.printpo', ['pohdr' => $pohdr, 'poitem' => $podtl]);
    }

    public function wolist(Request $req){
        return view('transaksi.spk.printlist');
    }

    public function wodetail($id){
        // return view('transaksi.spk.printlist');
        $wohdr = DB::table('v_spk01')->where('id', $id)->first();
        if($wohdr){
            $woitem     = DB::table('t_wo02')->where('wonum', $wohdr->wonum)->get();
            $mekanik    = DB::table('t_mekanik')->where('id', $wohdr->mekanik)->first();
            $warehouse  = DB::table('t_warehouse')->where('id', $wohdr->whscode)->first();
            $kendaraan  = DB::table('t_kendaraan')->where('id', $wohdr->license_number)->first();
            $attachments = DB::table('t_attachments')->where('doc_object', 'SPK')->where('doc_number', $wohdr->wonum)->get();
            // return $woitem;

            return view('transaksi.spk.printdetail',
                [
                    'spkhdr'      => $wohdr,
                    'spkitems'    => $woitem,
                    'mekanik'     => $mekanik,
                    'warehouse'   => $warehouse,
                    'kendaraan'   => $kendaraan,
                    'attachments' => $attachments
                ]);
        }else{
            return Redirect::to("/printdoc/wo")->withError('Data SPK/Work Order tidak ditemukan');
        }
    }

    public function printwo($id){
        $prhdr = DB::table('t_wo01')->where('id', $id)->first();
        $prdtl = DB::table('t_wo02')->where('wonum', $prhdr->wonum)->get();

        $pdf = PDF::loadview('transaksi.spk.printspk', ['prhdr' => $prhdr, 'pritem' => $prdtl]);
        return $pdf->stream();
    }

    public function grpo(){
        return view('transaksi.movement.grpolist');
    }

    public function grpolist(Request $req){
        $query = DB::table('v_report_grpo02');
        // ->select('id','docnum','postdate','received_by','vendor_name','remark')->distinct();

        if(isset($req->datefrom) && isset($req->dateto)){
            $query->whereBetween('postdate', [$req->datefrom, $req->dateto]);
        }elseif(isset($req->datefrom)){
            $query->where('postdate', $req->datefrom);
        }elseif(isset($req->dateto)){
            $query->where('postdate', $req->dateto);
        }

        // $query->where('createdby', Auth::user()->email);

        $query->orderBy('id');

        return DataTables::queryBuilder($query)
        ->editColumn('postdate', function ($query){
            return [
                'postdate1' => \Carbon\Carbon::parse($query->postdate)->format('d-m-Y')
             ];
        })
        ->toJson();
    }

    public function printgrpo($id){
        $pohdr = DB::table('v_report_grpo02')
                 ->where('id', $id)->first();
        $podtl = DB::table('t_inv02')->where('docnum', $pohdr->docnum)->get();

        $approval = DB::table('t_movement_approval')
                    ->where('docnum', $pohdr->docnum)
                    ->where('docyear', $pohdr->docyear)
                    ->where('approval_status', 'A')
                    ->orderBy('approver_level', 'DESC')
                    ->first();

        if($approval){
            $approveSign = DB::table('users')->where('id', $approval->approver)->first();
        }else{
            $approveSign = null;
        }

        $creatorSign = DB::table('users')->where('email', $pohdr->createdby)->first();

        $pdf = PDF::loadview('transaksi.movement.printgrpo',
        [
            'pohdr' => $pohdr,
            'poitem' => $podtl,
            'approval'    => $approval,
            'approveSign' => $approveSign,
            'creatorSign' => $creatorSign
        ]);
        return $pdf->stream();
    }

    public function grpodetail($id){
        $pohdr = DB::table('v_rgrpo')->select('id','docnum','postdate','received_by','vendor_name','remark')->where('id', $id)->first();
        $podtl = DB::table('v_rgrpo')->where('docnum', $pohdr->docnum)->get();
        return view('transaksi.movement.grpodetail',
        [
            'pohdr'       => $pohdr,
            'poitem'      => $podtl
        ]);
    }

    public function issuedlist(){

    }

    public function printissued(){

    }

    public function bast(){
        return view('transaksi.bast.list');
    }

    public function printbast($id){
        $pohdr = DB::table('v_bast_01')
                 ->where('id', $id)->first();
        if($pohdr){
            $podtl = DB::table('t_bast02')->where('bast_id', $pohdr->id)->get();

            $approval = DB::table('t_bast_approval')
                    ->where('no_bast', $pohdr->no_bast)
                    ->where('approval_status', 'A')
                    ->orderBy('approver_level', 'DESC')
                    ->first();
            if($approval){
                $approveSign = DB::table('users')->where('id', $approval->approver)->first();
            }else{
                $approveSign = null;
            }

            $creatorSign = DB::table('users')->where('email', $pohdr->createdby)->first();

            $pdf = PDF::loadview('transaksi.printout.bast',
                [
                    'pohdr'  => $pohdr,
                    'poitem' => $podtl,
                    'approval' => $approval,
                    'approveSign' => $approveSign,
                    'creatorSign' => $creatorSign
                ]);
            return $pdf->stream();
        }else{
            return "Data not found";
        }
    }
}
