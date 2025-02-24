<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DataTables, Auth, DB;
use Validator,Redirect,Response;

class GeneralController extends Controller
{
    public function allMaterial(Request $request){
        $params      = $request->params;
        // $whereClause = $params['sac'];
        $query       = DB::table('v_material')->orderBy('material');
        return DataTables::queryBuilder($query)->toJson();
    }

    public function matstockAll(Request $request){
        $params      = $request->params;
        $whereClause = $params['sac'];
        $query       = DB::table('v_inv_stock')->orderBy('material');
        return DataTables::queryBuilder($query)->toJson();
    }

    public function matstockByWhs(Request $request, $whscode){
        $params      = $request->params;
        $whereClause = $params['sac'];
        $query       = DB::table('v_inv_stock')->where('whscode', $whscode)->orderBy('material');
        return DataTables::queryBuilder($query)->toJson();
    }

    public function summaryStokByWhs(Request $request, $whscode){
        $params      = $request->params;
        $whereClause = $params['sac'];
        $query       = DB::table('v_summary_stock_whs')->where('whscode', $whscode)->orderBy('material');
        return DataTables::queryBuilder($query)->toJson();
    }
}
