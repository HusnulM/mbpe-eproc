@extends('layouts/App')

@section('title', 'Pembuatan PBJ')

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
    <!-- <form id="form-pbj-data" action="{{ url('transaction/pbj/save') }}" method="post" enctype="multipart/form-data"> -->
    <form id="form-pbj-data" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Pembuatan PBJ</h3>
                        <div class="card-tools">
                            <button type="submit" class="btn btn-primary btn-sm btn-submit">
                                <i class="fas fa-save"></i> Simpan PBJ
                            </button>
                            <a href="{{ url('/transaction/pbj/list') }}" class="btn btn-success btn-sm">
                                <i class="fa fa-list"></i> List PBJ
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4 col-md-12">
                                <div class="row">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="tglpbj">Tanggal PBJ</label>
                                            <input type="date" name="tglpbj" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="requestto">Tujuan Permintaan</label>
                                            <input type="text" name="requestto" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="kepada">Kepada</label>
                                            <!-- <input type="text" name="kepada" class="form-control"> -->
                                            <select name="kepada" id="kepada" class="form-control" required>
                                                <option value="">Pilih Department</option>
                                                @foreach($department as $key => $row)
                                                    <option value="{{ $row->department }}">{{ $row->department }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="unitdesc">Unit Desc / Code</label>
                                            <select name="unitdesc" id="find-unitdesc" class="form-control"></select>
                                            <!-- <input type="text" name="unitdesc" class="form-control"> -->
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="engine">Engine Model</label>
                                            <input type="text" name="engine" id="engine_model" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="chassis">Chassis S/N</label>
                                            <input type="text" name="chassis" id="chassis" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="refrensi">Refrensi Permintaan</label>
                                            <input type="text" name="refrensi" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="requestor">Requestor</label>
                                            <input type="text" name="requestor" class="form-control" value="{{ Auth::user()->name }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="typeModel">Type / Model</label>
                                            <input type="text" name="typeModel" id="typeModel" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="user">User</label>
                                            <!-- <input type="text" name="user" class="form-control"> -->
                                            <select name="user" id="user" class="form-control" required>
                                                <option value="">Pilih Mekanik</option>
                                                @foreach($mekanik as $key => $row)
                                                    <option value="{{ $row->nama }}">{{ $row->nama }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="kodeJasa">Kode Barang / Jasa</label>
                                            <input type="text" name="kodeJasa" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="nginesn">Engine S/N</label>
                                            <input type="text" name="nginesn" id="nginesn" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="hmkm">HM</label>
                                            <input type="text" name="hmkm" id="hmkm" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="km">KM</label>
                                            <input type="text" name="km" id="km" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="budgetcode">Budget / Cost Code</label>
                                            <input type="text" name="budgetcode" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="no_rangka">No. Rangka</label>
                                            <input type="text" name="no_rangka" id="no_rangka" class="form-control">
                                        </div>
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col-lg-12 col-md-12">
                                        <div class="form-group">
                                            <label for="attachment">Attachment</label>
                                            <input type="file" class="form-control" name="efile[]" multiple="multiple">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-8 col-md-12">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <table class="table table-sm">
                                            <thead>
                                                <th>Part Number</th>
                                                {{-- <th>Description</th> --}}
                                                <th>Quantity</th>
                                                <th>Unit</th>
                                                <th>Figure</th>
                                                <th>Remark</th>
                                                <th style="text-align:right;">
                                                    <button type="button" class="btn btn-success btn-sm btn-add-pbj-item">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-success btn-sm btn-add-pbj-rab">
                                                        <i class="fa fa-plus">List RAB</i>
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
                            <th>Part Number</th>
                            <th>Warehouse</th>
                            <th>Warehouse Name</th>
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
@endsection

@section('additional-js')
<script src="{{ asset('/assets/js/select2.min.js') }}"></script>
<script>
    $(document).ready(function(){

        var count = 0;

        let _token   = $('meta[name="csrf-token"]').attr('content');
        let selected_items = [];
        var fCount = 0;


        function loadMaterial(){
            $("#tbl-material-list").DataTable({
                serverSide: true,
                ajax: {
                    url: base_url+'/matstock',
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
                    {data: "partnumber", className: 'fname'},
                    {data: "whsnum", className: 'fname'},
                    {data: "whsname", className: 'fname'},
                    {data: "quantity", className: 'fname'},
                    {data: "unit", className: 'fname'},
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
                                <input type="text" name="quantity[]" class="form-control" onkeypress="`+validate(event)+`" required>
                            </td>
                            <td>
                                <input type="text" name="uoms[]" id="partunit`+fCount+`" value="`+ selected_data.unit +`" class="form-control" readonly>
                            </td>
                            <td>
                                <input type="text" name="figures[]" class="form-control" required>
                            </td>
                            <td>
                                <input type="text" name="remarks[]" class="form-control" required>
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

        $(document).on('select2:open', (event) => {
            const searchField = document.querySelector(
                `.select2-search__field`,
            );
            if (searchField) {
                searchField.focus();
            }
        });
        $('#find-unitdesc').select2({
            placeholder: 'Type Unit Desc / Code',
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: base_url + '/master/kendaraan/findkendaraan',
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
                                text: item.no_kendaraan + ' - ' + item.model_kendaraan,
                                slug: item.model_kendaraan,
                                id: item.no_kendaraan,
                                ...item
                            }
                        })
                    };
                },
                cache: true
            }
        });

        $('#find-unitdesc').on('change', function(){
            var data = $('#find-unitdesc').select2('data')
            console.log(data);
            $('#typeModel').val(data[0].model_kendaraan);
            $('#engine').val(data[0].engine_sn);
            $('#chassis').val(data[0].chassis_sn);
            $('#engine_model').val(data[0].engine_model);
            $('#nginesn').val(data[0].engine_sn);
            $('#km').val(data[0].last_km);
            $('#hmkm').val(data[0].last_hm);
            $('#no_rangka').val(data[0].no_rangka);
            $('#tahun').val(data[0].tahun);
            $('#bahan_bakar').val(data[0].bahan_bakar);
        });

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

        function formatNumber(num) {
            return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
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

        $('#form-pbj-data').on('submit', function(event){
            event.preventDefault();

            var formData = new FormData(this);
            console.log($(this).serialize());
            $.ajax({
                url:base_url+'/transaction/pbj/save',
                method:'post',
                data:formData,
                dataType:'JSON',
                contentType: false,
                cache: false,
                processData: false,
                beforeSend:function(){
                    $('.btn-submit').attr('disabled',true);
                },
                success:function(data)
                {
                    console.log(data);
                },
                error:function(err){
                    showErrorMessage(JSON.stringify(err))
                }
            }).done(function(result){
                if(result.msgtype === "200"){
                    toastr.success(result.message);
                    $(".btn-submit").attr("disabled", false);

                    setTimeout(function(){
                        location.reload();
                    }, 2000);
                }else if(result.msgtype === "400"){
                    toastr.error(result.message)
                    $(".btn-submit").attr("disabled", false);
                }
            });
        });

    });
</script>
@endsection
