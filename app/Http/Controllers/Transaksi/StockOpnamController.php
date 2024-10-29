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

    public function saveUploadOpname(Request $request){
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
        $import = Excel::import(new StockOpnamImport(), 'excel/'.$file->getClientOriginalName());

        //remove from server
		unlink('excel/'.$file->getClientOriginalName());

        if($import) {
            return Redirect::to("/logistic/stockopname")->withSuccess('Data Stock Opnam Berhasil di Upload');
        } else {
            return Redirect::to("/logistic/stockopname")->withError('Error');
        }
    }
}
