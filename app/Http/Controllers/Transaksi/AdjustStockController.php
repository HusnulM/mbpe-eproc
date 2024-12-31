<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Imports\AdjustStockImport;
use DataTables, Auth, DB;
use Validator,Redirect,Response;
use Excel;

class AdjustStockController extends Controller
{
    public function stockout(){
        return view('transaksi.adjustment.adjuststock');
    }

    public function saveAdjStockOut(Request $request){
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
        $import = Excel::import(new AdjustStockImport(), 'excel/'.$file->getClientOriginalName());

        //remove from server
		// unlink('excel/'.$file->getClientOriginalName());

        if($import) {
            return Redirect::to("/inbal/stock")->withSuccess('Data Stock Berhasil di Upload');
        } else {
            return Redirect::to("/inbal/stock")->withError('Error');
        }
    }
}
