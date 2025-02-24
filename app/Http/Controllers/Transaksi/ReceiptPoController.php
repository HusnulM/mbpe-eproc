<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DataTables, Auth, DB;
use Validator,Redirect,Response;

class ReceiptPoController extends Controller
{
    public function index(){
        return view('transaksi.movement.grpo');
    }

    public function getApprovedPO(Request $request){
        if(isset($request->params)){
            $params = $request->params;
            $whereClause = $params['sac'];
        }
        $query = DB::table('v_approved_po')
                 ->where('grstatus', 'O')
                 ->orderBy('id');
        return DataTables::queryBuilder($query)->toJson();
    }

    public function save(Request $req){
        DB::beginTransaction();
        try{
            $tgl   = substr($req['grdate'], 8, 2);
            $bulan = substr($req['grdate'], 5, 2);
            $tahun = substr($req['grdate'], 0, 4);
            // return $tgl . ' - ' . $bulan . ' - ' . $tahun;
            $ptaNumber = generateGRPONumber($tahun, $bulan);

            // return $ptaNumber;

            // $amount = $req['nominal'];
            // $amount = str_replace(',','',$amount);
            DB::table('t_inv01')->insert([
                'docnum'            => $ptaNumber,
                'docyear'           => $tahun,
                'docdate'           => $req['grdate'],
                'postdate'          => $req['grdate'],
                'received_by'       => $req['recipient'],
                'movement_code'     => '101',
                'remark'            => $req['remark'],
                'createdon'         => getLocalDatabaseDateTime(),
                'createdby'         => Auth::user()->email ?? Auth::user()->username
            ]);

            $parts    = $req['parts'];
            $partdsc  = $req['partdesc'];
            $quantity = $req['quantity'];
            $uom      = $req['uoms'];
            $whscode  = $req['whscode'];
            $price    = $req['unitprice'];
            $ponum    = $req['ponum'];
            $poitem   = $req['poitem'];
            $kodebudget  = $req['kodebudget'];

            $insertData = array();
            $count = 0;

            for($i = 0; $i < sizeof($parts); $i++){
                $batchNumber = generateBatchNumber();
                $qty    = $quantity[$i];
                $qty    = str_replace(',','',$qty);

                $matPrice = $price[$i];
                $matPrice = str_replace(',','',$matPrice);

                $count = $count + 1;
                $data = array(
                    'docnum'       => $ptaNumber,
                    'docyear'      => $tahun,
                    'docitem'      => $count,
                    'movement_code'=> '101',
                    'material'     => $parts[$i],
                    'matdesc'      => $partdsc[$i],
                    'batch_number' => $batchNumber,
                    'quantity'     => $qty,
                    'unit'         => $uom[$i],
                    'unit_price'   => $matPrice,
                    'total_price'  => $matPrice*$qty,
                    'ponum'        => $ponum[$i] ?? null,
                    'poitem'       => $poitem[$i] ?? null,
                    'whscode'      => $whscode[$i],
                    'shkzg'        => '+',
                    'budget_code'  => $kodebudget[$i],
                    'createdon'    => getLocalDatabaseDateTime(),
                    'createdby'    => Auth::user()->email ?? Auth::user()->username
                );
                array_push($insertData, $data);

                // DB::table('t_inv_batch_stock')->insert([
                //     'material'     => $parts[$i],
                //     'whscode'      => $whscode[$i],
                //     'batchnum'     => $batchNumber,
                //     'quantity'     => $qty,
                //     'unit'         => $uom[$i],
                //     'last_udpate'  => getLocalDatabaseDateTime()
                // ]);

                // DB::table('t_inv_stock')->insert([
                //     'material'     => $parts[$i],
                //     'whscode'      => $whscode[$i],
                //     'batchnum'     => $batchNumber,
                //     'quantity'     => $qty,
                //     'unit'         => $uom[$i],
                //     'last_udpate'  => getLocalDatabaseDateTime()
                // ]);

                $POItemQty = DB::table('t_po02')
                ->where('ponum', $ponum[$i])
                ->where('poitem', $poitem[$i])->first();
                if($POItemQty){
                    if($POItemQty->quantity == ($qty+$POItemQty->grqty)){
                        DB::table('t_po02')
                        ->where('ponum',  $ponum[$i])
                        ->where('poitem', $poitem[$i])
                        ->update([
                            'grstatus' => 'F',
                            'grqty'    => $qty + $POItemQty->grqty
                        ]);
                    }else{
                        DB::table('t_po02')
                        ->where('ponum',  $ponum[$i])
                        ->where('poitem', $poitem[$i])
                        ->update([
                            'grqty'    => $qty + $POItemQty->grqty
                        ]);
                    }
                }
            }

            // GR PO Approval
            $approval = DB::table('v_workflow_budget')->where('object', 'GRPO')->where('requester', Auth::user()->id)->get();
            if(sizeof($approval) > 0){
                insertOrUpdate($insertData,'t_inv02_app');
                $insertApproval = array();
                foreach($approval as $row){
                    $is_active = 'N';
                    if($row->approver_level == 1){
                        $is_active = 'Y';
                    }
                    $approvals = array(
                        'docnum'            => $ptaNumber,
                        'docyear'           => $tahun,
                        'docitem'           => 0,
                        // 'docitem'           => $gr['docitem'],
                        'approver_level'    => $row->approver_level,
                        'approver'          => $row->approver,
                        'requester'         => Auth::user()->id,
                        'is_active'         => $is_active,
                        'createdon'         => getLocalDatabaseDateTime()
                    );
                    array_push($insertApproval, $approvals);
                }
                insertOrUpdate($insertApproval,'t_movement_approval');

                DB::commit();

                DB::table('t_inv01')
                ->where('docnum', $ptaNumber)
                ->where('docyear', $tahun)
                ->update([
                    'approval_status' => 'N'
                ]);

                DB::commit();
            }else{

                insertOrUpdate($insertData,'t_inv02');
                $qty = 0;
                for($i = 0; $i < sizeof($parts); $i++){
                    $qty    = $quantity[$i];
                    $qty    = str_replace(',','',$qty);

                    DB::table('t_inv_batch_stock')->insert([
                        'material'     => $parts[$i],
                        'whscode'      => $whscode[$i],
                        'batchnum'     => $batchNumber,
                        'quantity'     => $qty,
                        'unit'         => $uom[$i],
                        'last_udpate'  => getLocalDatabaseDateTime()
                    ]);

                    DB::table('t_inv_stock')->insert([
                        'material'     => $parts[$i],
                        'whscode'      => $whscode[$i],
                        'batchnum'     => $batchNumber,
                        'quantity'     => $qty,
                        'unit'         => $uom[$i],
                        'last_udpate'  => getLocalDatabaseDateTime()
                    ]);
                }
                DB::commit();
            }

            return Redirect::to("/logistic/terimapo")->withSuccess('Penerimaan PO Berhasil dengan Nomor : '. $ptaNumber);
        } catch(\Exception $e){
            // dd($e);
            DB::rollBack();
            return Redirect::to("/logistic/terimapo")->withError($e->getMessage());
            // dd($e->getMessage());
        }
    }
}
