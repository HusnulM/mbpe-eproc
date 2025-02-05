@extends('layouts/App')

@section('title', 'Pembuatan Purchase Request')

@section('additional-css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style type="text/css">
        .select2-container {
            display: block
        }

        .select2-container .select2-selection--single {
            height: 36px;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    <form id="form-submit-data" method="post" enctype="multipart/form-data">
    <!-- <form id="form-submit-dataxx" action="/proc/pr/save" method="post" enctype="multipart/form-data"> -->
        @csrf
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Purchase Request</h3>
                        <div class="card-tools">
                            <button type="submit" class="btn btn-primary btn-sm btn-save-pr">
                                <i class="fas fa-save"></i> Simpan Purchase Request
                            </button>
                            <a href="{{ url('/proc/pr/listpr') }}" class="btn btn-success btn-sm">
                                <i class="fa fa-list"></i> List PR
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-2 col-md-12">
                                <div class="row">
                                    <div class="col-lg-12 col-md-12">
                                        <div class="form-group">
                                            <label for="tglreq">Tanggal Request</label>
                                            <input type="date" name="tglreq" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 col-md-12">
                                        <div class="form-group">
                                            <label for="requestor">Requestor</label>
                                            <input type="text" name="requestor" class="form-control" value="{{ Auth::user()->name }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-12 col-md-12">
                                        <div class="form-group">
                                            <label for="department">Department</label>
                                            <input type="text" name="department" class="form-control" value="{{ getUserDepartment() }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 col-md-12">
                                        <div class="form-group">
                                            <label for="remark">Remark</label>
                                            <textarea name="remark" id="remark" cols="30" rows="4" class="form-control" placeholder="Remark..."></textarea>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 col-md-12">
                                        <div class="form-group">
                                            <label for="attachment">Attachment</label>
                                            <input type="file" class="form-control" name="efile[]" multiple="multiple">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-10 col-md-12">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <table class="table table-sm">
                                            <thead>
                                                <th>Part Number</th>
                                                <!-- <th>Description</th> -->
                                                <th>Quantity</th>
                                                <th>Unit</th>
                                                <th>PBJ Reference</th>
                                                <th>Project</th>
                                                <th>Kode Budget</th>
                                                <th style="text-align:right;">
                                                    <button type="button" class="btn btn-success btn-sm btn-add-pbj-item">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-success btn-sm btn-select-pbj">
                                                        <i class="fa fa-list"></i> List PBJ
                                                    </button>
                                                </th>
                                            </thead>
                                            <tbody id="tbl-pbj-body">

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
    </form>
</div>

@endsection

@section('additional-modal')
<div class="modal fade" id="modal-add-material">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Pilih Material</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-lg-12">
                    <table id="tbl-material-list" class="table table-bordered table-hover table-striped table-sm" style="width:100%;">
                        <thead>
                            <th>No</th>
                            <th>Material</th>
                            <th>Description</th>
                            {{-- <th>Part Number</th>
                            <th>Warehouse</th>
                            <th>Warehouse Name</th> --}}
                            <th>Available Quantity</th>
                            <th>Unit</th>
                            <th></th>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
</div>
<div class="modal fade" id="modal-list-pbj">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Pilih PBJ</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12">
                        <table id="tbl-pbj-list" class="table table-bordered table-hover table-striped table-sm" style="width:100%;">
                            <thead>
                                <th></th>
                                <th>Nomor PBJ</th>
                                <th>Tanggal PBJ</th>
                                <th>Project</th>
                                <th>No. Plat</th>
                                <th>Part Number</th>
                                <th>Part Name</th>
                                <th>Quantity</th>
                                <th>Open Qty</th>
                                <th>Unit</th>
                                <th>Figure</th>
                                <th>Remark</th>
                                <th>Kode Budget</th>
                                <th style="width:50px; text-align:center;">

                                </th>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <!-- <button type="submit" class="btn btn-primary">Save</button> -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('additional-js')
<script src="{{ asset('/assets/js/select2.min.js') }}"></script>
<script>
    $(document).ready(function(){
        let selected_pbj_items = [];
        let selected_items = [];
        var count = 0;

        let _token   = $('meta[name="csrf-token"]').attr('content');

        $('.btn-select-pbj').on('click', function(){
            loadListPBJ();
            $('#modal-list-pbj').modal('show');
        });

        var fCount = 0;

        function loadMaterial(){
            $("#tbl-material-list").DataTable({
                serverSide: true,
                ajax: {
                    url: base_url+'/allmaterial',
                    data: function (data) {
                        data.params = {
                            sac: "sac"
                        }
                    }
                },
                buttons: false,
                columns: [
                    { "data": null,"sortable": false, "searchable": false,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {data: "material", className: 'uid'},
                    {data: "matdesc", className: 'fname'},
                    // {data: "partnumber", className: 'fname'},
                    // {data: "whsnum", className: 'fname'},
                    // {data: "whsname", className: 'fname'},
                    {data: "availableQty", "className": "text-right"},
                    {data: "matunit", className: 'fname'},
                    {"defaultContent":
                        "<button type='button' class='btn btn-primary btn-sm button-add-material'> <i class='fa fa-plus'></i> Add</button>"
                    }
                ],
                "bDestroy": true,
            });

            $("#tbl-material-list tbody").on('click', '.button-add-material', function(){
                var menuTable = $('#tbl-material-list').DataTable();
                selected_data = [];
                selected_data = menuTable.row($(this).closest('tr')).data();

                if(checkSelectedMaterial(selected_data.material)){
                    console.log(selected_items);
                }else{
                    console.log(selected_data);
                    selected_items.push(selected_data);
                    fCount = fCount + 1;
                    $('#tbl-pbj-body').append(`
                        <tr>
                            <td>
                                `+selected_data.material+` - `+ selected_data.matdesc +`
                                <input type="hidden" name="parts[]" id="parts`+fCount+`" class="form-control" value="`+ selected_data.material +`" readonly>
                                <input type="hidden" name="partdesc[]" id="partdesc`+fCount+`" class="form-control" value="`+ selected_data.matdesc +`" readonly>
                            </td>

                            <td>
                                <input type="text" name="quantity[]" class="form-control inputNumber" required>
                            </td>
                            <td>
                                <input type="text" name="uoms[]" id="partunit`+fCount+`" value="`+ selected_data.matunit +`" class="form-control" readonly>
                            </td>
                            <td>
                                <input type="text" name="pbjref[]" id="pbjref`+fCount+`" class="form-control">
                                <input type="hidden" name="pbjnum[]" id="pbjnum`+fCount+`" class="form-control">
                                <input type="hidden" name="pbjitm[]" id="pbjitm`+fCount+`" class="form-control">
                                <input type="hidden" name="nopol[]" id="nopol`+fCount+`" class="form-control">
                            </td>
                            <td>
                                <select name="project[]" id="find-project`+fCount+`" class="form-control"></select>

                            </td>
                            <td>
                                <input type="text" name="kodebudget[]" id="kodebudget`+fCount+`" class="form-control" value="">
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger" id="btnRemove`+fCount+`">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);

                    $('#btnRemove'+fCount).on('click', function(e){
                        e.preventDefault();
                        var row_index = $(this).closest("tr").index();
                        removeItem(row_index);
                        $(this).closest("tr").remove();
                    });

                    $('#find-project'+fCount).select2({
                        placeholder: 'Nama Project',
                        width: '100%',
                        minimumInputLength: 0,
                        ajax: {
                            url: base_url + '/master/project/findproject',
                            dataType: 'json',
                            delay: 250,
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': _token
                            },
                            data: function (params) {
                                var query = {
                                    search: params.term,
                                    // custname: $('#find-customer').val()
                                }
                                return query;
                            },
                            processResults: function (data) {
                                // return {
                                //     results: response
                                // };
                                console.log(data)
                                return {
                                    results: $.map(data.data, function (item) {
                                        return {
                                            text: item.kode_project + ' - ' + item.nama_project,
                                            slug: item.nama_project,
                                            id: item.idproject,
                                            ...item
                                        }
                                    })
                                };
                            },
                            cache: true
                        }
                    });

                    // $('#find-project'+fCount).on('change', function(){
                    //     var data = $('#find-project'+fCount).select2('data')
                    //     console.log(data);
                    //     // $('#project'+fCount).val(data[0].idproject);

                    // });
                }
            });
        }

        function checkSelectedMaterial(pMaterial) {
            return selected_items.some(function(el) {
                if(el.material === pMaterial){
                    return true;
                }else{
                    return false;
                }
            });
        }

        function removeItem(index){
            selected_items.splice(index, 1);
        }

        $('.btn-add-pbj-item').on('click', function(){
            loadMaterial();
            $('#modal-add-material').modal('show');
        });

        function loadListPBJ(){
            $("#tbl-pbj-list").DataTable({
                serverSide: true,
                ajax: {
                    url: base_url+'/proc/pr/listapprovedpbj',
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
                    {data: "pbjnumber", className: 'uid'},
                    {data: "tgl_pbj", className: 'uid'},
                    {data: "nama_project", className: 'uid'},
                    {data: "unit_desc", className: 'uid'},
                    {data: "partnumber"},
                    {data: "description"},
                    {data: "quantity", "className": "text-right",},
                    {data: "openqty", "className": "text-right",},
                    {data: "unit"},
                    {data: "figure"},
                    {data: "remark"},
                    {data: "budget_code"},
                    {"defaultContent":
                        `
                        <button class='btn btn-success btn-sm button-add-pbj-to-pritem'> <i class="fa fa-plus"></i></button>
                        `,
                        "className": "text-center",
                        "width": "10%"
                    }
                ] ,
                bDestroy: true,
            });

            function checkPbjSelected(pbjNum, pbjItem) {
                return selected_pbj_items.some(function(el) {
                    if(el.pbjnumber === pbjNum && el.pbjitem === pbjItem){
                        return true;
                    }else{
                        return false;
                    }
                });
            }

            function removePbjItem(index){
                selected_pbj_items.splice(index, 1);
            }

            $('#tbl-pbj-list tbody').on( 'click', '.button-add-pbj-to-pritem', function () {
                var table = $('#tbl-pbj-list').DataTable();
                selected_data = [];
                selected_data = table.row($(this).closest('tr')).data();


                if(checkPbjSelected(selected_data.pbjnumber, selected_data.pbjitem)){
                    console.log(selected_pbj_items);
                }else{
                    selected_pbj_items.push(selected_data);
                    console.log(selected_pbj_items);
                    fCount = fCount + 1;
                    var _qty = selected_data.openqty;
                    _qty     = _qty.replace('.000', '');
                    $('#tbl-pbj-body').append(`
                        <tr>
                            <td>
                                `+selected_data.partnumber+` - `+ selected_data.description +`
                                <input type="hidden" name="parts[]" id="parts`+fCount+`" class="form-control" value="`+ selected_data.partnumber +`" readonly>
                                <input type="hidden" name="partdesc[]" id="partdesc`+fCount+`" class="form-control" value="`+ selected_data.description +`" readonly>
                            </td>

                            <td>
                                <input type="text" name="quantity[]" class="form-control inputNumber" value="`+_qty+`" style="text-align:right;" required>
                            </td>
                            <td>
                                <input type="text" name="uoms[]" id="partunit`+fCount+`" class="form-control" value="`+selected_data.unit+`" readonly>
                            </td>
                            <td>
                                <input type="text" name="pbjref[]" id="pbjref`+fCount+`" class="form-control" value="`+selected_data.pbjnumber+`" readonly>
                                <input type="hidden" name="pbjnum[]" id="pbjnum`+fCount+`" class="form-control" value="`+selected_data.pbjnumber+`">
                                <input type="hidden" name="pbjitm[]" id="pbjitm`+fCount+`" class="form-control" value="`+selected_data.pbjitem+`">
                                <input type="hidden" name="nopol[]" id="nopol`+fCount+`" class="form-control" value="`+selected_data.unit_desc+`">
                            </td>
                            <td>
                                <input type="text" name="namaproject[]" class="form-control" value="`+ selected_data.nama_project +`" readonly>
                                <input type="hidden" name="project[]" id="project`+fCount+`" value="`+ selected_data.idproject +`">
                            </td>
                            <td>
                                <input type="text" name="kodebudget[]" id="kodebudget`+fCount+`" class="form-control" value="`+ selected_data.budget_code +`" readonly>
                            </td>
                            <td style="text-align:center;">
                                <button type="button" class="btn btn-danger btnRemove" id="btnRemove`+fCount+`">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);

                    $('#btnRemove'+fCount).on('click', function(e){
                        e.preventDefault();
                        var row_index = $(this).closest("tr").index();
                        removePbjItem(row_index);
                        $(this).closest("tr").remove();

                        console.log(selected_pbj_items);
                    });

                    $('.inputNumber').on('change', function(){
                        this.value = formatRupiah(this.value,'');
                    });

                    $('.inputNumber').on('keypress', function(e){
                        validate(e);
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
                }

            });

        }

        // action="{{ url('proc/pr/save') }}"
        $('#form-submit-data').on('submit', function(event){
            event.preventDefault();
            var formData = new FormData(this);
            console.log($(this).serialize())
            $.ajax({
                url:base_url+'/proc/pr/save',
                method:'post',
                data:formData,
                dataType:'JSON',
                contentType: false,
                cache: false,
                processData: false,
                beforeSend:function(){
                    $('.btn-save-pr').attr('disabled','disabled');
                    // showBasicMessage();
                },
                success:function(data)
                {

                },
                error:function(error){
                    toastr.error(error)
                    setTimeout(function(){
                        location.reload();
                    }, 2000);
                }
            }).done(function(result){
                console.log(result)
                if(result.msgtype === "200"){
                    toastr.success(result.message)
                    setTimeout(function(){
                        window.location.href = base_url+'/proc/pr';
                    }, 2000);
                }else{
                    toastr.error(result.message)
                    setTimeout(function(){
                        location.reload();
                    }, 2000);
                }
            }) ;

        });
    });
</script>
@endsection
