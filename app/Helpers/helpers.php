<?php

use Illuminate\Support\Facades\DB;

$totalBaris = 0;
$opnamNumber = null;

function setOpnamNumber($Num){
    $opnamNumber = $Num;
}

function getOpnamNumber(){
    return $opnamNumber;
}

function resetPBJNotRealized(){
    DB::beginTransaction();
    try{
        $pbjN = DB::table('v_check_pbj')
                ->where('realized_qty', '>', '0')->get();
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
        // DB::select('call spPBJNotRealized()');
        // DB::commit();

        $result = array(
            'msgtype' => '200',
            'message' => 'Reset Realized PBJ Success'
        );
        return $result;
    }catch(\Exception $e){
        DB::rollBack();
        $result = array(
            'msgtype' => '400',
            'message' => $e->getMessage()
        );
        return $result;
    }
}

function setExcelRows($row){
    $totalBaris = $row;
    return $totalBaris;
}

function getExcelRows(){
    return $totalBaris;
}

function userMenu(){
    $mnGroups = DB::table('v_usermenus')
                ->select('menugroup', 'groupname', 'groupicon','group_idx')
                ->distinct()
                ->where('userid', Auth::user()->id)
                ->orderBy('group_idx','ASC')
                ->get();
    return $mnGroups;
}

function userSubMenu(){
    $mnGroups = DB::table('v_usermenus')
                ->select('menugroup', 'route', 'menu_desc','menu_idx', 'icon')
                ->distinct()
                ->where('userid', Auth::user()->id)
                ->orderBy('menu_idx','ASC')
                ->get();
    return $mnGroups;
}

function getLocalDatabaseDateTime(){
    // SELECT now()
    $localDateTime = DB::select('SELECT fGetDatabaseLocalDatetime() as lcldate');
    return $localDateTime[0]->lcldate;
}

function getTotalPricePO($ponum){
    // fGetTotalPricePO
    $totalPrice = DB::select("SELECT fGetTotalPricePO('$ponum') as price");
    return $totalPrice[0]->price;
}

function formatDate($date, $format = "d-m-Y")
{
    if (is_null($date)) {
        return '-';
    }
    return date($format, strtotime($date));
}

function formatDateTime($dateTime, $format = "d-m-Y h:i A")
{
    if (is_null($dateTime)) {
        return '-';
    }
    return ($dateTime) ? date($format, strtotime($dateTime)) : $dateTime;
}

function generateBatchNumber(){
    $dcnNumber = 'BATCH-';
    $getdata = DB::table('dcn_nriv')->where('year', date('Y'))
                ->where('month', date('m'))
                ->where('object','BATCH')->first();
    if($getdata){
        DB::beginTransaction();
        try{
            $leadingZero = '';
            if(strlen($getdata->current_number) == 5){
                $leadingZero = '0';
            }elseif(strlen($getdata->current_number) == 4){
                $leadingZero = '00';
            }elseif(strlen($getdata->current_number) == 3){
                $leadingZero = '000';
            }elseif(strlen($getdata->current_number) == 2){
                $leadingZero = '0000';
            }elseif(strlen($getdata->current_number) == 1){
                $leadingZero = '00000';
            }

            $lastnum = ($getdata->current_number*1) + 1;

            if($leadingZero == ''){
                $dcnNumber = date('Y').date('m').$lastnum;
            }else{
                $dcnNumber = date('Y').date('m'). $leadingZero . $lastnum;
            }

            DB::table('dcn_nriv')->where('year',$getdata->year)->where('month', date('m'))
            ->where('object','BATCH')->update([
                'current_number' => $lastnum
            ]);

            DB::commit();
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            return null;
        }
    }else{
        $dcnNumber = date('Y').date('m').'000001';
        DB::beginTransaction();
        try{
            DB::table('dcn_nriv')->insert([
                'year'            => date('Y'),
                'month'           => date('m'),
                'object'          => 'BATCH',
                'current_number'  => '1',
                'createdon'       => date('Y-m-d H:m:s'),
                'createdby'       => Auth::user()->email ?? Auth::user()->username
            ]);
            DB::commit();
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            return null;
        }
    }
}

function generateDcnNumber($doctype){
    $dcnNumber = '';
    $getdata = DB::table('dcn_nriv')->where('year', date('Y'))->where('object',$doctype)->first();
    if($getdata){
        DB::beginTransaction();
        try{
            $leadingZero = '';
            if(strlen($getdata->current_number) == 5){
                $leadingZero = '0';
            }elseif(strlen($getdata->current_number) == 4){
                $leadingZero = '00';
            }elseif(strlen($getdata->current_number) == 3){
                $leadingZero = '000';
            }elseif(strlen($getdata->current_number) == 2){
                $leadingZero = '0000';
            }elseif(strlen($getdata->current_number) == 1){
                $leadingZero = '00000';
            }

            $lastnum = ($getdata->current_number*1) + 1;

            if($leadingZero == ''){
                $dcnNumber = $doctype . '-' . substr($getdata->year,2) .'-'. $lastnum;
            }else{
                $dcnNumber = $doctype . '-' . substr($getdata->year,2) .'-'. $leadingZero . $lastnum;
            }

            DB::table('dcn_nriv')->where('year',$getdata->year)->where('object',$doctype)->update([
                'current_number' => $lastnum
            ]);

            DB::commit();
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            return null;
        }
    }else{
        $dcnNumber = $doctype . '-' .substr(date('Y'),2).'-000001';
        DB::beginTransaction();
        try{
            DB::table('dcn_nriv')->insert([
                'year'            => date('Y'),
                'object'          => $doctype,
                'current_number'  => '1',
                'createdon'       => date('Y-m-d H:m:s'),
                'createdby'       => Auth::user()->email ?? Auth::user()->username
            ]);
            DB::commit();
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            return null;
        }
    }

}

function generateCheckListNumber($tahun, $bulan){
    $dcnNumber = '';
    $getdata = DB::table('dcn_nriv')->where('year', $tahun)->where('month', $bulan)
              ->where('object','CKL')->first();
    $doctype = 'CKL';

    if($getdata){
        DB::beginTransaction();
        try{
            $leadingZero = '';
            if(strlen($getdata->current_number) == 5){
                $leadingZero = '0';
            }elseif(strlen($getdata->current_number) == 4){
                $leadingZero = '00';
            }elseif(strlen($getdata->current_number) == 3){
                $leadingZero = '000';
            }elseif(strlen($getdata->current_number) == 2){
                $leadingZero = '0000';
            }elseif(strlen($getdata->current_number) == 1){
                $leadingZero = '00000';
            }

            $lastnum = ($getdata->current_number*1) + 1;

            if($leadingZero == ''){
                $dcnNumber = $doctype . '-' . substr($getdata->year,2). $bulan .'-'. $lastnum;
            }else{
                $dcnNumber = $doctype . '-' . substr($getdata->year,2). $bulan .'-'. $leadingZero . $lastnum;
            }

            DB::table('dcn_nriv')->where('year', $tahun)->where('month', $bulan)->where('object','CKL')->update([
                'current_number' => $lastnum
            ]);

            DB::commit();
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            return null;
        }
    }else{
        $dcnNumber = $doctype . '-' .substr(date('Y'),2).$bulan.'-000001';
        DB::beginTransaction();
        try{
            DB::table('dcn_nriv')->insert([
                'year'            => $tahun,
                'month'           => $bulan,
                'object'          => 'CKL',
                'current_number'  => '1',
                'createdon'       => date('Y-m-d H:m:s'),
                'createdby'       => Auth::user()->email ?? Auth::user()->username
            ]);
            DB::commit();
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            return null;
        }
    }
}

function getWfGroup($doctype){

    $wfgroup = DB::table('doctypes')->where('id', $doctype)->first();
    if($wfgroup){
        return $wfgroup->workflow_group;
    }else{
        return 0;
    }
}

function groupOpen($groupid){
    $routeName = null;
    $routes = explode('/',\Route::current()->uri());
    $count = 0;

    foreach($routes as $row){
        $count = $count + 1;
        $routeName = $routeName . '/' . $row;
        if($count == 1){
            $routeName = substr($routeName,1);
        }

        $selectMenu = DB::table('menus')->where('route', $routeName)->first();
        if($selectMenu){
            // dd($selectMenu);
            return $groupid == $selectMenu->menugroup ? 'menu-open' : '';
            break;
        }
    }

    // $routeName = \Route::current()->uri();
    // $selectMenu = DB::table('menus')->where('route', $routeName)->first();
    // if($selectMenu){
    //     return $groupid == $selectMenu->menugroup ? 'menu-open' : '';
    // }
    // return request()->is("*".$groupname."*") ? 'menu-open' : '';
}

function currentURL(){
    $routeName = \Route::current()->uri();
    $selectMenu = DB::table('menus')->where('route', $routeName)->first();
    if($selectMenu){

    }
    dd(\Route::current()->uri());
}

function active($partialUrl){
    // return $partialUrl;
    return request()->is("*".$partialUrl."*") ? 'active' : '';
}

function insertOrUpdate(array $rows, $table){
    $first = reset($rows);

    $columns = implode(
        ',',
        array_map(function ($value) {
            return "$value";
        }, array_keys($first))
    );

    $values = implode(',', array_map(function ($row) {
            return '('.implode(
                ',',
                array_map(function ($value) {
                    return '"'.str_replace('"', '""', $value).'"';
                }, $row)
            ).')';
    }, $rows));

    $updates = implode(
        ',',
        array_map(function ($value) {
            return "$value = VALUES($value)";
        }, array_keys($first))
    );

    $sql = "INSERT INTO {$table}({$columns}) VALUES {$values} ON DUPLICATE KEY UPDATE {$updates}";

    return \DB::statement($sql);
}

function userAllowDownloadDocument(){
    $checkData = DB::table('user_object_auth')
                ->where('userid', Auth::user()->id)
                ->where('object_name', 'ALLOW_DOWNLOAD_DOC')
                ->first();
    if($checkData){
        if($checkData->object_val === "Y"){
            return 1;
        }else{
            return 0;
        }
    }else{
        return 0;
    }
}

function userAllowChangeDocument(){
    $checkData = DB::table('user_object_auth')
                ->where('userid', Auth::user()->id)
                ->where('object_name', 'ALLOW_CHANGE_DOC')
                ->first();
    if($checkData){
        if($checkData->object_val === "Y"){
            return 1;
        }else{
            return 0;
        }
    }else{
        return 0;
    }
}

function checkIsLocalhost(){
    if(request()->getHost() == "localhost"){
        return 1;
    }else{
        return 0;
    }
}

function getbaseurl(){
    $baseurl = env('APP_URL');
    return $baseurl;
}

function allowUplodOrginalDoc(){
    $checkData = DB::table('user_object_auth')
    ->where('userid', Auth::user()->id)
    ->where('object_name', 'ALLOW_UPLOAD_ORIGINAL_DOC')
    ->first();
    if($checkData){
        if($checkData->object_val === "Y"){
            return 1;
        }else{
            return 0;
        }
    }else{
        return 0;
    }
}

function allowDownloadOrginalDoc(){
    $checkData = DB::table('user_object_auth')
    ->where('userid', Auth::user()->id)
    ->where('object_name', 'ALLOW_DOWNLOAD_ORIGINAL_DOC')
    ->first();
    if($checkData){
        if($checkData->object_val === "Y"){
            return 1;
        }else{
            return 0;
        }
    }else{
        return 0;
    }
}

function apiIpdApp(){
    $ipdapi    = DB::table('general_setting')->where('setting_name', 'IPD_MODEL_API')->first();
    return $ipdapi->setting_value;
}

function getAppTheme(){
    $ipdapi    = DB::table('general_setting')->where('setting_name', 'APP_THEME')->first();
    return $ipdapi->setting_value;
}

function getAppBgImage(){
    $ipdapi    = DB::table('general_setting')->where('setting_name', 'APP_BGIMAGE')->first();
    return $ipdapi->setting_value;
}

function getUserDepartment(){
    $userDept = DB::table('t_department')->where('deptid', Auth::user()->deptid)->first();
    if($userDept){
        return $userDept->department;
    }else{
        return null;
    }
}

function getCompanyAddress(){
    $addr = DB::table('general_setting')->where('setting_name', 'COMPANY_ADDRESS')->first();
    return $addr->setting_value;
}

function getCompanyLogo(){
    $addr = DB::table('general_setting')->where('setting_name', 'COMPANY_LOGO')->first();
    return $addr->setting_value;
}

function getDepartmentByID($id){
    $userDept = DB::table('t_department')->where('deptid', $id)->first();
    if($userDept){
        return $userDept->department;
    }else{
        return '';
    }
}

function getUserNameByID($id){
    $userDept = DB::table('users')->where('id', $id)
            ->orWhere('email', $id)
            ->orWhere('username', $id)->first();
    return $userDept->name;
}

function getUserEsignByID($id){
    $userData = DB::table('users')->where('id', $id)
            ->orWhere('email', $id)
            ->orWhere('username', $id)->first();
    return $userData->s_signfile;
}

function generateBudgetDcnNumber($tahun, $bulan, $tgl, $dept, $deptname){
    $dcnNumber = 'PTA-'.$deptname.'/'.$tahun.$bulan.$tgl;

    $getdata = DB::table('t_nriv_budget')
               ->where('tahun',  $tahun)
               ->where('object', 'BUDGET')
               ->where('bulan',  $bulan)
               ->where('tanggal',  $tgl)
               ->where('deptid', $dept)
               ->first();

    if($getdata){
        DB::beginTransaction();
        try{
            $leadingZero = '';
            if(strlen($getdata->lastnumber) == 5){
                $leadingZero = '0';
            }elseif(strlen($getdata->lastnumber) == 4){
                $leadingZero = '00';
            }elseif(strlen($getdata->lastnumber) == 3){
                $leadingZero = '000';
            }elseif(strlen($getdata->lastnumber) == 2){
                $leadingZero = '0000';
            }elseif(strlen($getdata->lastnumber) == 1){
                $leadingZero = '00000';
            }

            $lastnum = ($getdata->lastnumber*1) + 1;

            if($leadingZero == ''){
                $dcnNumber = $dcnNumber. $lastnum;
            }else{
                $dcnNumber = $dcnNumber . $leadingZero . $lastnum;
            }

            // dd($leadingZero);

            DB::table('t_nriv_budget')
            ->where('tahun',  $tahun)
            ->where('object', 'BUDGET')
            ->where('bulan',  $bulan)
            ->where('tanggal',  $tgl)
            ->where('deptid', $dept)
            ->update([
                'lastnumber' => $lastnum
            ]);

            DB::commit();
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            return null;
        }
    }else{
        $dcnNumber = $dcnNumber.'000001';
        DB::beginTransaction();
        try{
            DB::table('t_nriv_budget')->insert([
                'object'          => 'BUDGET',
                'tahun'           => $tahun,
                'bulan'           => $bulan,
                'tanggal'         => $tgl,
                'deptid'          => $dept,
                'lastnumber'      => '1',
                'createdon'       => date('Y-m-d H:m:s'),
                'createdby'       => Auth::user()->email ?? Auth::user()->username
            ]);
            DB::commit();
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            return null;
        }
    }
}

function generatePbjNumber($tahun, $dept, $tgl){

    $department = DB::table('t_department')->where('deptid', $dept)->first();


    $deptid = $department->deptid;
    // dd($deptid);
    $dcnNumber = 'PBJ-'.$department->department.'/'.$tahun;
    // dd($dcnNumber);
    $getdata = DB::table('t_nriv_budget')
               ->where('tahun',  $tahun)
               ->where('object', 'PBJ')
               ->where('deptid',  $deptid)
            //    ->where('tanggal',  $tgl)
            //    ->where('deptid', $dept)
               ->first();
    // dd($getdata);

    if($getdata){
        DB::beginTransaction();
        try{
            $leadingZero = '';
            if(strlen($getdata->lastnumber) == 5){
                $leadingZero = '0';
            }elseif(strlen($getdata->lastnumber) == 4){
                $leadingZero = '00';
            }elseif(strlen($getdata->lastnumber) == 3){
                $leadingZero = '000';
            }elseif(strlen($getdata->lastnumber) == 2){
                $leadingZero = '0000';
            }elseif(strlen($getdata->lastnumber) == 1){
                $leadingZero = '00000';
            }

            $lastnum = ($getdata->lastnumber*1) + 1;

            if($leadingZero == ''){
                $dcnNumber = $dcnNumber. $lastnum;
            }else{
                $dcnNumber = $dcnNumber . $leadingZero . $lastnum;
            }

            // dd($leadingZero);

            DB::table('t_nriv_budget')
            ->where('tahun',  $tahun)
            ->where('object', 'PBJ')
            ->where('deptid',  $deptid)
            // ->where('tanggal',  $tgl)
            // ->where('deptid', $dept)
            ->update([
                'lastnumber' => $lastnum
            ]);

            DB::commit();
            // dd($dcnNumber);
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            return null;
        }
    }else{
        $dcnNumber = $dcnNumber.'000001';
        DB::beginTransaction();
        try{
            DB::table('t_nriv_budget')->insert([
                'object'          => 'PBJ',
                'tahun'           => $tahun,
                'deptid'          => $deptid,
                'bulan'           => date('m'),
                'tanggal'         => '01',
                // 'deptid'          => $dept,
                'lastnumber'      => '1',
                'createdon'       => date('Y-m-d H:m:s'),
                'createdby'       => Auth::user()->email ?? Auth::user()->username
            ]);
            DB::commit();
            // dd($dcnNumber);
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            dd($e);
            return null;
        }
    }
}

function generatePRNumber($tahun, $bulan, $tgl, $dept, $deptname){
    // $dcnNumber = 'PR-'.$deptname.'/'.$tahun.$bulan.$tgl;
    $dcnNumber = 'PR-'.$deptname.'/'.$tahun;
    // dd($dcnNumber);
    $getdata = DB::table('t_nriv_budget')
               ->where('tahun',  $tahun)
               ->where('object', 'PR')
            //    ->where('bulan',  $bulan)
            //    ->where('tanggal',  $tgl)
               ->where('deptid', $dept)
               ->first();

    if($getdata){
        DB::beginTransaction();
        try{
            $leadingZero = '';
            if(strlen($getdata->lastnumber) == 5){
                $leadingZero = '0';
            }elseif(strlen($getdata->lastnumber) == 4){
                $leadingZero = '00';
            }elseif(strlen($getdata->lastnumber) == 3){
                $leadingZero = '000';
            }elseif(strlen($getdata->lastnumber) == 2){
                $leadingZero = '0000';
            }elseif(strlen($getdata->lastnumber) == 1){
                $leadingZero = '00000';
            }

            $lastnum = ($getdata->lastnumber*1) + 1;

            if($leadingZero == ''){
                $dcnNumber = $dcnNumber. $lastnum;
            }else{
                $dcnNumber = $dcnNumber . $leadingZero . $lastnum;
            }

            // dd($leadingZero);

            DB::table('t_nriv_budget')
            ->where('tahun',  $tahun)
            ->where('object', 'PR')
            // ->where('bulan',  $bulan)
            // ->where('tanggal',  $tgl)
            ->where('deptid', $dept)
            ->update([
                'lastnumber' => $lastnum
            ]);

            DB::commit();
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            return null;
        }
    }else{
        $dcnNumber = $dcnNumber.'000001';
        DB::beginTransaction();
        try{
            DB::table('t_nriv_budget')->insert([
                'object'          => 'PR',
                'tahun'           => $tahun,
                'bulan'           => $bulan,
                'tanggal'         => $tgl,
                'deptid'          => $dept,
                'lastnumber'      => '1',
                'createdon'       => date('Y-m-d H:m:s'),
                'createdby'       => Auth::user()->email ?? Auth::user()->username
            ]);
            DB::commit();
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            return null;
        }
    }
}

function generateNextNumber($prefix, $object, $tahun, $bulan, $tgl){
    $dcnNumber = $prefix.'/'.$tahun.$bulan;
    // dd('A');
    $getdata = DB::table('t_nriv_budget')
    ->where('object',   $object)
    ->where('tahun',    $tahun)
    ->where('bulan',    $bulan)
    ->where('tanggal',  $tgl)
    ->first();

    if($getdata){
        // dd($getdata);
        DB::beginTransaction();
        try{
            $leadingZero = '';
            if(strlen($getdata->lastnumber) == 5){
                $leadingZero = '0';
            }elseif(strlen($getdata->lastnumber) == 4){
                $leadingZero = '00';
            }elseif(strlen($getdata->lastnumber) == 3){
                $leadingZero = '000';
            }elseif(strlen($getdata->lastnumber) == 2){
                $leadingZero = '0000';
            }elseif(strlen($getdata->lastnumber) == 1){
                $leadingZero = '00000';
            }

            $lastnum = ($getdata->lastnumber*1) + 1;

            if($leadingZero == ''){
                $dcnNumber = $dcnNumber. $lastnum;
            }else{
                $dcnNumber = $dcnNumber . $leadingZero . $lastnum;
            }

            DB::table('t_nriv_budget')
            ->where('tahun',  $tahun)
            ->where('object', $object)
            ->where('bulan',  $bulan)
            ->update([
                'lastnumber' => $lastnum
            ]);

            DB::commit();
            // dd($dcnNumber);
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            return null;
        }
    }else{
        $dcnNumber = $dcnNumber.'000001';
        DB::beginTransaction();
        try{
            DB::table('t_nriv_budget')->insert([
                'object'          => $object,
                'tahun'           => $tahun,
                'bulan'           => $bulan,
                'tanggal'         => $tgl,
                // 'deptid'          => $dept,
                'lastnumber'      => '1',
                'createdon'       => date('Y-m-d H:m:s'),
                'createdby'       => Auth::user()->email ?? Auth::user()->username
            ]);
            DB::commit();
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            dd($e);
            return null;
        }
    }
}

function generateNextNumber2($prefix, $object, $tahun, $bulan, $tgl){
    $dcnNumber = $prefix.'/'.$tahun.$bulan;
    // dd('A');
    $getdata = DB::table('t_nriv_budget')
    ->where('object',   $object)
    ->where('tahun',    $tahun)
    // ->where('bulan',    $bulan)
    ->where('tanggal',  $tgl)
    ->first();

    if($getdata){
        // dd($getdata);
        DB::beginTransaction();
        try{
            $leadingZero = '';
            if(strlen($getdata->lastnumber) == 5){
                $leadingZero = '0';
            }elseif(strlen($getdata->lastnumber) == 4){
                $leadingZero = '00';
            }elseif(strlen($getdata->lastnumber) == 3){
                $leadingZero = '000';
            }elseif(strlen($getdata->lastnumber) == 2){
                $leadingZero = '0000';
            }elseif(strlen($getdata->lastnumber) == 1){
                $leadingZero = '00000';
            }

            $lastnum = ($getdata->lastnumber*1) + 1;

            if($leadingZero == ''){
                $dcnNumber = $dcnNumber. $lastnum;
            }else{
                $dcnNumber = $dcnNumber . $leadingZero . $lastnum;
            }

            DB::table('t_nriv_budget')
            ->where('tahun',  $tahun)
            ->where('object', $object)
            // ->where('bulan',  $bulan)
            ->update([
                'lastnumber' => $lastnum
            ]);

            DB::commit();
            // dd($dcnNumber);
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            return null;
        }
    }else{
        $dcnNumber = $dcnNumber.'000001';
        DB::beginTransaction();
        try{
            DB::table('t_nriv_budget')->insert([
                'object'          => $object,
                'tahun'           => $tahun,
                // 'bulan'           => $bulan,
                'tanggal'         => $tgl,
                // 'deptid'          => $dept,
                'lastnumber'      => '1',
                'createdon'       => date('Y-m-d H:m:s'),
                'createdby'       => Auth::user()->email ?? Auth::user()->username
            ]);
            DB::commit();
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            dd($e);
            return null;
        }
    }
}

function generatePONumber($tahun, $bulan, $tgl){
    $dcnNumber = 'PO/'.$tahun.$bulan.$tgl;
    // dd($dcnNumber);
    $getdata = DB::table('t_nriv_budget')
               ->where('tahun',  $tahun)
               ->where('object', 'PO')
               ->where('bulan',  $bulan)
               ->where('tanggal',  $tgl)
            //    ->where('deptid', $dept)
               ->first();

    if($getdata){
        DB::beginTransaction();
        try{
            $leadingZero = '';
            if(strlen($getdata->lastnumber) == 5){
                $leadingZero = '0';
            }elseif(strlen($getdata->lastnumber) == 4){
                $leadingZero = '00';
            }elseif(strlen($getdata->lastnumber) == 3){
                $leadingZero = '000';
            }elseif(strlen($getdata->lastnumber) == 2){
                $leadingZero = '0000';
            }elseif(strlen($getdata->lastnumber) == 1){
                $leadingZero = '00000';
            }

            $lastnum = ($getdata->lastnumber*1) + 1;

            if($leadingZero == ''){
                $dcnNumber = $dcnNumber. $lastnum;
            }else{
                $dcnNumber = $dcnNumber . $leadingZero . $lastnum;
            }

            // dd($leadingZero);

            DB::table('t_nriv_budget')
            ->where('tahun',  $tahun)
            ->where('object', 'PO')
            ->where('bulan',  $bulan)
            ->where('tanggal',  $tgl)
            // ->where('deptid', $dept)
            ->update([
                'lastnumber' => $lastnum
            ]);

            DB::commit();
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            return null;
        }
    }else{
        $dcnNumber = $dcnNumber.'000001';
        DB::beginTransaction();
        try{
            DB::table('t_nriv_budget')->insert([
                'object'          => 'PO',
                'tahun'           => $tahun,
                'bulan'           => $bulan,
                'tanggal'         => $tgl,
                // 'deptid'          => $dept,
                'lastnumber'      => '1',
                'createdon'       => date('Y-m-d H:m:s'),
                'createdby'       => Auth::user()->email ?? Auth::user()->username
            ]);
            DB::commit();
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            return null;
        }
    }
}

function generateGRPONumber($tahun, $bulan){
    $dcnNumber = 'RPO/'.$tahun.$bulan;
    // dd($dcnNumber);
    $getdata = DB::table('t_nriv_budget')
               ->where('tahun',  $tahun)
               ->where('object', 'GRPO')
               ->where('bulan',  $bulan)
            //    ->where('tanggal',  $tgl)
            //    ->where('deptid', $dept)
               ->first();

    if($getdata){
        DB::beginTransaction();
        try{
            $leadingZero = '';
            if(strlen($getdata->lastnumber) == 5){
                $leadingZero = '0';
            }elseif(strlen($getdata->lastnumber) == 4){
                $leadingZero = '00';
            }elseif(strlen($getdata->lastnumber) == 3){
                $leadingZero = '000';
            }elseif(strlen($getdata->lastnumber) == 2){
                $leadingZero = '0000';
            }elseif(strlen($getdata->lastnumber) == 1){
                $leadingZero = '00000';
            }

            $lastnum = ($getdata->lastnumber*1) + 1;

            if($leadingZero == ''){
                $dcnNumber = $dcnNumber. $lastnum;
            }else{
                $dcnNumber = $dcnNumber . $leadingZero . $lastnum;
            }

            // dd($leadingZero);

            DB::table('t_nriv_budget')
            ->where('tahun',  $tahun)
            ->where('object', 'GRPO')
            ->where('bulan',  $bulan)
            // ->where('tanggal',  $tgl)
            // ->where('deptid', $dept)
            ->update([
                'lastnumber' => $lastnum
            ]);

            DB::commit();
            // dd($dcnNumber);
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            return null;
        }
    }else{
        $dcnNumber = $dcnNumber.'000001';
        DB::beginTransaction();
        try{
            DB::table('t_nriv_budget')->insert([
                'object'          => 'GRPO',
                'tahun'           => $tahun,
                'bulan'           => $bulan,
                'tanggal'         => '01',
                // 'deptid'          => $dept,
                'lastnumber'      => '1',
                'createdon'       => date('Y-m-d H:m:s'),
                'createdby'       => Auth::user()->email ?? Auth::user()->username
            ]);
            DB::commit();
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            // dd($e->getMessage());
            return null;
        }
    }
}

function generateIssueNumber($tahun, $bulan){
    $dcnNumber = 'ISSUE/'.$tahun.$bulan;
    // dd($dcnNumber);
    $getdata = DB::table('t_nriv_budget')
               ->where('tahun',  $tahun)
               ->where('object', 'ISSUE')
               ->where('bulan',  $bulan)
            //    ->where('tanggal',  $tgl)
            //    ->where('deptid', $dept)
               ->first();

    if($getdata){
        DB::beginTransaction();
        try{
            $leadingZero = '';
            if(strlen($getdata->lastnumber) == 5){
                $leadingZero = '0';
            }elseif(strlen($getdata->lastnumber) == 4){
                $leadingZero = '00';
            }elseif(strlen($getdata->lastnumber) == 3){
                $leadingZero = '000';
            }elseif(strlen($getdata->lastnumber) == 2){
                $leadingZero = '0000';
            }elseif(strlen($getdata->lastnumber) == 1){
                $leadingZero = '00000';
            }

            $lastnum = ($getdata->lastnumber*1) + 1;

            if($leadingZero == ''){
                $dcnNumber = $dcnNumber. $lastnum;
            }else{
                $dcnNumber = $dcnNumber . $leadingZero . $lastnum;
            }

            // dd($leadingZero);

            DB::table('t_nriv_budget')
            ->where('tahun',  $tahun)
            ->where('object', 'ISSUE')
            ->where('bulan',  $bulan)
            // ->where('tanggal',  $tgl)
            // ->where('deptid', $dept)
            ->update([
                'lastnumber' => $lastnum
            ]);

            DB::commit();
            // dd($dcnNumber);
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            return null;
        }
    }else{
        $dcnNumber = $dcnNumber.'000001';
        DB::beginTransaction();
        try{
            DB::table('t_nriv_budget')->insert([
                'object'          => 'ISSUE',
                'tahun'           => $tahun,
                'bulan'           => $bulan,
                'tanggal'         => '01',
                // 'deptid'          => $dept,
                'lastnumber'      => '1',
                'createdon'       => date('Y-m-d H:m:s'),
                'createdby'       => Auth::user()->email ?? Auth::user()->username
            ]);
            DB::commit();
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            // dd($e->getMessage());
            return null;
        }
    }
}

function generateTransferNumber($tahun, $bulan){
    $dcnNumber = 'TF/'.$tahun.$bulan;
    // dd($dcnNumber);
    $getdata = DB::table('t_nriv_budget')
               ->where('tahun',  $tahun)
               ->where('object', 'TF')
               ->where('bulan',  $bulan)
            //    ->where('tanggal',  $tgl)
            //    ->where('deptid', $dept)
               ->first();

    if($getdata){
        DB::beginTransaction();
        try{
            $leadingZero = '';
            if(strlen($getdata->lastnumber) == 5){
                $leadingZero = '0';
            }elseif(strlen($getdata->lastnumber) == 4){
                $leadingZero = '00';
            }elseif(strlen($getdata->lastnumber) == 3){
                $leadingZero = '000';
            }elseif(strlen($getdata->lastnumber) == 2){
                $leadingZero = '0000';
            }elseif(strlen($getdata->lastnumber) == 1){
                $leadingZero = '00000';
            }

            $lastnum = ($getdata->lastnumber*1) + 1;

            if($leadingZero == ''){
                $dcnNumber = $dcnNumber. $lastnum;
            }else{
                $dcnNumber = $dcnNumber . $leadingZero . $lastnum;
            }

            // dd($leadingZero);

            DB::table('t_nriv_budget')
            ->where('tahun',  $tahun)
            ->where('object', 'TF')
            ->where('bulan',  $bulan)
            // ->where('tanggal',  $tgl)
            // ->where('deptid', $dept)
            ->update([
                'lastnumber' => $lastnum
            ]);

            DB::commit();
            // dd($dcnNumber);
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            return null;
        }
    }else{
        $dcnNumber = $dcnNumber.'000001';
        DB::beginTransaction();
        try{
            DB::table('t_nriv_budget')->insert([
                'object'          => 'TF',
                'tahun'           => $tahun,
                'bulan'           => $bulan,
                'tanggal'         => '01',
                // 'deptid'          => $dept,
                'lastnumber'      => '1',
                'createdon'       => date('Y-m-d H:m:s'),
                'createdby'       => Auth::user()->email ?? Auth::user()->username
            ]);
            DB::commit();
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            // dd($e->getMessage());
            return null;
        }
    }
}

function generateWONumber($tahun, $bulan){
    $dcnNumber = 'WO/'.$tahun.$bulan;
    // dd($dcnNumber);
    $getdata = DB::table('t_nriv_budget')
               ->where('tahun',  $tahun)
               ->where('object', 'WO')
               ->where('bulan',  $bulan)
            //    ->where('tanggal',  $tgl)
            //    ->where('deptid', $dept)
               ->first();

    if($getdata){
        DB::beginTransaction();
        try{
            $leadingZero = '';
            if(strlen($getdata->lastnumber) == 5){
                $leadingZero = '0';
            }elseif(strlen($getdata->lastnumber) == 4){
                $leadingZero = '00';
            }elseif(strlen($getdata->lastnumber) == 3){
                $leadingZero = '000';
            }elseif(strlen($getdata->lastnumber) == 2){
                $leadingZero = '0000';
            }elseif(strlen($getdata->lastnumber) == 1){
                $leadingZero = '00000';
            }

            $lastnum = ($getdata->lastnumber*1) + 1;

            if($leadingZero == ''){
                $dcnNumber = $dcnNumber. $lastnum;
            }else{
                $dcnNumber = $dcnNumber . $leadingZero . $lastnum;
            }

            // dd($leadingZero);

            DB::table('t_nriv_budget')
            ->where('tahun',  $tahun)
            ->where('object', 'WO')
            ->where('bulan',  $bulan)
            // ->where('tanggal',  $tgl)
            // ->where('deptid', $dept)
            ->update([
                'lastnumber' => $lastnum
            ]);

            DB::commit();
            // dd($dcnNumber);
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            return null;
        }
    }else{
        $dcnNumber = $dcnNumber.'000001';
        DB::beginTransaction();
        try{
            DB::table('t_nriv_budget')->insert([
                'object'          => 'WO',
                'tahun'           => $tahun,
                'bulan'           => $bulan,
                'tanggal'         => '01',
                // 'deptid'          => $dept,
                'lastnumber'      => '1',
                'createdon'       => date('Y-m-d H:m:s'),
                'createdby'       => Auth::user()->email ?? Auth::user()->username
            ]);
            DB::commit();
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            // dd($e->getMessage());
            return null;
        }
    }
}

function generateVendorCode(){
    $getdata = DB::table('t_nriv')
    ->where('object', 'VENDOR')
    ->first();

    if($getdata){
        DB::beginTransaction();
        try{
            $dcnNumber = ($getdata->currentnum*1) + 1;

            DB::table('t_nriv')->where('object', 'VENDOR')->update([
                'currentnum' => $dcnNumber
            ]);
            DB::commit();
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            // dd($e->getMessage());
            // return '3000000000';
        }
    }else{
        $dcnNumber = '300000';
        DB::beginTransaction();
        try{
            DB::table('t_nriv')->insert([
                'object'          => 'VENDOR',
                'fromnum'         => '300000',
                'tonumber'        => '399999',
                'currentnum'      => '300000',
                'nyear'           => 0,
            ]);
            DB::commit();
            return $dcnNumber;
        }catch(\Exception $e){
            DB::rollBack();
            // dd($e->getMessage());
            // return '3000000000';
        }
    }
}

function sendPurchaseOrder($poNumber){

    $poheader = DB::table('t_po01')->where('ponum', $poNumber)->first();
    $vendor   = DB::table('t_vendor')->where('vendor_code', $poheader->vendor)->first();
    $poitem   = DB::table('t_po02')->where('ponum', $poNumber)->where('approvestat', 'A')->get();

    $prNumber      = DB::table('t_po02')->where('ponum', $poheader->ponum)->pluck('prnum');
    $pbjNumber     = DB::table('t_pr02')->whereIn('prnum', $prNumber)->pluck('pbjnumber');

    $pbjData       = DB::table('t_pbj01')->whereIn('pbjnumber', $pbjNumber)->first();

    $attachments = DB::table('v_attachments')
                    ->select('fileurl')
                    // ->whereIn('doc_object', ['PO','PR', 'PBJ'])
                    ->where('doc_number', $poheader->ponum)
                    ->orWhereIn('doc_number', $prNumber)
                    ->orWhereIn('doc_number', $pbjNumber)
                    ->pluck('fileurl');

    $sendData   = array();
    $insertData = array();
    foreach($poitem as $row){
        $idProject = 0;
        $project = DB::table('t_projects')->where('idproject', $row->idproject)->first();
        if(!$project){
            $idProject = 0;
        }else{
            $idProject = $project->kode_project;
        }
        $insert = array(
            "proyek_id"     => $idProject,
            "item_desk"     => $row->matdesc. '. '. $row->quantity . $row->unit . ' @'.$row->price,
            "item_payee"    => $vendor->vendor_id,
            "item_curr"     => "IDR",
            "pretax_rp"     => $row->price*$row->quantity,
            "PPN"           => $poheader->ppn,
            "item_rp"       => ($row->price*$row->quantity)+(($row->price*$row->quantity)*($poheader->ppn/100)),
            "oleh"          => $row->createdby,
            "dept"          => $poheader->deptid,
            "budget"        => $row->budget_code,
            "budget_period" => $row->budget_period ?? "",
            "kodebudget"    => $row->budget_code_num ?? "NONBUDGET",
            "catatan"       => $pbjData->tujuan_permintaan ?? $poheader->note,
            "item_rek"      => $vendor->vendor_id, //$vendor->no_rek, Pak ada sedikit update untuk array yang dikirim pak
            "item_bank"     => $vendor->vendor_id, //$vendor->bank, Item_bank dan item_rek disamakan dengan item_payee pak
            "periode"       => date('Y'),
            "no_po"         => $row->ponum,
            "attachment"    => $attachments
        );
        array_push($sendData, $insert);

        $submitData = array(
            'ponum'    => $row->ponum,
            'poitem'   => $row->poitem,
            'material' => $row->material,
            'matdesc'  => $row->matdesc,
            'quantity' => $row->quantity,
            'unit'     => $row->unit,
            'submitdate' => getLocalDatabaseDateTime(),
            'submitby'   => Auth::user()->username
        );
        array_push($insertData, $submitData);
    }

    return $sendData;

    $apikey  = 'B807C072-05ADCCE0-C1C82376-3EC92EF1';
    $url     = 'https://mahakaryabangunpersada.com/api/v1/submit/po';
    $get_api = mbpAPI($url, $apikey, $sendData);
    // return $get_api;
    // return str_replace($apikey,"",$get_api);
    // $response = json_decode($get_api, true);
    $response = json_decode(str_replace($apikey,"",$get_api));
    // return $response->status;
    // $status   = $response['status'];
    // $pesan    = $response['status_message'];
    // $datajson = $response['data'];

    $status   = $response->status;
    $pesan    = $response->status_message;
    $datajson = $response->data;
    if(str_contains($datajson,'Succeed')){
        insertOrUpdate($insertData,'t_log_submit_api');
        DB::table('t_po01')->where('ponum', $poNumber)->update([
            'submitted' => 'Y'
        ]);
    }
    return $response;
}

function sendPurchaseOrderV2($poNumber){

    $poheader = DB::table('t_po01')->where('ponum', $poNumber)->first();
    $vendor   = DB::table('t_vendor')->where('vendor_code', $poheader->vendor)->first();
    $poitem   = DB::table('t_po02')->where('ponum', $poNumber)->where('approvestat', 'A')->get();

    $prNumber      = DB::table('t_po02')->where('ponum', $poheader->ponum)->pluck('prnum');
    $pbjNumber     = DB::table('t_pr02')->whereIn('prnum', $prNumber)->pluck('pbjnumber');

    $pbjData       = DB::table('t_pbj01')->whereIn('pbjnumber', $pbjNumber)->first();

    $attachments = DB::table('v_attachments')
                    ->select('fileurl')
                    // ->whereIn('doc_object', ['PO','PR', 'PBJ'])
                    ->where('doc_number', $poheader->ponum)
                    ->orWhereIn('doc_number', $prNumber)
                    ->orWhereIn('doc_number', $pbjNumber)
                    ->pluck('fileurl');

    $sendData   = array();
    $insertData = array();
    $idProject = 0;
    $poNumber = null;
    $budCode  = null;
    $material = null;
    $matdesc  = null;
    $pretax_rp = 0;
    $item_rp   = 0;
    $budget_period = '';
    $budget_code   = '';

    foreach($poitem as $row){
        $item_rp = $item_rp + ($row->price*$row->quantity)+(($row->price*$row->quantity)*($poheader->ppn/100));
        // if($poNumber == null){
        //     $poNumber = $row->ponum;
        // }else{
        //     $poNumber = $poNumber. ', '. $row->ponum;
        // }

        if($budCode == null){
            $budCode = $row->budget_code_num;
        }else{
            if($row->budget_code_num == "" || $row->budget_code_num == null){
                $row->budget_code_num = 'NONBUDGET';
            }
            $budCode = $budCode. ', '. $row->budget_code_num;
        }

        if($material == null){
            $material = $row->material;
        }else{
            $material = $material. ', '. $row->material;
        }

        if($matdesc == null){
            $matdesc = $row->matdesc;
        }else{
            $matdesc = $matdesc. ', '. $row->matdesc;
        }

        $pretax_rp = $pretax_rp + $row->price*$row->quantity;

        $project = DB::table('t_projects')->where('idproject', $row->idproject)->first();
        if(!$project){
            $idProject = 0;
        }else{
            $idProject = $project->kode_project;
        }

        $submitData = array(
            'ponum'    => $row->ponum,
            'poitem'   => $row->poitem,
            'material' => $row->material,
            'matdesc'  => $row->matdesc,
            'quantity' => $row->quantity,
            'unit'     => $row->unit,
            'submitdate' => getLocalDatabaseDateTime(),
            'submitby'   => Auth::user()->username
        );
        array_push($insertData, $submitData);

        $budget_code   = $row->budget_code;
        $budget_period = $row->budget_period;
    }

    $insert = array(
        "proyek_id"     => $idProject,
        "item_desk"     => $matdesc,
        "item_payee"    => $vendor->vendor_id,
        "item_curr"     => "IDR",
        "pretax_rp"     => $pretax_rp,
        "PPN"           => $poheader->ppn,
        "item_rp"       => $item_rp,
        "oleh"          => $poheader->createdby,
        "dept"          => $poheader->deptid,
        "budget"        => "0",
        "budget_period" => $budget_period ?? "",
        "kodebudget"    => $budCode,
        "partnumber"    => $material,
        "catatan"       => $pbjData->tujuan_permintaan ?? $poheader->note,
        "item_rek"      => $vendor->vendor_id, //$vendor->no_rek, Pak ada sedikit update untuk array yang dikirim pak
        "item_bank"     => $vendor->vendor_id, //$vendor->bank, Item_bank dan item_rek disamakan dengan item_payee pak
        "periode"       => date('Y'),
        "no_po"         => $poheader->ponum,
        "attachment"    => $attachments
    );
    array_push($sendData, $insert);


    // return $sendData;

    $apikey  = 'B807C072-05ADCCE0-C1C82376-3EC92EF1';
    $url     = 'https://mahakaryabangunpersada.com/api/v1/submit/po';
    $get_api = mbpAPI($url, $apikey, $sendData);

    $response = json_decode(str_replace($apikey,"",$get_api));

    $status   = $response->status;
    $pesan    = $response->status_message;
    $datajson = $response->data;
    if(str_contains($datajson,'Succeed')){
        insertOrUpdate($insertData,'t_log_submit_api');
        DB::table('t_po01')->where('ponum', $poNumber)->update([
            'submitted' => 'Y'
        ]);
    }
    return $response;
}

function mbpAPI($url, $apikey, $data=array()){
    $debugfileh = tmpfile();
    $curl       = curl_init();
    curl_setopt($curl, CURLOPT_POST, 1);
    if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data) );

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'APIKEY: '.$apikey,
        'Content-Type: application/json',
    ));
    // curl_setopt($curl, CURLOPT_VERBOSE, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

    $result = curl_exec($curl);
    if(!$result){die("Connection Failure");}
    curl_close($curl);
    return $result;
    // try{
    // }finally{
    //     var_dump('curl verbose log:',file_get_contents(stream_get_meta_data($debugfileh)['uri']));
    //     fclose($debugfileh);
    //     // curl_close($curl);
    // }
}
