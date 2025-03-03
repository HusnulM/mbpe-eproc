@extends('layouts/App')

@section('title', 'Submit Purchase Order')

@section('additional-css')
<style>
    td.details-control {
        background: url("{{ ('/assets/dist/img/show_detail.png') }}") no-repeat center center;
        cursor: pointer;
    }
    tr.shown td.details-control {
        background: url("{{ ('/assets/dist/img/close_detail.png') }}") no-repeat center center;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <ul class="nav nav-tabs" id="custom-content-above-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="custom-content-above-home-tab" data-toggle="pill" href="#custom-content-above-home" role="tab" aria-controls="custom-content-above-home" aria-selected="true">Approved PO</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-content-above-approval-tab" data-toggle="pill" href="#custom-content-above-approval" role="tab" aria-controls="custom-content-above-approval" aria-selected="false">Submitted PO</a>
                            </li>
                        </ul>
                    </div>
                    <!-- <h3 class="card-title">List Approved Purchase Order</h3> -->
                    <div class="card-tools">

                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="tab-content" id="custom-content-above-tabContent">

                                <div class="tab-pane fade show active" id="custom-content-above-home" role="tabpanel" aria-labelledby="custom-content-above-home-tab">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <form action="{{ url('report/exportpo') }}" method="post">
                                                @csrf
                                                <div class="row">
                                                    <div class="col-lg-2">
                                                        <label for="">Tanggal PO</label>
                                                        <input type="date" class="form-control" name="datefrom" id="datefrom" value="{{ $_GET['datefrom'] ?? '' }}">
                                                    </div>
                                                    <div class="col-lg-2">
                                                        <label for="">-</label>
                                                        <input type="date" class="form-control" name="dateto" id="dateto" value="{{ $_GET['dateto'] ?? '' }}">
                                                    </div>

                                                    <div class="col-lg-2">
                                                        <label for="">Department</label>
                                                        <select name="department" id="department" class="form-control">
                                                            <option value="All">All</option>
                                                            @foreach($department as $key => $row)
                                                                <option value="{{ $row->deptid }}">{{ $row->department }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-lg-3" style="text-align:center;">
                                                        <br>
                                                        <button type="button" class="btn btn-default mt-2 btn-search">
                                                            <i class="fa fa-search"></i> Filter
                                                        </button>
                                                        {{-- <button type="submit" class="btn btn-success mt-2 btn-export">
                                                            <i class="fa fa-download"></i> Export Data
                                                        </button> --}}
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="table-responsive">
                                            <table id="tbl-budget-list" class="table table-bordered table-hover table-striped table-sm" style="width:100%;">
                                                <thead>
                                                    <th>No</th>
                                                    <th>Nomor PO</th>
                                                    <th>Tanggal PO</th>
                                                    <th>Vendor</th>
                                                    <th>Department</th>
                                                    <th>Remark</th>
                                                    <th></th>
                                                </thead>
                                                <tbody>

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab 2 -->
                                <div class="tab-pane fade" id="custom-content-above-approval" role="tabpanel" aria-labelledby="custom-content-above-approval-tab">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <form action="{{ url('report/exportpo') }}" method="post">
                                                @csrf
                                                <div class="row">
                                                    <div class="col-lg-2">
                                                        <label for="">Tanggal PO</label>
                                                        <input type="date" class="form-control" name="datefrom" id="datefrom2" value="{{ $_GET['datefrom'] ?? '' }}">
                                                    </div>
                                                    <div class="col-lg-2">
                                                        <label for="">-</label>
                                                        <input type="date" class="form-control" name="dateto" id="dateto2" value="{{ $_GET['dateto'] ?? '' }}">
                                                    </div>

                                                    <div class="col-lg-2">
                                                        <label for="">Department</label>
                                                        <select name="department" id="department2" class="form-control">
                                                            <option value="All">All</option>
                                                            @foreach($department as $key => $row)
                                                                <option value="{{ $row->deptid }}">{{ $row->department }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-lg-3" style="text-align:center;">
                                                        <br>
                                                        <button type="button" class="btn btn-default mt-2 btn-search-submit">
                                                            <i class="fa fa-search"></i> Filter
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="table-responsive">
                                            <table id="tbl-submitted-list" class="table table-bordered table-hover table-striped table-sm" style="width:100%;">
                                                <thead>
                                                    <th>No</th>
                                                    <th>Nomor PO</th>
                                                    <th>Tanggal PO</th>
                                                    <th>Vendor</th>
                                                    <th>Department</th>
                                                    <th>Remark</th>
                                                    <th></th>
                                                </thead>
                                                <tbody>

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('additional-modal')

@endsection

@section('additional-js')
<script>
    function validate(evt) {
        var theEvent = evt || window.event;

        // Handle paste
        if (theEvent.type === 'paste') {
            key = event.clipboardData.getData('text/plain');
        } else {
        // Handle key press
            var key = theEvent.keyCode || theEvent.which;
            key = String.fromCharCode(key);
        }
        var regex = /[0-9]|\./;
        if( !regex.test(key) ) {
            theEvent.returnValue = false;
            if(theEvent.preventDefault) theEvent.preventDefault();
        }
    }

    $(document).ready(function(){

        $('.btn-search').on('click', function(){
            var param = '?datefrom='+ $('#datefrom').val() +'&dateto='+ $('#dateto').val()+'&department='+$('#department').val();
            loadDocument(param);
        });

        $('.btn-search-submit').on('click', function(){
            var param = '?datefrom='+ $('#datefrom2').val() +'&dateto='+ $('#dateto2').val()+'&department='+$('#department2').val();
            loadSubmittedPO(param);
        });

        loadDocument('');
        loadSubmittedPO('');

        function loadDocument(_params){
            $("#tbl-budget-list").DataTable({
                serverSide: true,
                ajax: {
                    url: base_url+'/proc/submitpo/polist'+_params,
                    data: function (data) {
                        data.params = {
                            sac: "sac"
                        }
                    }
                },
                buttons: false,
                searching: true,
                // scrollY: 500,
                // scrollX: true,
                // scrollCollapse: true,
                bDestroy: true,
                // sScrollXInner: "100%",
                columns: [
                    {
                        "className":      'details-control',
                        "orderable":      false,
                        "searchable":     false,
                        "data":           null,
                        "defaultContent": '',
                        "width": "30px"
                    },
                    // { "data": null,"sortable": false, "searchable": false,
                    //     render: function (data, type, row, meta) {
                    //         return meta.row + meta.settings._iDisplayStart + 1;
                    //     }
                    // },
                    {data: "ponum", className: 'uid'},
                    {data: "podat", className: 'uid',
                        render: function (data, type, row){
                            return ``+ row.podat.podat1 + ``;
                        }
                    },
                    {data: "vendor_name", className: 'uid'},
                    {data: "department"},
                    {data: "note" },
                    {"defaultContent":
                        `
                        <button class='btn btn-success btn-sm button-submit-po'> <i class='fa fa-search'></i> Submit PO</button>
                        `,
                        "className": "text-center",
                        "width": "10%"
                    }
                ]
            });

            $('#tbl-budget-list tbody').on('click', '.button-submit-po', function () {
                let _token   = $('meta[name="csrf-token"]').attr('content');
                var tabledata = $('#tbl-budget-list').DataTable();
                var tr = $(this).closest('tr');
                var row = tabledata.row( tr );
                var d = row.data();
                console.log(d)
                console.log(row.child.isShown())
                // if ( row.child.isShown() ) {
                //     row.child.hide();
                //     tr.removeClass('shown');
                // }else{
                // }
                $.ajax({
                    url: base_url+'/proc/submitpo/submitdata',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data:{
                        ponum: d.ponum,
                        _token: _token
                    },
                    dataType: 'json',
                    cache:false,
                    success: function(result){
                        console.log(result)
                    },
                    error: function(err){
                        console.log(err)
                        toastr.error(err.responseText)
                    }
                }).done(function(data){

                    console.log(data)
                    if(data.status == '200'){
                        var rText = data.data;
                        if(rText.includes('Failed')){
                            toastr.info(data.data)
                        }else{
                            toastr.info(data.data)
                            // var row_index = $(this).closest("tr").index();
                            tr.remove();
                            // loadDocument('');
                        }
                    }else{
                        toastr.error(error)
                    }
                });
            });

            $('#tbl-budget-list tbody').on('click', 'tr td.details-control', function () {
                let _token   = $('meta[name="csrf-token"]').attr('content');
                var tabledata = $('#tbl-budget-list').DataTable();
                var tr = $(this).closest('tr');
                var row = tabledata.row( tr );
                var d = row.data();
                console.log(d)
                console.log(row.child.isShown())
                if ( row.child.isShown() ) {
                    row.child.hide();
                    tr.removeClass('shown');
                }else{
                    $.ajax({
                        url: base_url+'/proc/submitpo/getitems',
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data:{
                            ponum: d.ponum,
                            _token: _token
                        },
                        dataType: 'json',
                        cache:false,
                        success: function(result){
                        },
                        error: function(err){
                            console.log(err)
                        }
                    }).done(function(data){

                        if ( row.child.isShown() ) {
                            row.child.hide();
                            tr.removeClass('shown');
                        }
                        else {
                            row.child( format(row.data(), data) ).show();
                            tr.addClass('shown');
                        }
                    });
                }

                function format ( d, results ) {
                    // console.log(results)
                    var tdStyle = '';
                    var appStat = '';
                    var appNote = '';
                    var appDate = '';
                    var appBy   = '';

                    var html = '';
                    html = `<table class="table table-bordered table-hover table-striped table-sm">
                        <thead>
                                <th>Line Item</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total Price</th>
                        </thead>
                        <tbody>`;
                        for(var i = 0; i < results.length; i++){
                            html +=`
                            <tr style="background-color:gray; color:white;">
                                <td> `+ results[i].poitem +` </td>
                                <td> `+ results[i].material +` </td>
                                <td> `+ results[i].matdesc +` </td>
                                <td style="text-align:right;"> `+ (results[i].quantity*1) +` `+ results[i].unit +` </td>
                                <td style="text-align:right;"> `+ (results[i].price*1) +` </td>
                                <td style="text-align:right;"> `+ (results[i].price*1)*(results[i].quantity*1) +` </td>
                            </tr>
                            `;
                        }

                    html +=`</tbody>
                            </table>`;
                    return html;
                }
            });
        }

        function loadSubmittedPO(_params){
            $("#tbl-submitted-list").DataTable({
                serverSide: true,
                ajax: {
                    url: base_url+'/proc/submitpo/submittedpolist'+_params,
                    data: function (data) {
                        data.params = {
                            sac: "sac"
                        }
                    }
                },
                buttons: false,
                searching: true,
                // scrollY: 500,
                // scrollX: true,
                // scrollCollapse: true,
                bDestroy: true,
                // sScrollXInner: "100%",
                columns: [
                    {
                        "className":      'details-control',
                        "orderable":      false,
                        "searchable":     false,
                        "data":           null,
                        "defaultContent": '',
                        "width": "30px"
                    },
                    // { "data": null,"sortable": false, "searchable": false,
                    //     render: function (data, type, row, meta) {
                    //         return meta.row + meta.settings._iDisplayStart + 1;
                    //     }
                    // },
                    {data: "ponum", className: 'uid'},
                    {data: "podat", className: 'uid',
                        render: function (data, type, row){
                            return ``+ row.podat.podat1 + ``;
                        }
                    },
                    {data: "vendor_name", className: 'uid'},
                    {data: "department"},
                    {data: "note" },
                    {"defaultContent":
                        `
                        <button class='btn btn-success btn-sm button-resubmit-po'> <i class='fa fa-search'></i> Re-Submit PO</button>
                        `,
                        "className": "text-center",
                        "width": "10%"
                    }
                ]
            });

            $('#tbl-submitted-list tbody').on('click', '.button-resubmit-po', function () {
                let _token   = $('meta[name="csrf-token"]').attr('content');
                var tabledata = $('#tbl-submitted-list').DataTable();
                var tr = $(this).closest('tr');
                var row = tabledata.row( tr );
                var d = row.data();
                console.log(d)
                console.log(row.child.isShown())
                $.ajax({
                    url: base_url+'/proc/submitpo/submitdata',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data:{
                        ponum: d.ponum,
                        _token: _token
                    },
                    dataType: 'json',
                    cache:false,
                    success: function(result){
                    },
                    error: function(err){
                        console.log(err)
                    }
                }).done(function(data){

                    console.log(data)
                    if(data.status == '200'){
                        toastr.info(data.data)
                    }else{
                        toastr.error(error)
                    }
                });
            });

            $('#tbl-submitted-list tbody').on('click', 'tr td.details-control', function () {
                let _token   = $('meta[name="csrf-token"]').attr('content');
                var tabledata = $('#tbl-submitted-list').DataTable();
                var tr = $(this).closest('tr');
                var row = tabledata.row( tr );
                var d = row.data();
                console.log(d)
                console.log(row.child.isShown())
                if ( row.child.isShown() ) {
                    row.child.hide();
                    tr.removeClass('shown');
                }else{
                    $.ajax({
                        url: base_url+'/proc/submitpo/getitems',
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data:{
                            ponum: d.ponum,
                            _token: _token
                        },
                        dataType: 'json',
                        cache:false,
                        success: function(result){
                        },
                        error: function(err){
                            console.log(err)
                        }
                    }).done(function(data){

                        if ( row.child.isShown() ) {
                            row.child.hide();
                            tr.removeClass('shown');
                        }
                        else {
                            row.child( format(row.data(), data) ).show();
                            tr.addClass('shown');
                        }
                    });
                }

                function format ( d, results ) {
                    // console.log(results)
                    var tdStyle = '';
                    var appStat = '';
                    var appNote = '';
                    var appDate = '';
                    var appBy   = '';

                    var html = '';
                    html = `<table class="table table-bordered table-hover table-striped table-sm">
                        <thead>
                                <th>Line Item</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Budget Code</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total Price</th>
                        </thead>
                        <tbody>`;
                        for(var i = 0; i < results.length; i++){
                            html +=`
                            <tr style="background-color:gray; color:white;">
                                <td> `+ results[i].poitem +` </td>
                                <td> `+ results[i].material +` </td>
                                <td> `+ results[i].matdesc +` </td>
                                <td> `+ results[i].budget_code_num +` </td>
                                <td style="text-align:right;"> `+ (results[i].quantity*1) +` `+ results[i].unit +` </td>
                                <td style="text-align:right;"> `+ (results[i].price*1) +` </td>
                                <td style="text-align:right;"> `+ (results[i].price*1)*(results[i].quantity*1) +` </td>
                            </tr>
                            `;
                        }

                    html +=`</tbody>
                            </table>`;
                    return html;
                }
            });
        }


        $('.inputNumber').on('change', function(){
            this.value = formatRupiah(this.value,'');
        });

        function formatRupiah(angka, prefix){
            var number_string = angka.toString().replace(/[^.\d]/g, '').toString(),
            split   		  = number_string.split('.'),
            sisa     		  = split[0].length % 3,
            rupiah     		  = split[0].substr(0, sisa),
            ribuan     		  = split[0].substr(sisa).match(/\d{3}/gi);

            if(ribuan){
                separator = sisa ? ',' : '';
                rupiah += separator + ribuan.join(',');
            }

            rupiah = split[1] != undefined ? rupiah + '.' + split[1] : rupiah;
            return prefix == undefined ? rupiah : (rupiah ? '' + rupiah : '');
        }
    });
</script>
@endsection
