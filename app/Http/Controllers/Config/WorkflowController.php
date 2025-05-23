<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DataTables, Auth, DB;
use Validator,Redirect,Response;

class WorkflowController extends Controller
{
    public function index(){
        $users  = DB::table('users')->orderBy('id', 'ASC')->get();
        $ctgrs  = DB::table('workflow_categories')->orderBy('id', 'ASC')->get();
        $groups = DB::table('workflow_groups')->orderBy('id', 'ASC')->get();
        $wfassignments = DB::table('v_workflow_assignments')->get();

        $budgetwf = DB::table('v_workflow_budget')->where('object', 'BUDGET')->get();
        $pbjwf    = DB::table('v_workflow_budget')->where('object', 'PBJ')->get();
        $spkwf    = DB::table('v_workflow_budget')->where('object', 'SPK')->get();
        $prwf     = DB::table('v_workflow_budget')->where('object', 'PR')->get();
        $powf     = DB::table('v_workflow_budget')->where('object', 'PO')->get();
        $opnamwf  = DB::table('v_workflow_budget')->where('object', 'OPNAM')->get();
        $grpowf   = DB::table('v_workflow_budget')->where('object', 'GRPO')->get();
        $bastwf   = DB::table('v_workflow_budget')->where('object', 'BAST')->get();
        $rtrbastwf   = DB::table('v_workflow_budget')->where('object', 'RETURBAST')->get();

        return view('config.approval.index',
            [ 'ctgrs' => $ctgrs, 'groups'   => $groups,
              'users' => $users, 'budgetwf' => $budgetwf,
              'pbjwf' => $pbjwf, 'spkwf'    => $spkwf,
              'prwf'  => $prwf,  'powf'     => $powf,
              'opnamwf' => $opnamwf, 'grpowf' => $grpowf,
              'bastwf' => $bastwf, 'rtrbastwf' => $rtrbastwf
            ]);
    }

    public function saveBudgetApproval(Request $req){
        DB::beginTransaction();
        try{
            $requester = $req['requester'];
            $approver  = $req['approver'];
            $applevel  = $req['applevel'];

            $insertData = array();
            for($i = 0; $i < sizeof($requester); $i++){
                $data = array(
                    'object'          => 'BUDGET',
                    'requester'       => $requester[$i],
                    'approver'        => $approver[$i],
                    'approver_level'  => $applevel[$i],
                    'createdon'       => date('Y-m-d H:m:s'),
                    'createdby'       => Auth::user()->email ?? Auth::user()->username
                );
                array_push($insertData, $data);
            }
            insertOrUpdate($insertData,'workflow_budget');
            DB::commit();
            return Redirect::to("/config/workflow")->withSuccess('Approval Budget Berhasil dibuat');
        } catch(\Exception $e){
            DB::rollBack();
            return Redirect::to("/config/workflow")->withError($e->getMessage());
        }
    }

    public function savePbjApproval(Request $req){
        DB::beginTransaction();
        try{
            $requester = $req['requester'];
            $approver  = $req['approver'];
            $applevel  = $req['applevel'];

            $insertData = array();
            for($i = 0; $i < sizeof($requester); $i++){
                $data = array(
                    'object'          => 'PBJ',
                    'requester'       => $requester[$i],
                    'approver'        => $approver[$i],
                    'approver_level'  => $applevel[$i],
                    'createdon'       => date('Y-m-d H:m:s'),
                    'createdby'       => Auth::user()->email ?? Auth::user()->username
                );
                array_push($insertData, $data);
            }
            insertOrUpdate($insertData,'workflow_budget');
            DB::commit();
            return Redirect::to("/config/workflow")->withSuccess('Approval PBJ Berhasil dibuat');
        } catch(\Exception $e){
            DB::rollBack();
            return Redirect::to("/config/workflow")->withError($e->getMessage());
        }
    }

    public function savePRApproval(Request $req){
        DB::beginTransaction();
        try{
            $requester = $req['requester'];
            $approver  = $req['approver'];
            $applevel  = $req['applevel'];

            $insertData = array();
            for($i = 0; $i < sizeof($requester); $i++){
                $data = array(
                    'object'          => 'PR',
                    'requester'       => $requester[$i],
                    'approver'        => $approver[$i],
                    'approver_level'  => $applevel[$i],
                    'createdon'       => date('Y-m-d H:m:s'),
                    'createdby'       => Auth::user()->email ?? Auth::user()->username
                );
                array_push($insertData, $data);
            }
            insertOrUpdate($insertData,'workflow_budget');
            DB::commit();
            return Redirect::to("/config/workflow")->withSuccess('Approval PR Berhasil dibuat');
        } catch(\Exception $e){
            DB::rollBack();
            return Redirect::to("/config/workflow")->withError($e->getMessage());
        }
    }

    public function savePOApproval(Request $req){
        DB::beginTransaction();
        try{
            $requester = $req['requester'];
            $approver  = $req['approver'];
            $applevel  = $req['applevel'];

            $insertData = array();
            for($i = 0; $i < sizeof($requester); $i++){
                $data = array(
                    'object'          => 'PO',
                    'requester'       => $requester[$i],
                    'approver'        => $approver[$i],
                    'approver_level'  => $applevel[$i],
                    'createdon'       => date('Y-m-d H:m:s'),
                    'createdby'       => Auth::user()->email ?? Auth::user()->username
                );
                array_push($insertData, $data);
            }
            insertOrUpdate($insertData,'workflow_budget');
            DB::commit();
            return Redirect::to("/config/workflow")->withSuccess('Approval PO Berhasil dibuat');
        } catch(\Exception $e){
            DB::rollBack();
            return Redirect::to("/config/workflow")->withError($e->getMessage());
        }
    }

    public function saveSPKApproval(Request $req){
        DB::beginTransaction();
        try{
            $requester = $req['requester'];
            $approver  = $req['approver'];
            $applevel  = $req['applevel'];

            $insertData = array();
            for($i = 0; $i < sizeof($requester); $i++){
                $data = array(
                    'object'          => 'SPK',
                    'requester'       => $requester[$i],
                    'approver'        => $approver[$i],
                    'approver_level'  => $applevel[$i],
                    'createdon'       => date('Y-m-d H:m:s'),
                    'createdby'       => Auth::user()->email ?? Auth::user()->username
                );
                array_push($insertData, $data);
            }
            insertOrUpdate($insertData,'workflow_budget');
            DB::commit();
            return Redirect::to("/config/workflow")->withSuccess('Approval SPK Berhasil dibuat');
        } catch(\Exception $e){
            DB::rollBack();
            return Redirect::to("/config/workflow")->withError($e->getMessage());
        }
    }

    public function saveopnamwf(Request $req)
    {
        DB::beginTransaction();
        try{
            $requester = $req['requester'];
            $approver  = $req['approver'];
            $applevel  = $req['applevel'];

            $insertData = array();
            for($i = 0; $i < sizeof($requester); $i++){
                $data = array(
                    'object'          => 'OPNAM',
                    'requester'       => $requester[$i],
                    'approver'        => $approver[$i],
                    'approver_level'  => $applevel[$i],
                    'createdon'       => date('Y-m-d H:m:s'),
                    'createdby'       => Auth::user()->email ?? Auth::user()->username
                );
                array_push($insertData, $data);
            }
            insertOrUpdate($insertData,'workflow_budget');
            DB::commit();
            return Redirect::to("/config/workflow")->withSuccess('Approval Opnam Berhasil dibuat');
        } catch(\Exception $e){
            DB::rollBack();
            return Redirect::to("/config/workflow")->withError($e->getMessage());
        }
    }

    public function saveWF(Request $req)
    {
        DB::beginTransaction();
        try{
            $requester = $req['requester'];
            $approver  = $req['approver'];
            $applevel  = $req['applevel'];
            $wfobject  = $req['wfobject'];

            $insertData = array();
            for($i = 0; $i < sizeof($requester); $i++){
                $data = array(
                    'object'          => $wfobject,
                    'requester'       => $requester[$i],
                    'approver'        => $approver[$i],
                    'approver_level'  => $applevel[$i],
                    'createdon'       => date('Y-m-d H:m:s'),
                    'createdby'       => Auth::user()->email ?? Auth::user()->username
                );
                array_push($insertData, $data);
            }
            insertOrUpdate($insertData,'workflow_budget');
            DB::commit();
            return Redirect::to("/config/workflow")->withSuccess('Approval '. $wfobject .' Berhasil dibuat');
        } catch(\Exception $e){
            DB::rollBack();
            return Redirect::to("/config/workflow")->withError($e->getMessage());
        }
    }

    public function deleteBudgetwf($id){
        DB::beginTransaction();
        try{
            DB::table('workflow_budget')->where('id', $id)->delete();
            DB::commit();
            return Redirect::to("/config/workflow")->withSuccess('Approval Budget Berhasil dihapus');
        } catch(\Exception $e){
            DB::rollBack();
            return Redirect::to("/config/workflow")->withError($e->getMessage());
        }
    }

    public function deletePBJwf($id){
        DB::beginTransaction();
        try{
            DB::table('workflow_budget')->where('id', $id)->delete();
            DB::commit();
            return Redirect::to("/config/workflow")->withSuccess('Approval PBJ Berhasil dihapus');
        } catch(\Exception $e){
            DB::rollBack();
            return Redirect::to("/config/workflow")->withError($e->getMessage());
        }
    }

    public function deletePRwf($id){
        DB::beginTransaction();
        try{
            DB::table('workflow_budget')->where('id', $id)->delete();
            DB::commit();
            return Redirect::to("/config/workflow")->withSuccess('Approval PR Berhasil dihapus');
        } catch(\Exception $e){
            DB::rollBack();
            return Redirect::to("/config/workflow")->withError($e->getMessage());
        }
    }

    public function deleteSPKwf($id){
        DB::beginTransaction();
        try{
            DB::table('workflow_budget')->where('id', $id)->delete();
            DB::commit();
            return Redirect::to("/config/workflow")->withSuccess('Approval WO Berhasil dihapus');
        } catch(\Exception $e){
            DB::rollBack();
            return Redirect::to("/config/workflow")->withError($e->getMessage());
        }
    }

    public function deletePOwf($id){
        DB::beginTransaction();
        try{
            DB::table('workflow_budget')->where('id', $id)->delete();
            DB::commit();
            return Redirect::to("/config/workflow")->withSuccess('Approval PO Berhasil dihapus');
        } catch(\Exception $e){
            DB::rollBack();
            return Redirect::to("/config/workflow")->withError($e->getMessage());
        }
    }

    public function deleteOpnamWf($id){
        DB::beginTransaction();
        try{
            DB::table('workflow_budget')->where('id', $id)->delete();
            DB::commit();
            return Redirect::to("/config/workflow")->withSuccess('Approval PO Berhasil dihapus');
        } catch(\Exception $e){
            DB::rollBack();
            return Redirect::to("/config/workflow")->withError($e->getMessage());
        }
    }

    public function deleteGrpoWF($id){
        DB::beginTransaction();
        try{
            DB::table('workflow_budget')->where('id', $id)->delete();
            DB::commit();
            return Redirect::to("/config/workflow")->withSuccess('Approval Receipt PO Berhasil dihapus');
        } catch(\Exception $e){
            DB::rollBack();
            return Redirect::to("/config/workflow")->withError($e->getMessage());
        }
    }

    public function deleteBASTWF($id){
        DB::beginTransaction();
        try{
            DB::table('workflow_budget')->where('id', $id)->delete();
            DB::commit();
            return Redirect::to("/config/workflow")->withSuccess('Approval BAST PO Berhasil dihapus');
        } catch(\Exception $e){
            DB::rollBack();
            return Redirect::to("/config/workflow")->withError($e->getMessage());
        }
    }
}
