@extends('layouts/App')

@section('title', 'RETUR BAST Approval')

@section('additional-css')
{{-- https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css
https://cdn.datatables.net/2.2.1/css/dataTables.bootstrap5.css --}}
{{-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">

<script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script> --}}

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('/assets/css/customstyle.css') }}">
    <style type="text/css">
        .select2-container {
            display: block
        }

        .select2-container .select2-selection--single {
            height: 36px;
        }
    </style>

<script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-3">
            <form action="{{ url('/approve/bast/save') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Approve Retur BAST <b>[ {{ $header->nota_retur }} ]</b></h3>
                        <div class="card-tools">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12">

                                <div class="form-group">
                                    <label for="doctitle">BAST Number</label>
                                    <p>{{ $header->no_bast }}</p>
                                    <input type="hidden" id="no_bast" value="{{ $header->no_bast }}">
                                </div>
                                <div class="form-group">
                                    <label>Di Proses Oleh:</label> {{ getUserNameByID($header->createdby) }}
                                </div>
                                <div class="form-group">
                                    <label>Terima Dari:</label> {{ $header->name }}
                                </div>
                                <div class="form-group">
                                    <label>Tanggal RETUR:</label>
                                    <p>{!! formatDate($header->tgl_retur) !!}
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label>Remark</label>
                                    <p>{!! $header->remark !!}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header">
                    <!-- <h3 class="card-title">Approve Document</h3> -->
                    <div class="row">
                        <ul class="nav nav-tabs" id="custom-content-above-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="custom-content-above-home-tab" data-toggle="pill" href="#custom-content-above-home" role="tab" aria-controls="custom-content-above-home" aria-selected="true">RETUR BAST Items</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-content-above-approval-tab" data-toggle="pill" href="#custom-content-above-approval" role="tab" aria-controls="custom-content-above-approval" aria-selected="false">Approval Status</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-tools">
                        <a href="{{ url('/approve/retur') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="tab-content" id="custom-content-above-tabContent">
                                <div class="tab-pane fade show active" id="custom-content-above-home" role="tabpanel" aria-labelledby="custom-content-above-home-tab">
                                    <div class="row">
                                        <div class="col-lg-12">
                                        </div>

                                        <div class="col-lg-12">
                                            <td colspan="9" style="text-align: right;">
                                                <button type="button" class="btn btn-success pull-right ml-1 btn-sm" id="btn-approve-items">
                                                    <i class="fa fa-check"></i> APPROVE
                                                </button>

                                                <button type="button" class="btn btn-danger pull-right btn-sm" id="btn-reject-items">
                                                    <i class="fa fa-xmark"></i> REJECT
                                                </button>
                                            </td>
                                            <table id="tbl-pr-data" class="table table-bordered table-hover table-striped table-sm">
                                                <thead>
                                                    <th>No</th>
                                                    <th>Item Code</th>
                                                    <th>Item Name</th>
                                                    <th style="text-align:right;">Retur Quantity</th>
                                                    <th>Unit</th>
                                                    <th>Warehouse</th>
                                                    <th>NO. BAST</th>
                                                    <th>PBJ Number</th>
                                                    <th>Remark</th>
                                                </thead>
                                                <tbody>
                                                @foreach($items as $key => $row)
                                                    <tr>
                                                        <td>{{ $key+1 }}</td>
                                                        <td>
                                                            {{ $row->material }}
                                                        </td>
                                                        <td>
                                                            {{ $row->matdesc }}
                                                        </td>
                                                        <td style="text-align:right;">
                                                            {{ number_format($row->quantity,0) }}
                                                        </td>
                                                        <td>
                                                            {{ $row->unit }}
                                                        </td>
                                                        <td>
                                                            {{ $row->whsname }}
                                                        </td>
                                                        <td>
                                                            {{ $row->no_bast }}
                                                        </td>
                                                        <td>
                                                            {{ $row->refdoc }}
                                                        </td>
                                                        <td>
                                                            {{ $row->remark }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                                <tfoot>

                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="custom-content-above-approval" role="tabpanel" aria-labelledby="custom-content-above-approval-tab">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <table id="tbl-approval" class="table table-bordered table-hover table-striped table-sm" style="width:100%;">
                                                <thead>
                                                    {{-- <th>Opnam Line Item</th> --}}
                                                    <th>Approver Name</th>
                                                    <th>Approver Level</th>
                                                    <th>Approval Status</th>
                                                    <th>Approve/Reject Date</th>
                                                    <th>Approver Note</th>
                                                </thead>
                                                <tbody>
                                                    @foreach($approvals as $key => $row)
                                                    <tr>
                                                        {{-- <td>{{ $row->piditem }}</td> --}}
                                                        <td>{{ $row->approver_name }}</td>
                                                        <td>{{ $row->approver_level }}</td>
                                                        @if($row->approval_status == "A")
                                                        <td style="text-align:center; background-color:green; color:white;">
                                                            Approved
                                                        </td>
                                                        @elseif($row->approval_status == "R")
                                                        <td style="text-align:center; background-color:red; color:white;">
                                                            Rejected
                                                        </td>
                                                        @else
                                                        <td style="text-align:center; background-color:yellow; color:black;">
                                                            Open
                                                        </td>
                                                        @endif

                                                        <td>
                                                            @if($row->approval_date != null)
                                                                <i class="fa fa-clock"></i>
                                                                ({{ formatDateTime($row->approval_date) }})
                                                            @endif
                                                        </td>
                                                        <td>{!! $row->approval_remark !!}</td>
                                                    </tr>
                                                    @endforeach
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
<div class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id="modalPreviewFile">
    <div class="modal-dialog modal-xl">
        <form class="form-horizontal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPreviewFileTitle">Preview Document</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="position-relative row form-group">
                    <div class="col-lg-12" id="fileViewer">
                        <!-- <div id="example1"></div> -->

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal"> Close</button>
                <a href="#" id="btnDownloadFile" class="btn btn-default btnDownloadFile" download="">
                    <i class="fa fa-download"></i> Download Document
                </a>
            </div>
        </div>
        </form>
    </div>
</div>

<div class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id="modalApprovalNote">
    <div class="modal-dialog modal-md">
        {{-- action="{{ url('approve/opnam/postapproval') }}" method="POST" --}}
        <form class="form-horizontal">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalApprovalTitle">Approval Note</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="position-relative row form-group">
                        {{-- <input type="text" name="action" class="form-control" placeholder="Action">
                        <input type="text" name="docid" class="form-control" placeholder="Doc ID">
                        <input type="text" name="pidnumber" class="form-control" placeholder="PID Number"> --}}
                        <div class="col-lg-12">
                            <textarea name="approver_note" id="approver_note" cols="30" rows="3" class="form-control" placeholder="Approval Note..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal" id="submit-approval"> OK</button>
                    {{-- <button type="submit" class="btn btn-primary"> Check</button> --}}
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('additional-js')
<script src="{{ asset('/assets/ckeditor/ckeditor.js') }}"></script>
<script src="{{ asset('/assets/ckeditor/adapters/jquery.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- <script src="https://cdn.scaleflex.it/plugins/filerobot-image-editor/3/filerobot-image-editor.min.js"></script> -->

<script type="text/javascript">
    function previewFile(files){
        // alert(base_url)
        var pathfile = base_url+'/'+files;
        if(files !== ""){
            $('#fileViewer').html('');
            $('#fileViewer').append(`
                <embed src="`+ pathfile +`" frameborder="0" width="100%" height="500px">

            `);

            var fileUri = pathfile;
            fileUri = fileUri.replace("#toolbar=0", "?force=true");

            document.getElementById("btnDownloadFile").href=fileUri;
            $('#modalPreviewFile').modal('show');
        } else{
            swal("File Not Found", "", "warning");
        }
    }

    $(document).ready(function () {
        let _token   = $('meta[name="csrf-token"]').attr('content');
        let _action  = null;

        // $('#tbl-pr-data').DataTable();
        // $("#tbl-pr-data").DataTable();
        new DataTable('#tbl-pr-data');

        $('#checkAll').click(function(){
            if(this.checked){
                $('.checkbox').each(function(){
                    this.checked = true;
                });
            }else{
                $('.checkbox').each(function(){
                    this.checked = false;
                });
            }
        });

        $('#btn-approve-items').on('click', function(){
            _action = 'A';
            $('#modalApprovalTitle').html('Approve Note');
            $('#modalApprovalNote').modal('show');
        });

        $('#btn-reject-items').on('click', function(){
            _action = 'R';
            $('#modalApprovalTitle').html('Reject Note');
            $('#modalApprovalNote').modal('show');
        });

        $('#submit-approval').on('click', function(){
            approveOrReject();
        });

        function approveOrReject(){
            var prtemchecked = {
                    "docid" : {{ $header->id }},
                    "nota_retur" : "{{ $header->nota_retur }}",
                    // "piditem" : _splchecked,
                    "action" : _action,
                    "_token": _token,
                    "approvernote":$('#approver_note').val(),
                }
                console.log(prtemchecked)
                $.ajax({
                    url:base_url+'/approve/retur/save',
                    method:'post',
                    data:prtemchecked,
                    dataType:'JSON',
                    beforeSend:function(){
                        $('#btn-approve-items').attr('disabled','disabled');
                        $('#btn-reject-items').attr('disabled','disabled');
                    },
                    success:function(data)
                    {

                    },
                    error:function(err){
                        console.log(err);
                        toastr.error(err)

                        // setTimeout(function(){
                        //     location.reload();
                        // }, 2000);
                    }
                }).done(function(response){
                    console.log(response);
                    // $('#btn-approve').attr('disabled',false);
                    console.log(response);
                    if(response.msgtype === "200"){
                        if(_action === "A"){
                            toastr.success(response.message)
                        }else if(_action === "R"){
                            toastr.success(response.message)
                        }

                        setTimeout(function(){
                            window.location.href = base_url+'/approve/retur';
                        }, 2000);
                    }else{
                        toastr.error(response.message)
                        setTimeout(function(){
                            location.reload();
                        }, 2000);
                    }
                });

        }
    });
</script>
@endsection
