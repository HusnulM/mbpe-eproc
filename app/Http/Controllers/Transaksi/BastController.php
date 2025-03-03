<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DataTables, Auth, DB;
use Validator,Redirect,Response;

class BastController extends Controller
{
    public function index(){
        // resetPBJNotRealized();
        return view('transaksi.bast.index');
    }

    public function create($pbjID){
        $pbjheader = DB::table('v_pbj_to_bast')->where('id', $pbjID)->first();
        if($pbjheader){
            $pbjitems = DB::table('t_pbj02')->where('pbjnumber', $pbjheader->pbjnumber)
                        ->where('approvestat', 'A')
                        ->Where('bast_created', 'N')
                        ->get();
            // return $pbjitems;
            return view('transaksi.bast.create',[
                'pbjheader' => $pbjheader,
                'pbjitems'  => $pbjitems
            ]);
        }else{
            return Redirect::to("/logistic/bast"); //return view('transaksi.bast.index');
        }
    }

    public function viewListBast(){
        return view('transaksi.bast.list');
    }

    public function detailBAST($id){
        $header = DB::table('v_bast_01')->where('id', $id)->first();
        $items  = DB::table('t_bast02')->where('bast_id', $id)->get();
        $attachment = DB::table('t_attachments')->where('doc_object', 'BAST')
                     ->where('doc_number', $id)->first();

        if(!$attachment){
            $attachment = null;
        }
        return view('transaksi.bast.detail',[
            'header' => $header,
            'items'  => $items,
            'file'   => $attachment
        ]);
    }

    function listDataBast(Request $req){
        $params = $req->params;
        $whereClause = $params['sac'];
        $query = DB::table('v_bast_01');

        if(isset($req->datefrom) && isset($req->dateto)){
            $query->whereBetween('tanggal_bast', [$req->datefrom, $req->dateto]);
        }elseif(isset($req->datefrom)){
            $query->where('tanggal_bast', $req->datefrom);
        }elseif(isset($req->dateto)){
            $query->where('tanggal_bast', '<=', $req->dateto);
        }

        $query->orderBy('id');

        return DataTables::queryBuilder($query)
        ->toJson();
    }

    public function dataBast(Request $request){
        $params = $request->params;
        $whereClause = $params['sac'];
        $query = DB::table('v_pbj_to_bast')->orderBy('id');
        return DataTables::queryBuilder($query)
        ->toJson();
    }

    public function findUser(Request $request){
        $query['data'] = DB::table('users')->select('id','name','email','username')
        ->where('name', 'like', '%'. $request->search . '%')->get();
        return $query;
    }

    public function save(Request $req){
        // return $req;
        $this->resetQtyPBJ($req);
        DB::beginTransaction();
        try{
            // resetPBJNotRealized();
            $parts    = $req['material'];
            $partdsc  = $req['matdesc'];
            $quantity = $req['bastquantity'];
            $uom      = $req['unit'];
            $pbjnum   = $req['pbjnumber'];
            $pbjitm   = $req['pbjitem'];
            $wonum    = $req['wonum'];

            $is_error = "0";

            $bulan = date('m');
            $tahun = date('Y');
            $prefix = 'BAST';
            $bastNumber = generateNextNumber($prefix, 'BAST', $tahun, $bulan, '');

            $checkNoBAST = DB::table('t_bast01')
                        //    ->where('no_bast',$req['nomorbast'])->first();
                           ->where('no_bast',$bastNumber)->first();
            if($checkNoBAST){
                $is_error = "1";
                $result = array(
                    'msgtype' => '400',
                    'message' => 'Nomor BAST '. $bastNumber . ' sudah ada'
                );
                return $result;
            }




            // return $wonum;
            $insertData = array();
            $count = 0;

            $pbjheader = DB::table('t_pbj01')->where('pbjnumber', $pbjnum[0])->first();

            $approval = DB::table('v_workflow_budget')->where('object', 'BAST')->where('requester', Auth::user()->id)->get();
            if(sizeof($approval) > 0){

                $bastID = DB::table('t_bast01')->insertGetId([
                    'no_bast'         => $bastNumber,
                    'userid_pemberi'  => Auth::user()->id,
                    'userid_penerima' => $req['penerima'],
                    'tanggal_bast'    => $req['tglbast'],
                    'remark'          => $req['remark'],
                    'approval_status' => 'N',
                    'createdon'       => getLocalDatabaseDateTime(),
                    'createdby'       => Auth::user()->email ?? Auth::user()->username
                ]);

                $insertApproval = array();
                foreach($approval as $row){
                    $is_active = 'N';
                    if($row->approver_level == 1){
                        $is_active = 'Y';
                    }
                    $approvals = array(
                        'no_bast'           => $bastNumber,
                        'approver_level'    => $row->approver_level,
                        'approver'          => $row->approver,
                        'requester'         => Auth::user()->id,
                        'is_active'         => $is_active,
                        'createdon'         => getLocalDatabaseDateTime()
                    );
                    array_push($insertApproval, $approvals);
                }
                if(sizeof($insertApproval) > 0){
                    insertOrUpdate($insertApproval,'t_bast_approval');
                }
            }else{
                $bastID = DB::table('t_bast01')->insertGetId([
                    'no_bast'         => $bastNumber,
                    'userid_pemberi'  => Auth::user()->id,
                    'userid_penerima' => $req['penerima'],
                    'tanggal_bast'    => $req['tglbast'],
                    'remark'          => $req['remark'],
                    'createdon'       => getLocalDatabaseDateTime(),
                    'createdby'       => Auth::user()->email ?? Auth::user()->username
                ]);

                $ptaNumber = generateIssueNumber(date('Y'), date('m'));
                DB::table('t_inv01')->insert([
                    'docnum' => $ptaNumber,
                    'docyear' => date('Y'),
                    'docdate' => date('Y-m-d'),
                    'postdate' => date('Y-m-d'),
                    'movement_code' => '201',
                    'remark' => 'Issued BAST',
                    'createdon'         => date('Y-m-d H:m:s'),
                    'createdby'         => Auth::user()->email ?? Auth::user()->username
                ]);
            }

            for($i = 0; $i < sizeof($parts); $i++)
            {
                if($quantity[$i] > 0)
                {
                    $qty    = 0;
                    $qty    = $quantity[$i];
                    $qty    = str_replace(',','',$qty);
                    $inputQty = $qty;
                    $qty = (int)$qty;
                    $warehouseID = 0;

                    if($pbjheader->pbjtype == 1){
                        if($wonum[$i]){
                            $wodata = DB::table('t_wo01')->where('wonum', $wonum[$i])->first();
                            $warehouseID = $wodata->whscode;
                        }else{
                            $pbjdtl = DB::table('t_pbj02')
                            ->where('pbjnumber', $pbjnum[$i])
                            ->where('pbjitem', $pbjitm[$i])->first();
                            $warehouseID = $pbjdtl->whscode;

                            $qty = (int)$qty + (int)$pbjdtl->realized_qty;
                        }
                        $latestStock = DB::table('v_inv_summary_stock')
                                       ->where('material', $parts[$i])
                                       ->where('whsid',    $warehouseID)->first();

                        if($latestStock){
                            if((int)$latestStock->quantity < (int)$inputQty)
                            {
                                $is_error = "1";
                                DB::rollBack();
                                $result = array(
                                    'msgtype' => '400',
                                    'message' => 'Stock Tidak Mencukupi untuk part : '. $parts[$i]
                                );
                                return $result;
                            }
                        }else{
                            $is_error = "1";
                            DB::rollBack();
                            $result = array(
                                'msgtype' => '400',
                                'message' => 'Stock Tidak Mencukupi untuk part : '. $parts[$i]
                            );
                            return $result;
                        }

                        if((int)$pbjdtl->quantity < (int)$qty)
                        {
                            $is_error = "1";
                            DB::rollBack();
                            $openQty = (int)$pbjdtl->quantity - (int)$pbjdtl->realized_qty;
                            $result = array(
                                'msgtype' => '400',
                                'message' => 'Quantity BAST lebih besar dari Open Quantity PBJ Partnumber '. $parts[$i] . '. Open Quantity ( '. $openQty . ' )'
                            );
                            return $result;
                        }
                    }else{
                        $pbjdtl = DB::table('t_pbj02')
                                ->where('pbjnumber', $pbjnum[$i])
                                ->where('pbjitem', $pbjitm[$i])->first();

                        $qty = (int)$qty + (int)$pbjdtl->realized_qty;

                        $warehouseID = $pbjdtl->whscode;
                        $latestStock = DB::table('v_inv_summary_stock')
                                       ->where('material', $parts[$i])
                                       ->where('whsid',    $pbjdtl->whscode)->first();
                        if($latestStock){
                            if((int)$latestStock->quantity < (int)$inputQty){
                                $is_error = "1";
                                DB::rollBack();
                                $result = array(
                                    'msgtype' => '400',
                                    'message' => 'Stock Tidak Mencukupi untuk part : '. $parts[$i]
                                );
                                return $result;
                            }
                        }else{
                            $is_error = "1";
                            DB::rollBack();
                            $result = array(
                                'msgtype' => '400',
                                'message' => 'Stock Tidak Mencukupi untuk part : '. $parts[$i]
                            );
                            return $result;
                        }

                        if((int)$pbjdtl->quantity < (int)$qty){
                            $is_error = "1";
                            $openQty = (int)$pbjdtl->quantity - (int)$pbjdtl->realized_qty;
                            DB::rollBack();
                            $result = array(
                                'msgtype' => '400',
                                'message' => 'Quantity BAST lebih besar dari Open Quantity PBJ Partnumber '. $parts[$i] . '. Open Quantity ( '. $openQty . ' )'
                            );
                            return $result;
                        }
                    }

                    if ($is_error == "1")
                    {
                        $result = array(
                            'msgtype' => '400',
                            'message' => 'Gagal Create BAST'
                        );
                        return $result;
                    }else{
                        $matdesc = str_replace('"','\"',$partdsc[$i]);

                        $data = array(
                            'bast_id'      => $bastID,
                            'no_bast'      => $bastNumber,
                            'material'     => $parts[$i],
                            'matdesc'      => $partdsc[$i],
                            'quantity'     => $inputQty,
                            'unit'         => $uom[$i],
                            'refdoc'       => $pbjnum[$i] ?? 0,
                            'refdocitem'   => $pbjitm[$i] ?? 0,
                            'createdon'    => getLocalDatabaseDateTime(),
                            'createdby'    => Auth::user()->email ?? Auth::user()->username
                        );
                        array_push($insertData, $data);


                        $matdesc = str_replace('"','\"',$partdsc[$i]);
                        $matCode = str_replace('"','\"',$parts[$i]);
                        if(sizeof($approval) == 0){
                            DB::select('call spIssueMaterialWithBatchFIFO(
                                "'. $matCode .'",
                                "'. $warehouseID .'",
                                "'. $inputQty .'",
                                "'. $ptaNumber .'",
                                "'. date('Y') .'",
                                "201",
                                "'. $matdesc .'",
                                "'. $uom[$i] .'",
                                "-",
                                "'. $pbjnum[$i] .'",
                                "'. $pbjitm[$i] .'",
                                "'. Auth::user()->email .'",
                                "'. $bastNumber .'",
                                "'. $bastID .'")');
                        }

                        $pbjitem = DB::table('t_pbj02')
                            ->where('pbjnumber', $pbjnum[$i])
                            ->where('pbjitem', $pbjitm[$i])->first();

                        $relQty = $pbjitem->realized_qty + $inputQty;
                        if((int)$relQty >= (int)$pbjitem->quantity){
                            DB::table('t_pbj02')->where('pbjnumber', $pbjnum[$i])->where('pbjitem', $pbjitm[$i])
                            ->update([
                                'itemstatus'   => 'C',
                                'bast_created' => 'Y',
                                'realized_qty' => $relQty
                            ]);
                        }else{
                            DB::table('t_pbj02')->where('pbjnumber', $pbjnum[$i])->where('pbjitem', $pbjitm[$i])
                            ->update([
                                'realized_qty' => $relQty
                            ]);
                        }
                    }
                }
            }
            if(sizeof($insertData) > 0){
                insertOrUpdate($insertData,'t_bast02');
            }

            //Insert Attachments | t_attachments
            if(isset($req['efile'])){
                $files = $req['efile'];
                $insertFiles = array();

                foreach ($files as $efile) {
                    $filename = $efile->getClientOriginalName();
                    $upfiles = array(
                        'doc_object' => 'BAST',
                        'doc_number' => $bastID,
                        'efile'      => $filename,
                        'pathfile'   => '/files/BAST/'. $filename,
                        'createdon'  => getLocalDatabaseDateTime(),
                        'createdby'  => Auth::user()->username ?? Auth::user()->email
                    );
                    array_push($insertFiles, $upfiles);

                    $efile->move('files/BAST/', $filename);
                }
                if(sizeof($insertFiles) > 0){
                    insertOrUpdate($insertFiles,'t_attachments');
                }
            }
            DB::commit();

            sleep(2);

            $checkBastAll = DB::table('t_pbj02')
                            ->where('pbjnumber', $pbjheader->pbjnumber)
                            ->where('bast_created', 'N')->first();
            if(!$checkBastAll)
            {
                DB::table('t_pbj01')->where('pbjnumber', $pbjheader->pbjnumber)
                    ->update([
                        'bast_created' => 'Y'
                    ]);
            }

            DB::commit();
            $result = array(
                'msgtype' => '200',
                'message' => 'BAST Berhasil dibuat dengan nomor '. $bastNumber
            );

            // $this->resetQtyPBJ($req);
            return $result;

        } catch(\Exception $e){
            DB::rollBack();
            $result = array(
                'msgtype' => '400',
                'message' => $e->getMessage()
            );
            // $this->resetQtyPBJ($req);
            return $result;
            // dd($e);
            // return Redirect::to("/logistic/bast")->withError($e->getMessage());
            // return Redirect::to("/logistic/bast/create/".$req['pbjID'])->withError($e->getMessage());
        }
    }

    function resetQtyPBJ($req)
    {
        $pbjnum   = $req['pbjnumber'];
        $pbjitm   = $req['pbjitem'];

        for($i = 0; $i < sizeof($pbjnum); $i++){
            $pbjN = DB::table('v_check_pbj')
                    ->where('realized_qty', '>', '0')
                    ->where('pbjnumber', $pbjnum[$i])
                    ->where('pbjitem', $pbjitm[$i])
                    ->get();
            foreach($pbjN as $row){
                $check = DB::table('t_inv02')
                        ->where('wonum', $row->pbjnumber)
                        ->where('woitem', $row->pbjitem)
                        ->first();
                if(!$check){
                    DB::table('t_pbj02')
                    ->where('pbjnumber', $row->pbjnumber)
                    ->where('pbjitem', $row->pbjitem)
                    ->update([
                        'realized_qty' => 0
                    ]);
                    DB::commit();
                }
            }
        }
    }

    function mysql_escape_mimic($inp) {

        if(is_array($inp))
            return array_map(__METHOD__, $inp);

        if(!empty($inp) && is_string($inp)) {
            return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
        }

        return $inp;
    }
}
