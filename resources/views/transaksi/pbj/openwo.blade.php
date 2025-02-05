@extends('layouts/App')

@section('title', 'List Open Work Order')

@section('additional-css')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"></h3>
                    <div class="card-tools">

                        <a href="{{ url('/transaction/list/pbj') }}" class="btn btn-success btn-sm">
                            <i class="fa fa-list"></i> List PBJ
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tbl-data-ceklist" class="table table-bordered table-hover table-striped table-sm" style="width:100%;">
                            <thead>
                                <th>No</th>
                                <th>WO Number</th>
                                <th>Keterangan</th>
                                <th>Tanggal WO</th>
                                <th>No Plat</th>
                                <th>Jenis Kendaraan</th>
                                <th>Nomor Rangka</th>
                                <th>Schedule Type</th>
                                <th>Issued</th>
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
        $("#tbl-data-ceklist").DataTable({
            serverSide: true,
            ajax: {
                // url: base_url+'/datachecklistkendaraan/datachecklisttidaklayak',
                url: base_url+'/transaction/pbj/listopenwo',
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
            columns: [
                { "data": null,"sortable": false, "searchable": false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {data: "wonum", className: 'uid'},
                {data: "description", className: 'uid'},
                {data: "wodate"},
                {data: "license_number"},
                {data: "model_kendaraan"},
                {data: "no_rangka" },
                {data: "schedule_type"},
                {data: "issued"},
                {"defaultContent":
                    `
                    <button class='btn btn-success btn-sm button-create-pbj'> <i class='fa fa-plus'></i> Create PBJ</button>
                    <button class='btn btn-primary btn-sm button-detail'> <i class='fa fa-search'></i> Detail</button>
                    `,
                    "className": "text-center",
                    "width": "13%"
                }
            ]
        });

        $('#tbl-data-ceklist tbody').on( 'click', '.button-detail', function () {
            var table = $('#tbl-data-ceklist').DataTable();
            selected_data = [];
            selected_data = table.row($(this).closest('tr')).data();
            console.log(selected_data)
            window.location = base_url+"/transaction/pbj/wo/detail/"+selected_data.id;
        });

        $('#tbl-data-ceklist tbody').on( 'click', '.button-create-pbj', function () {
            var table = $('#tbl-data-ceklist').DataTable();
            selected_data = [];
            selected_data = table.row($(this).closest('tr')).data();
            console.log(selected_data)
            window.location = base_url+"/transaction/pbj/create/"+selected_data.id;
        });

        // $('#tbl-pbj-list tbody').on( 'click', '.button-print', function () {
        //     var table = $('#tbl-pbj-list').DataTable();
        //     selected_data = [];
        //     selected_data = table.row($(this).closest('tr')).data();
        //         window.open(
        //             base_url+"/printdoc/pbj/print/"+selected_data.id,
        //             '_blank'
        //         );
        // });


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
