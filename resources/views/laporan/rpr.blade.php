@extends('layouts/App')

@section('title', 'Laporan PR')

@section('additional-css')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Laporan Purchase Request</h3>
                    <div class="card-tools">
                        <!-- <a href="{{ url('transaction/budgeting') }}" class="btn btn-success btn-sm btn-add-dept">
                            <i class="fas fa-plus"></i> Buat Pengajuan Budget
                        </a> -->
                        <!-- <a href="{{ url('/master/department/create') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Create Department
                        </a> -->
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <form action="{{ url('report/exportpr') }}" method="post">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-2">
                                        <label for="">Tanggal PR</label>
                                        <input type="date" class="form-control" name="datefrom" id="datefrom" value="{{ $_GET['datefrom'] ?? '' }}">
                                    </div>
                                    <div class="col-lg-2">
                                        <label for="">-</label>
                                        <input type="date" class="form-control" name="dateto" id="dateto" value="{{ $_GET['dateto'] ?? '' }}">
                                    </div>
                                    <div class="col-lg-2">
                                        <label for="">Approval Status</label>
                                        <select name="approvalStatus" id="approvalStatus" class="form-control">
                                            <option value="All">All</option>
                                            <option value="O">Open</option>
                                            <option value="A">Approved</option>
                                            <option value="R">Rejected</option>
                                        </select>
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
                                    <div class="col-lg-4" style="text-align:right;">
                                        <br>
                                        <button type="button" class="btn btn-default mt-2 btn-search">
                                            <i class="fa fa-search"></i> Filter
                                        </button>
                                        <button type="submit" class="btn btn-success mt-2 btn-export">
                                            <i class="fa fa-download"></i> Export Data
                                        </button>
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
                                    <th>Nomor PR</th>
                                    <th>Tanggal PR</th>
                                    <th>Partnumber</th>
                                    <th>Description</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Remark</th>
                                    <th>PBJ Number</th>
                                    <th>PBJ Item</th>
                                    <th>Kode Budget</th>
                                    {{-- <th>Budget Period</th> --}}
                                    <th>PO Created</th>
                                    <th style="width:7%;"></th>
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
            var param = '?datefrom='+ $('#datefrom').val() +'&dateto='+ $('#dateto').val()+'&department='+$('#department').val()+'&approvalstat='+$('#approvalStatus').val();
            loadDocument(param);
        });

        loadDocument('');

        function loadDocument(_params){
            $("#tbl-budget-list").DataTable({
                serverSide: true,
                ajax: {
                    url: base_url+'/report/prlist'+_params,
                    data: function (data) {
                        data.params = {
                            sac: "sac"
                        }
                    }
                },
                buttons: false,
                searching: true,
                scrollY: 500,
                scrollX: true,
                scrollCollapse: true,
                bDestroy: true,
                columns: [
                    { "data": null,"sortable": false, "searchable": false,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {data: "prnum", className: 'uid'},
                    {data: "prdate", className: 'uid',
                        render: function (data, type, row){
                            return ``+ row.prdate.prdate1 + ``;
                        }
                    },
                    {data: "material", className: 'uid'},
                    {data: "matdesc", className: 'uid'},
                    {data: "quantity", "sortable": false,
                        render: function (data, type, row){
                            return ``+ row.quantity.qty1 + ``;
                        },
                        "className": "text-right",
                    },
                    {data: "unit"},
                    {data: "deptname"},
                    {data: "approvestat",
                        render: function (data, type, row){
                            if(row.approvestat == "O"){
                                return `Open`;
                            }else if(row.approvestat == "A"){
                                return `Approved`;
                            }else if(row.approvestat == "R"){
                                return `Rejected`;
                            }else{
                                return `Open`;
                            }
                        }
                    },
                    {data: "remark" },
                    {data: "pbjnumber" },
                    {data: "pbjitem" },
                    {data: "budget_code" },
                    // {data: "periode" },
                    {data: "pocreated" },
                    {"defaultContent":
                        `<button class='btn btn-success btn-sm button-print'> <i class='fa fa-print'></i> Print</button>
                        `,
                        "className": "text-center",
                    }
                ]
            });

            $('#tbl-budget-list tbody').on( 'click', '.button-print', function () {
                var table = $('#tbl-budget-list').DataTable();
                selected_data = [];
                selected_data = table.row($(this).closest('tr')).data();
                // window.location = base_url+"/proc/pr/print";
                // if(selected_data.doctype === "Corporate Procedure"){
                    window.open(
                        base_url+"/proc/pr/print/"+selected_data.id,
                        '_blank' // <- This is what makes it open in a new window.
                    );
                // }
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
