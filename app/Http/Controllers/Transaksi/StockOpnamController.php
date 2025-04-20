<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Imports\StockOpnamImport;
use Illuminate\Http\Request;
use DataTables, Auth, DB;
use Validator,Redirect,Response;
use Excel;

class StockOpnamController extends Controller
{
    public function index()
    {
        return view('transaksi.opnam.index');
    }

    public function viewlist()
    {
        return view('transaksi.opnam.opnamlist');
    }

    public function details()
    {

    }

    public function getAttachment($id){
        $header = DB::table('t_stock_opnam01')->where('id', $id)->first();
        if($header){
            $attachment = DB::table('t_attachments')
                ->where('doc_object', 'OPNAM')
                ->where('doc_number', $header->pidnumber)
                ->first();
            if($attachment){
                return $attachment;
            }else{
                return null;
            }
        }else{
            return null;
        }
    }

    public function stockOpnameDetails($id)
    {
        $query = DB::table('v_stock_opname_detail')
                 ->where('id', $id);
        $query->orderBy('id', 'ASC');
        return DataTables::queryBuilder($query)
        ->editColumn('quantity', function ($query){
            return [
                'qty1' => number_format($query->quantity,0)
            ];
        })
        ->editColumn('unit_price', function ($query){
            return [
                'uprice' => number_format($query->unit_price,0)
            ];
        })
        ->editColumn('total_price', function ($query){
            return [
                'total' => number_format($query->total_price,0)
            ];
        })
        ->toJson();
    }

    public function getApprovalStatus($id)
    {
        $pidDoc = DB::table('t_stock_opnam01')->where('id', $id)->first();

        $query = DB::table('v_opnam_approval')
                ->where('pidnumber', $pidDoc->pidnumber);

        $query->orderBy('id', 'ASC');
        return DataTables::queryBuilder($query)->toJson();
    }

    public function opnamlist(Request $req)
    {
        // t_stock_opnam01
        $strDate  = $req->strdate;
        $endDate  = $req->enddate;


        $query = DB::table('t_stock_opnam01');

        if(isset($req->strdate) && isset($req->enddate)){
            $query->whereBetween('piddate', [$strDate, $endDate]);
        }else{
            if(isset($req->strdate)){
                $query->where('piddate', $strDate);
            }

            if(isset($req->enddate)){
                $query->where('piddate', '<=', $endDate);
            }
        }

        $query->orderBy('id', 'ASC');

        return DataTables::queryBuilder($query)
        ->editColumn('piddate', function ($query){
            return [
                'piddate' => \Carbon\Carbon::parse($query->piddate)->format('d-m-Y')
             ];
        })
        ->toJson();
    }

    public function saveUploadOpname(Request $request){
        // return $request;
        // dd($_POST);
        $this->validate($request, [
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);

        $file = $request->file('file');

        // membuat nama file unik
        $nama_file = $file->hashName();

        $destinationPath = 'excel/';
        $file->move($destinationPath,$file->getClientOriginalName());

        config(['excel.import.startRow' => 2]);
        // import data
        $import = Excel::import(new StockOpnamImport($request), 'excel/'.$file->getClientOriginalName());

        //remove from server
		// unlink('excel/'.$file->getClientOriginalName());


        if($import) {
            return Redirect::to("/logistic/stockopname")->withSuccess('Data Stock Opnam Berhasil di Upload');
        } else {
            return Redirect::to("/logistic/stockopname")->withError('Error');
        }
    }
}
