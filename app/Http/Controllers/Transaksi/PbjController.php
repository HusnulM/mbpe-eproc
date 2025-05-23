<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Mail\NotifApprovePbjMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

use Carbon\Carbon;
use DataTables, Auth, DB;
use Validator,Redirect,Response;

class PbjController extends Controller
{
    public function index(){
        return view('transaksi.pbj.openwo');
    }

    public function create($id){
        $wodata     = DB::table('v_wo_to_pbj')->where('id', $id)->where('wo_status', 'A')->first();
        if($wodata){
            $woitems    = DB::table('t_wo02')->where('wonum', $wodata->wonum)->get();
            $mekanik    = DB::table('t_mekanik')->get();
            $department = DB::table('t_department')->get();
            $cklist     = DB::table('v_checklist_kendaraan')->where('no_checklist', $wodata->cheklistnumber)->first();
            $kendaraan  = DB::table('t_kendaraan')->where('id', $cklist->no_plat)->first();
            $warehouse  = DB::table('t_warehouse')->where('id', $wodata->whscode)->first();
            $periode    = DB::table('t_budget_period')
                          ->where('pstatus', 'A')->get();

            return view('transaksi.pbj.create',
                [
                    'wodata'     => $wodata,
                    'mekanik'    => $mekanik,
                    'department' => $department,
                    'cklist'     => $cklist,
                    'kendaraan'  => $kendaraan,
                    'woitems'    => $woitems,
                    'warehouse'  => $warehouse,
                    'periode'    => $periode
                ]);
        }else{
            return Redirect::to("/transaction/pbj")->withError('Data SPK/Work Order tidak ditemukan');
        }
    }

    public function createWithoueWO(){
        // $newDateTime = date('Y')+1;
        // dd($newDateTime);
        $mekanik    = DB::table('t_mekanik')->get();
        $department = DB::table('t_department')->get();
        $kendaraan  = DB::table('t_kendaraan')->get();

        $periode    = DB::table('t_budget_period')
                      ->where('pstatus', 'A')->get();
        return view('transaksi.pbj.createpbj',
            [
                'mekanik'    => $mekanik,
                'department' => $department,
                'kendaraan'  => $kendaraan,
                'periode'    => $periode
            ]);
    }

    public function duedatepbj(){
        return view('transaksi.pbj.duedatepbj');
    }

    public function detailWO($id){
        $wohdr = DB::table('v_spk01')->where('id', $id)->first();
        if($wohdr){
            $warehouse  = DB::table('t_warehouse')->where('id', $wohdr->whscode)->first();
            $woitem     = DB::table('t_wo02')->where('wonum', $wohdr->wonum)->get();
            $attachments = DB::table('t_attachments')->where('doc_object','SPK')->where('doc_number', $wohdr->wonum)->get();
            $approvals  = DB::table('v_wo_approval')->where('wonum', $wohdr->wonum)->get();

            return view('transaksi.pbj.detailwo',
                [
                    'prhdr'       => $wohdr,
                    'pritem'      => $woitem,
                    // 'mekanik'     => $mekanik,
                    'warehouse'   => $warehouse,
                    // 'kendaraan'   => $kendaraan,
                    'attachments' => $attachments,
                    'approvals'   => $approvals,
                ]);
        }else{
            return Redirect::to("/transaction/pbj")->withError('Data SPK/Work Order tidak ditemukan');
        }
    }

    public function changePBJ($id){
        $pbjhdr = DB::table('t_pbj01')->where('id', $id)->first();
        if($pbjhdr){
            if($pbjhdr->pbjtype == 1){
                $pbjitem     = DB::table('t_pbj02')->where('pbjnumber', $pbjhdr->pbjnumber)->get();
                $department  = DB::table('t_department')->get();
                $attachments = DB::table('t_attachments')->where('doc_object','PBJ')->where('doc_number', $pbjhdr->pbjnumber)->get();
                $mekanik     = DB::table('t_mekanik')->get();
                $cklist      = DB::table('v_checklist_kendaraan')->where('no_checklist', $pbjhdr->cheklistnumber)->first();
                $kendaraan   = DB::table('t_kendaraan')->where('id', $cklist->no_plat)->first();
                $project     = DB::table('t_projects')->where('idproject', $pbjhdr->idproject)->first();
                if(!$project){
                    $project = null;
                }


                if(sizeof($pbjitem) > 0){
                    $pbjwhs    = DB::table('t_warehouse')->where('id', $pbjitem[0]->whscode)->first();
                }else{
                    $pbjwhs = 0;
                }

                $approvals   = DB::table('v_pbj_approval')
                ->where('pbjnumber', $pbjhdr->pbjnumber)
                ->orderBy('approver_level','asc')
                ->orderBy('pbjitem', 'asc')
                ->get();

                // return $pbjhdr;
                return view('transaksi.pbj.change',
                    [
                        'department'  => $department,
                        'pbjhdr'      => $pbjhdr,
                        'pbjitem'     => $pbjitem ?? null,
                        'attachments' => $attachments,
                        'approvals'   => $approvals,
                        'mekanik'     => $mekanik,
                        'kendaraan'   => $kendaraan,
                        'project'     => $project,
                        'pbjwhs'      => $pbjwhs,
                    ]);
            }else{
                $pbjitem     = DB::table('t_pbj02')->where('pbjnumber', $pbjhdr->pbjnumber)->get();
                $department  = DB::table('t_department')->get();
                $attachments = DB::table('t_attachments')->where('doc_object','PBJ')->where('doc_number', $pbjhdr->pbjnumber)->get();
                $mekanik     = DB::table('t_mekanik')->get();
                // $cklist     = DB::table('v_checklist_kendaraan')->where('no_checklist', $pbjhdr->cheklistnumber)->first();
                $kendaraan  = DB::table('t_kendaraan')->where('no_kendaraan', $pbjhdr->unit_desc)->first();

                $pbjdept   = DB::table('t_department')->where('deptid', $pbjhdr->deptid)->first();

                if(sizeof($pbjitem) > 0){
                    $pbjwhs    = DB::table('t_warehouse')->where('id', $pbjitem[0]->whscode)->first();
                }else{
                    $pbjwhs = 0;
                }

                $project     = DB::table('t_projects')->where('idproject', $pbjhdr->idproject)->first();
                if(!$project){
                    $project = null;
                }

                $approvals   = DB::table('v_pbj_approval')
                ->where('pbjnumber', $pbjhdr->pbjnumber)
                ->orderBy('approver_level','asc')
                ->orderBy('pbjitem', 'asc')
                ->get();

                // return $pbjhdr;
                return view('transaksi.pbj.changePbj',
                    [
                        'department'  => $department,
                        'pbjhdr'      => $pbjhdr,
                        'pbjitem'     => $pbjitem ?? null,
                        'attachments' => $attachments,
                        'approvals'   => $approvals,
                        'mekanik'     => $mekanik,
                        'kendaraan'   => $kendaraan,
                        'pbjdept'     => $pbjdept,
                        'pbjwhs'      => $pbjwhs,
                        'project'     => $project
                    ]);
            }
        }else{
            return Redirect::to("/transaction/list/pbj")->withError('Dokumen PBJ tidak ditemukan');
        }
    }

    public function closePbjView(){
        return view('transaksi.pbj.closepbj');
    }

    public function getPbjItems(Request $req){
        $query = DB::table('v_pbj02');

        $query->where('pbjnumber', $req->pbjnumber);
        // $query->where('prcreated', 'N');
        // $query->where('wocreated', 'N');
        $query->where('bast_created', 'N');
        $query->where('pbj_status', '!=', 'C');
        $query->where('openqty', '>', 0);

        $query->orderBy('id');

        return $query->get();
    }

    public function listOpenPbj(Request $req){
        $query = DB::table('v_pbj02')
                ->select('id','pbjnumber','tgl_pbj','tujuan_permintaan','kepada',
                          'unit_desc','engine_model','createdby','department')
                ->distinct();
        $query->where('bast_created', 'N');
        $query->where('pbj_status', '!=', 'C');
        $query->where('openqty', '>', 0);
        $query->orderBy('id', 'DESC');

        return DataTables::queryBuilder($query)->toJson();
    }

    public function rabList($param){

        $tahun = substr($param,0,4);
        $bulan = substr($param,5,2);

        $bulan = ltrim($bulan, '0');

        $month = ((int)$bulan);

        // dd('Bulan '. $month . ' - Tahun ' . $tahun);
        $url     = 'https://mahakaryabangunpersada.com/rab/B807C072-05ADCCE0-C1C82376-3EC92EF1/';
        // $url     = 'https://mahakaryabangunpersada.com/rab/B807C072-05ADCCE0-C1C82376-3EC92EF1/'.$param;
        // $url     = 'https://mahakaryabangunpersada.com/rab/B807C072-05ADCCE0-C1C82376-3EC92EF1/';
        // https://mahakaryabangunpersada.com/rab/B807C072-05ADCCE0-C1C82376-3EC92EF1/
        // dd($url);
        $execapi = mbpAPI($url, 'B807C072-05ADCCE0-C1C82376-3EC92EF1', null);

        $response = json_decode($execapi, true);
        $response = json_decode($execapi);
        // return $response;
        // return collect($response);
        // dd($response->data);
        $rabItems = array();

        foreach($response->data as $key => $row){
            // dd($row);
            if($month == ((int)$row->bulan) && ((int)$tahun) == ((int)$row->tahun)){
                if($row->part_number !== "" && $row->part_number !== "000"){
                    // dd($row);
                    $data = array(
                        'id'          => $row->id,
                        'material'    => $row->part_number,
                        'matdesc'     => $row->item,
                        'partnumber'  => $row->part_number,
                        'partname'    => $row->item,
                        'availableQty'=> $row->qty,
                        'matunit'     => $row->satuan,
                        'kodebudget'  => $row->kodebudget,
                        'bulan'       => $row->bulan,
                        'tahun'       => $row->tahun,
                        'avg_price'   => '0',
                    );
                    array_push($rabItems, $data);
                }
            }
        }

        $rabItems = collect($rabItems)->sortBy('id')->values();

        return Datatables::of($rabItems)->addIndexColumn()->make(true);
        // return $response;
    }

    public function list(){
        $department = DB::table('t_department')->get();
        return view('transaksi.pbj.list', ['department' => $department]);
    }

    public function listOpenWO(){
        $query = DB::table('v_wo_to_pbj')
                 ->where('wo_status', 'A')
                 ->orderBy('id', 'DESC');
        return DataTables::queryBuilder($query)->toJson();
    }

    public function dataCekListTidakLayak(Request $request){
        if(isset($request->params)){
            $params = $request->params;
            $whereClause = $params['sac'];
        }
        $query = DB::table('v_checklist_kendaraan')->where('hasil_pemeriksaan','TIDAK LAYAK')->where('pbj_created', 'N')
                 ->orderBy('id');
        return DataTables::queryBuilder($query)->toJson();
    }

    public function listPBJ(Request $request){
        if(isset($request->params)){
            $params = $request->params;
            $whereClause = $params['sac'];
        }

        $checkObjAuth = DB::table('user_object_auth')
                        ->where('object_name', 'ALLOW_DISPLAY_ALL_DEPT')
                        ->where('object_val', 'Y')
                        ->where('userid', Auth::user()->id)
                        ->first();
        if($checkObjAuth){
            $query = DB::table('v_pbj01')
            ->orderBy('id', 'DESC');
        }else{
            $query = DB::table('v_pbj01')
                     ->where('createdby',Auth::user()->email)
                     ->orderBy('id', 'DESC');
        }
        return DataTables::queryBuilder($query)
        ->toJson();
    }

    public function listDuedatePBJ(Request $request){
        if(isset($request->params)){
            $params = $request->params;
            $whereClause = $params['sac'];
        }
        $query = DB::table('v_duedate_pbj')
                //  ->where('createdby',Auth::user()->email)
                 ->where('duedate','>','3')
                 ->where('prcreated','N')
                 ->orderBy('id');
        return DataTables::queryBuilder($query)
        // ->editColumn('amount', function ($query){
        //     return [
        //         'amount1' => number_format($query->amount,0)
        //      ];
        // })->editColumn('approved_amount', function ($query){
        //     return [
        //         'amount2' => number_format($query->approved_amount,0)
        //      ];
        // })
        ->toJson();
    }

    public function detailPBJ($id){
        $pbjhdr = DB::table('t_pbj01')->where('id', $id)->first();
        if($pbjhdr){
            $pbjitem     = DB::table('t_pbj02')->where('pbjnumber', $pbjhdr->pbjnumber)->get();
            $department  = DB::table('t_department')->get();
            $attachments = DB::table('t_attachments')->where('doc_object','PBJ')->where('doc_number', $pbjhdr->pbjnumber)->get();

            $pbjProject = DB::table('t_projects')->where('idproject', $pbjhdr->idproject)->first();
            if(!$pbjProject){
                $pbjProject = null;
            }

            // return $pbjitem;

            $approvals   = DB::table('v_pbj_approval')
            ->where('pbjnumber', $pbjhdr->pbjnumber)
            ->orderBy('approver_level','asc')
            ->orderBy('pbjitem', 'asc')
            ->get();
            return view('transaksi.pbj.pbjdetail',
                [
                    'department'    => $department,
                    'pbjhdr'        => $pbjhdr,
                    'pbjitem'       => $pbjitem,
                    'attachments'   => $attachments,
                    'approvals'     => $approvals,
                    'project'       => $pbjProject
                ]);
        }else{
            return Redirect::to("/transaction/list/pbj")->withError('Dokumen PBJ tidak ditemukan');
        }
    }

    public function budgetLists(Request $request){
        $params = $request->params;
        $whereClause = $params['sac'];
        $query = DB::table('t_budget')->orderBy('id');
        return DataTables::queryBuilder($query)
        ->editColumn('amount', function ($query){
            return [
                'amount1' => number_format($query->amount,0)
             ];
        })->editColumn('approved_amount', function ($query){
            return [
                'amount2' => number_format($query->approved_amount,0)
             ];
        })
        ->toJson();
    }

    public function saveClosePBJ(Request $req){
        DB::beginTransaction();
        try{
            $pbjItems = DB::table('t_pbj02')
                        ->where('pbjnumber', $req->pbjnumber)
                        ->where('bast_created', 'N')->get();

            DB::table('t_pbj01')
            ->where('pbjnumber', $req->pbjnumber)
            ->update([
                'pbj_status' => 'C'
            ]);

            foreach($pbjItems as $row){
                $totalRelQty = DB::table('t_pr02')
                               ->where('pbjnumber', $row->pbjnumber)
                               ->where('pbjitem', $row->pbjitem)
                               ->sum('quantity');

                DB::table('t_pbj02')
                    ->where('pbjnumber', $req->pbjnumber)
                    ->where('bast_created', 'N')
                    // ->where('openqty', '>', 0)
                    ->update([
                        'itemstatus'   => 'C',
                        'realized_qty' => $totalRelQty
                    ]);
            }
            DB::commit();
            $result = array(
                'msgtype' => '200',
                'message' => 'PBJ '. $req->pbjnumber .' berhasil di close'
            );
            return $result;
        }catch(\Exception $e){
            DB::rollBack();
            $result = array(
                'msgtype' => '500',
                'message' => $e->getMessage()
            );
            return $result;
        }
    }

    public function save(Request $req){
        // return $req;
        DB::beginTransaction();
        try{
            if(isset($req['parts'])){
                $parts    = $req['parts'];
                $partdsc  = $req['partdesc'];
                $quantity = $req['quantity'];
                $uom      = $req['uoms'];
                $figure   = $req['figures'];
                $remark   = $req['remarks'];
                $wonum    = $req['wonum'];
                $woitem   = $req['woitem'];
                $whscode  = $req['whscode'];
                $budgetcode  = $req['kodebudget'];

                $tgl   = substr($req['tglpbj'], 8, 2);
                $bulan = substr($req['tglpbj'], 5, 2);
                $tahun = substr($req['tglpbj'], 0, 4);
                // return $tgl . ' - ' . $bulan . ' - ' . $tahun;
                $department = DB::table('t_department')->where('department', $req['kepada'])->first();

                // $ptaNumber = generatePbjNumber($tahun, $department->deptid, $tgl);
                $ptaNumber = generatePbjNumber($tahun, Auth::user()->deptid ?? $department->deptid, $tgl);

                // return $ptaNumber;

                // $amount = $req['nominal'];
                // $amount = str_replace(',','',$amount);
                $PBJid = DB::table('t_pbj01')->insertGetId([
                    'pbjnumber'         => $ptaNumber,
                    'pbjtype'           => $req['pbjTYpe'],
                    'deptid'            => Auth::user()->deptid ?? null,
                    'tgl_pbj'           => $req['tglpbj'],
                    'tujuan_permintaan' => $req['requestto'],
                    'kepada'            => $req['kepada'],
                    'unit_desc'         => $req['unitdesc'],
                    'engine_model'      => $req['engine'],
                    'chassis_sn'        => $req['chassis'],
                    'reference'         => $req['refrensi'],
                    'requestor'         => $req['requestor'],
                    'type_model'        => $req['typeModel'],
                    'user'              => $req['user'],
                    'kode_brg_jasa'     => $req['kodeJasa'],
                    'engine_sn'         => $req['nginesn'],
                    'hm_km'             => $req['hmkm'] ?? 0,
                    'km'                => $req['km'] ?? 0,
                    // 'budget_cost_code'  => $req['budgetcode'],
                    'budget_cost_code'  => $budgetcode[0] ?? 'NONBUDGET',
                    'cheklistnumber'    => $req['checklistnum'] ?? null,
                    'idproject'         => $req['project'] ?? null,
                    'remark'            => $req['remark'],
                    'periode'           => $req['periode'],
                    'is_draft'          => $req['is_draft'],
                    'createdon'         => getLocalDatabaseDateTime(),
                    'createdby'         => Auth::user()->email ?? Auth::user()->username
                ]);



                $insertData = array();
                $pbjItems   = array();
                $count = 0;
                for($i = 0; $i < sizeof($parts); $i++){
                    $qty    = $quantity[$i];
                    $qty    = str_replace(',','',$qty);

                    $count = $count + 1;
                    $data = array(
                        'pbjnumber'    => $ptaNumber,
                        'pbjitem'      => $count,
                        'partnumber'   => $parts[$i],
                        'description'  => $partdsc[$i],
                        'quantity'     => $qty,
                        'realized_qty' => 0,
                        'unit'         => $uom[$i],
                        'figure'       => $figure[$i],
                        'remark'       => $remark[$i],
                        'wonum'        => $wonum[$i] ?? null,
                        'woitem'       => $woitem[$i] ?? 0,
                        // 'whscode'      => $whscode[$i],
                        'whscode'      => $whscode,
                        'budget_code'  => $budgetcode[$i] ?? 'NONBUDGET',
                        'createdon'    => getLocalDatabaseDateTime(),
                        'createdby'    => Auth::user()->email ?? Auth::user()->username
                    );
                    array_push($insertData, $data);
                    array_push($pbjItems, $data);
                }
                insertOrUpdate($insertData,'t_pbj02');

                DB::table('t_wo01')->where('wonum', $req['woNumber'])->update([
                    'pbj_created' => 'Y'
                ]);

                DB::table('t_wo02')->where('wonum', $req['woNumber'])->update([
                    'pbj_created' => 'Y'
                ]);

                // return $pbjItems;
                //Insert Attachments | t_attachments
                if(isset($req['efile'])){
                    $files = $req['efile'];
                    $insertFiles = array();

                    foreach ($files as $efile) {
                        $filename = $efile->getClientOriginalName();
                        $upfiles = array(
                            'doc_object' => 'PBJ',
                            'doc_number' => $ptaNumber,
                            'efile'      => $filename,
                            'pathfile'   => '/files/PBJ/'. $filename,
                            'createdon'  => getLocalDatabaseDateTime(),
                            'createdby'  => Auth::user()->username ?? Auth::user()->email
                        );
                        array_push($insertFiles, $upfiles);

                        // $efile->move(public_path().'/files/PBJ/', $filename);
                        $efile->move('files/PBJ/', $filename);
                    }
                    if(sizeof($insertFiles) > 0){
                        insertOrUpdate($insertFiles,'t_attachments');
                    }
                }
                // insertOrUpdate($insertFiles,'t_attachments');

                //Set Approval
                if($req['is_draft'] === 'N'){
                    $approval = DB::table('v_workflow_budget')->where('object', 'PBJ')->where('requester', Auth::user()->id)->get();
                    if(sizeof($approval) > 0){
                        for($a = 0; $a < sizeof($pbjItems); $a++){
                            $insertApproval = array();
                            foreach($approval as $row){
                                $is_active = 'N';
                                if($row->approver_level == 1){
                                    $is_active = 'Y';
                                }
                                $approvals = array(
                                    'pbjnumber'         => $ptaNumber,
                                    'pbjitem'           => $pbjItems[$a]['pbjitem'],
                                    'approver_level'    => $row->approver_level,
                                    'approver'          => $row->approver,
                                    'requester'         => Auth::user()->id,
                                    'is_active'         => $is_active,
                                    'createdon'         => getLocalDatabaseDateTime()
                                );
                                array_push($insertApproval, $approvals);
                            }
                            insertOrUpdate($insertApproval,'t_pbj_approval');
                        }
                    }

                    DB::table('t_checklist_kendaraan')->where('no_checklist',$req['checklistnum'])->update([
                        'pbj_created' => 'Y',
                        'pbjnumber'   => $ptaNumber
                    ]);
                    DB::commit();

                    $approverId = DB::table('v_workflow_budget')->where('object', 'PBJ')
                                ->where('requester', Auth::user()->id)
                                ->where('approver_level', '1')
                                ->pluck('approver');

                    $mailto = DB::table('users')
                        ->whereIn('id', $approverId)
                        ->pluck('email');

                    $dataApprovePBJ = DB::table('v_duedate_pbj')
                        ->where('pbjnumber', $ptaNumber)
                        ->orderBy('id')->get();

                    Mail::to($mailto)->queue(new NotifApprovePbjMail($dataApprovePBJ, $PBJid, $ptaNumber));
                }else{
                    DB::commit();
                }

                $result = array(
                    'msgtype' => '200',
                    'message' => 'PBJ Berhasil dibuat dengan Nomor : '. $ptaNumber
                );
                return $result;
            }else{
                $result = array(
                    'msgtype' => '400',
                    'message' => 'PBJ Item Belum di Pilih'
                );
                return $result;
            }
        } catch(\Exception $e){
            DB::rollBack();
            $result = array(
                'msgtype' => '400',
                'message' => $e->getMessage()
            );
            return $result;
        }
    }

    public function update(Request $req){
        // return $req;
        DB::beginTransaction();
        try{
            if(isset($req['parts'])){
                $ptaNumber = $req['pbjnumber'];

                $pbjHdr = DB::table('t_pbj01')->where('pbjnumber', $ptaNumber)->first();
                if(!$pbjHdr){
                    return 'PBJ Tidak ditemukan';
                }
                // return $ptaNumber;

                // $amount = $req['nominal'];
                // $amount = str_replace(',','',$amount);
                $budgetcode  = $req['kodebudget'];

                DB::table('t_pbj01')->where('pbjnumber', $ptaNumber)->update([
                    'deptid'            => Auth::user()->deptid,
                    'tgl_pbj'           => $req['tglpbj'],
                    'tujuan_permintaan' => $req['requestto'],
                    'kepada'            => $req['kepada'],
                    'unit_desc'         => $req['unitdesc'],
                    'engine_model'      => $req['engine'],
                    'chassis_sn'        => $req['chassis'],
                    'reference'         => $req['refrensi'],
                    'requestor'         => $req['requestor'],
                    'type_model'        => $req['typeModel'],
                    'user'              => $req['user'],
                    'kode_brg_jasa'     => $req['kodeJasa'],
                    'engine_sn'         => $req['nginesn'],
                    'hm_km'             => $req['hmkm'] ?? 0,
                    'km'                => $req['km'] ?? 0,
                    // 'budget_cost_code'  => $req['budgetcode'],
                    'budget_cost_code'  => $budgetcode[0] ?? 'NONBUDGET',
                    'remark'            => $req['remark'],
                    'periode'           => $req['periode'],
                    'idproject'         => $req['project'],
                    'is_draft'          => "N",
                ]);

                $parts    = $req['parts'];
                $partdsc  = $req['partdesc'];
                $quantity = $req['quantity'];
                $uom      = $req['uoms'];
                $figure   = $req['figures'];
                $remark   = $req['remarks'];
                $wonum    = $req['wonum'];
                $woitem   = $req['woitem'];
                $whscode  = $req['warehouse'];
                $pbjitem  = $req['pbjitem'];


                $insertData = array();
                $pbjItems   = array();
                $count = 0;
                for($i = 0; $i < sizeof($parts); $i++){
                    $qty    = $quantity[$i];
                    $qty    = str_replace(',','',$qty);

                    if($pbjitem[$i]){
                        $count = $pbjitem[$i];
                    }else{
                        $count += 1;
                    }

                    $data = array(
                        'pbjnumber'    => $ptaNumber,
                        'pbjitem'      => $count,
                        'partnumber'   => $parts[$i],
                        'description'  => $partdsc[$i],
                        'quantity'     => $qty,
                        'unit'         => $uom[$i],
                        'figure'       => $figure[$i],
                        'remark'       => $remark[$i],
                        'wonum'        => $wonum[$i] ?? null,
                        'woitem'       => $woitem[$i] ?? 0,
                        'whscode'      => $whscode[$i] ?? $req['whscode'],
                        'budget_code'  => $budgetcode[$i] ?? 'NONBUDGET',
                        'createdon'    => getLocalDatabaseDateTime(),
                        'createdby'    => Auth::user()->email ?? Auth::user()->username
                    );
                    array_push($insertData, $data);
                    array_push($pbjItems, $data);
                }
                insertOrUpdate($insertData,'t_pbj02');

                // return $pbjItems;
                //Insert Attachments | t_attachments
                if(isset($req['efile'])){
                    $files = $req['efile'];
                    $insertFiles = array();

                    foreach ($files as $efile) {
                        $filename = $efile->getClientOriginalName();
                        $upfiles = array(
                            'doc_object' => 'PBJ',
                            'doc_number' => $ptaNumber,
                            'efile'      => $filename,
                            'pathfile'   => '/files/PBJ/'. $filename,
                            'createdon'  => getLocalDatabaseDateTime(),
                            'createdby'  => Auth::user()->username ?? Auth::user()->email
                        );
                        array_push($insertFiles, $upfiles);

                        // $efile->move(public_path().'/files/PBJ/', $filename);
                        $efile->move('files/PBJ/', $filename);
                    }
                    if(sizeof($insertFiles) > 0){
                        insertOrUpdate($insertFiles,'t_attachments');
                    }
                }



                // insertOrUpdate($insertFiles,'t_attachments');

                //Set Approval
                $creator  = DB::table('users')->where('email',  $pbjHdr->createdby)->first();
                $approval = DB::table('v_workflow_budget')->where('object', 'PBJ')->where('requester', $creator->id)->get();
                if(sizeof($approval) > 0){
                    DB::table('t_pbj_approval')->where('pbjnumber', $ptaNumber)->delete();
                    // foreach($pbjItems as $pbitem){
                    for($a = 0; $a < sizeof($pbjItems); $a++){
                        $insertApproval = array();
                        foreach($approval as $row){
                            $is_active = 'N';
                            if($row->approver_level == 1){
                                $is_active = 'Y';
                            }
                            $approvals = array(
                                'pbjnumber'         => $ptaNumber,
                                'pbjitem'           => $pbjItems[$a]['pbjitem'],
                                'approver_level'    => $row->approver_level,
                                'approver'          => $row->approver,
                                'requester'         => Auth::user()->id,
                                'is_active'         => $is_active,
                                'createdon'         => getLocalDatabaseDateTime()
                            );
                            array_push($insertApproval, $approvals);
                        }
                        insertOrUpdate($insertApproval,'t_pbj_approval');
                    }
                }

                // DB::table('t_checklist_kendaraan')->where('no_checklist',$req['checklistnum'])->update([
                //     'pbj_created' => 'Y',
                //     'pbjnumber'   => $ptaNumber
                // ]);
                DB::commit();
                return Redirect::to("/transaction/list/pbj")->withSuccess('PBJ '. $ptaNumber .' Berhasil diupdate');
                // if($req['pbjTYpe'] === "1"){
                //     return Redirect::to("/transaction/pbj")->withSuccess('PBJ '. $ptaNumber .' Berhasil diupdate');
                // }else{
                //     return Redirect::to("/transaction/pbjtanpawo")->withSuccess('PBJ Berhasil dibuat dengan Nomor : '. $ptaNumber);
                // }
            }else{
                // if($req['pbjTYpe'] === "1"){
                    return Redirect::to("/transaction/pbj/list")->withError('PBJ Item Belum di Pilih');
                // }else{
                //     return Redirect::to("/transaction/pbjtanpawo")->withError('PBJ Item Belum di Pilih');
                // }

            }
        } catch(\Exception $e){
            DB::rollBack();
            dd($e);
            return Redirect::to("/transaction/list/pbj")->withError($e->getMessage());
        }
    }

    public function deletePBJ(Request $req){
        DB::beginTransaction();
        try{

        }catch(\Exception $e){
            DB::rollBack();
            $result = array(
                'msgtype' => '500',
                'message' => $e->getMessage()
            );
            return $result;
        }
    }

    public function deletePBJItem(Request $req){
        DB::beginTransaction();
        try{
            $checkApproval = DB::table('v_pbj_approval')
                ->where('pbjnumber', $req['pbjnumber'])
                ->where('pbjitem', $req['pbjitem'])
                ->where('approval_status', 'A')->first();

            if($checkApproval){
                $result = array(
                    'msgtype' => '500',
                    'message' => 'PBJ : '. $req['pbjnumber'] . ' sudah di approve, data tidak bisa dihapus'
                );
                return $result;
            }

            $checkBAST = DB::table('t_pbj02')
                ->where('pbjnumber', $req['pbjnumber'])
                ->where('pbjitem', $req['pbjitem'])
                ->where('bast_created', 'N')->first();

            if($checkBAST){
                DB::table('t_pbj02')->where('pbjnumber', $req['pbjnumber'])->where('pbjitem', $req['pbjitem'])->delete();
                DB::table('t_pbj_approval')->where('pbjnumber', $req['pbjnumber'])
                                           ->where('pbjitem', $req['pbjitem'])->delete();

                DB::commit();
                $result = array(
                    'msgtype' => '200',
                    'message' => 'Item PBJ : '. $req['pbjnumber'] . ' - ' . $req['pbjitem'] . ' berhasil dihapus'
                );

            }else{
                $result = array(
                    'msgtype' => '500',
                    'message' => 'Item PBJ : '. $req['prnum'] . ' - ' . $req['pbjitem'] . ' sudah dibuat BAST'
                );
            }

            // return Redirect::to("/approve/pr")->withSuccess('PR dengan Nomor : '. $ptaNumber . ' berhasil di approve');
            return $result;
        } catch(\Exception $e){
            DB::rollBack();
            $result = array(
                'msgtype' => '500',
                'message' => $e->getMessage()
            );
            return $result;
            // return Redirect::to("/proc/pr")->withError($e->getMessage());
            // dd($e->getMessage());
        }
    }

    public function resetRealizedPBJ()
    {
        $exec = resetPBJNotRealized();
        return $exec;
    }
}
