@extends('layouts/App')

@section('title', 'Laporan Payment')

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
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Laporan Payment</h3>
                    <div class="card-tools">
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            {{-- <form action="{{ url('report/exportreturbast') }}" method="post">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-2">
                                        <label for="">Tanggal Retur BAST</label>
                                        <input type="date" class="form-control" name="datefrom" id="datefrom" value="{{ $_GET['datefrom'] ?? '' }}">
                                    </div>
                                    <div class="col-lg-2">
                                        <label for="">-</label>
                                        <input type="date" class="form-control" name="dateto" id="dateto" value="{{ $_GET['dateto'] ?? '' }}">
                                    </div>
                                    <div class="col-lg-8" style="text-align:right;">
                                        <br>
                                        <button type="button" class="btn btn-default mt-2 btn-search">
                                            <i class="fa fa-search"></i> Filter
                                        </button>
                                        <button type="submit" class="btn btn-success mt-2 btn-export">
                                            <i class="fa fa-download"></i> Export Data
                                        </button>
                                    </div>
                                </div>
                            </form> --}}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="table-responsive">
                            <table id="tbl-budget-list" class="table table-bordered table-hover table-striped table-sm" style="width:100%;">
                                <thead>
                                    {{-- <th>No</th> --}}
                                    <tr>
                                        <th>Nomor PO</th>
                                        <th>Status</th>
                                        <th>Last Update</th>
                                    </tr>
                                    {{-- <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr> --}}
                                </thead>
                                <tbody>

                                </tbody>
                                {{-- <tfoot>
                                    <tr>
                                        <th>Nomor PO</th>
                                        <th>Status</th>
                                        <th>Last Update</th>
                                    </tr>
                                </tfoot> --}}
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
<script src="{{ asset('/assets/js/select2.min.js') }}"></script>
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
            var param = '?datefrom='+ $('#datefrom').val() +'&dateto='+ $('#dateto').val();
            loadDocument(param);
        });

        $(document).on('select2:open', (event) => {
            const searchField = document.querySelector(
                `.select2-search__field`,
            );
            if (searchField) {
                searchField.focus();
            }
        });

        loadDocument('');

        function loadDocument(_params){
            $('#tbl-budget-list thead tr').clone(true).appendTo( '#tbl-budget-list thead' );
            $('#tbl-budget-list thead tr:eq(1) th').each( function (i) {
                var title = $(this).text();
                $(this).html( '<input type="text" class="form-control" placeholder="Search '+title+'" />' );

                $( 'input', this ).on( 'keyup change', function () {
                    if ( table.column(i).search() !== this.value ) {
                        table
                            .column(i)
                            .search( this.value )
                            .draw();
                    }
                } );
            } );
            var table = $("#tbl-budget-list").DataTable({
                serverSide: false,
                ajax: {
                    url: base_url+'/report/paymentlist',
                    data: function (data) {
                        data.params = {
                            sac: "sac"
                        }
                    }
                },
                orderCellsTop: true,
                buttons: true,
                searching: true,
                scrollY: 500,
                scrollX: true,
                scrollCollapse: true,
                bDestroy: false,
                columns: [
                    // { "data": null,"sortable": true, "searchable": false,
                    //     render: function (data, type, row, meta) {
                    //         return meta.row + meta.settings._iDisplayStart + 1;
                    //     }
                    // },
                    {data: "no_po", className: 'uid'},
                    {data: "status"},
                    {data: "last_update", className: 'uid'}
                ],
                // initComplete: function () {
                //     this.api()
                //         .columns()
                //         .every(function () {
                //             let column = this;

                //             // Create select element
                //             let select = document.createElement('select');
                //             // select.classList.add('form-control');
                //             select.className = 'form-control';
                //             select.add(new Option(''));
                //             column.footer().replaceChildren(select);

                //             // Apply listener for user change in value
                //             select.addEventListener('change', function () {
                //                 column
                //                     .search(select.value, {exact: true})
                //                     .draw();
                //             });

                //             // Add list of options
                //             column
                //                 .data()
                //                 .unique()
                //                 .sort()
                //                 .each(function (d, j) {
                //                     select.add(new Option(d));
                //                 });
                //         });
                // }
            });


        }
    });
</script>
@endsection
