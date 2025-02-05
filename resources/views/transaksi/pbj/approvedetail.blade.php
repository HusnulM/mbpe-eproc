@extends('layouts/App')

@section('title', 'Document Approval')

@section('additional-css')
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
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-3">
            <form action="{{ url('transaction/document/save') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Approve PBJ <b>[ {{ $pbjhdr->pbjnumber }} ]</b></h3>
                        <div class="card-tools">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <table class="table table-sm">
                                    <tr>
                                        <td>
                                            <label for="doctitle">PBJ Number</label>
                                            <input type="hidden" id="pbjNumber" value="{{ $pbjhdr->pbjnumber }}">
                                        </td>
                                        <td>
                                            <p>{{ $pbjhdr->pbjnumber }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <label>Tanggal PBJ</label>
                                        </td>
                                        <td>
                                            {{ formatDate($pbjhdr->tgl_pbj) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <label>Nomor Checklist</label>
                                        </td>
                                        <td>
                                            {{ $pbjhdr->cheklistnumber }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td><label>Tujuan Permintaan</label></td>
                                        <td>{{ $pbjhdr->tujuan_permintaan }}</td>
                                    </tr>
                                    <tr>
                                        <td><label>Kepada</label></td>
                                        <td>{{ $pbjhdr->kepada }}</td>
                                    </tr>
                                    <tr>
                                        <td><label>Unit Desc / Code</label></td>
                                        <td>{{ $pbjhdr->unit_desc }}</td>
                                    </tr>
                                    <tr>
                                        <td><label>Engine Model</label></td>
                                        <td>{{ $pbjhdr->engine_model }}</td>
                                    </tr>
                                    <tr>
                                        <td><label>Chassis S/N</label></td>
                                        <td>{{ $pbjhdr->chassis_sn }}</td>
                                    </tr>
                                    <tr>
                                        <td><label>Refrensi</label></td>
                                        <td>{{ $pbjhdr->reference }}</td>
                                    </tr>
                                    <tr>
                                        <td><label>Requestor</label></td>
                                        <td>{{ $pbjhdr->requestor }}</td>
                                    </tr>
                                    <tr>
                                        <td><label>Type / Model</label></td>
                                        <td>{{ $pbjhdr->type_model }}</td>
                                    </tr>
                                    <tr>
                                        <td><label>User</label></td>
                                        <td>{{ $pbjhdr->user }}</td>
                                    </tr>
                                    <tr>
                                        <td><label>Kode Barang / Jasa</label></td>
                                        <td>{{ $pbjhdr->kode_brg_jasa }}</td>
                                    </tr>
                                    <tr>
                                        <td><label>Engine S/N</label></td>
                                        <td>{{ $pbjhdr->engine_sn }}</td>
                                    </tr>
                                    {{-- <tr>
                                        <td><label>Cost Code</label></td>
                                        <td>{{ $pbjhdr->budget_cost_code }}</td>
                                    </tr>
                                    <tr>
                                        <td><label>Periode</label></td>
                                        <td>{{ $pbjhdr->periode }}</td>
                                    </tr> --}}
                                    <tr>
                                        <td><label>Project</label></td>
                                        <td>{{ $project->kode_project ?? '' }} - {{ $project->nama_project ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td><label>Created By</label></td>
                                        <td>{{$pbjhdr->createdby}}</td>
                                    </tr>
                                    <tr>
                                        <td><label>Created Date</label></td>
                                        <td>{!! formatDateTime($pbjhdr->createdon) !!}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <textarea class="form-control" cols="30" rows="3" readonly>{!! $pbjhdr->remark !!}</textarea>
                                        </td>
                                    </tr>
                                </table>
                                {{-- <div class="form-group">
                                    <label for="doctitle">PBJ Number</label>
                                    <p>{{ $pbjhdr->pbjnumber }}</p>
                                    <input type="hidden" id="pbjNumber" value="{{ $pbjhdr->pbjnumber }}">
                                </div>
                                <div class="form-group">
                                    <label>Checklist No:</label> {{$pbjhdr->cheklistnumber}}
                                </div>
                                <div class="form-group">
                                    <label>Project:</label> {{ $project->kode_project ?? '' }} - {{ $project->nama_project ?? '' }}
                                </div>
                                <div class="form-group">
                                    <label>Created By:</label> {{$pbjhdr->createdby}}
                                </div>
                                <div class="form-group">
                                    <label>Created Date:</label>
                                    <p>{!! formatDateTime($pbjhdr->createdon) !!}
                                    </p>
                                </div> --}}
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
                                <a class="nav-link active" id="custom-content-above-home-tab" data-toggle="pill" href="#custom-content-above-home" role="tab" aria-controls="custom-content-above-home" aria-selected="true">PBJ Items</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-content-above-approval-tab" data-toggle="pill" href="#custom-content-above-approval" role="tab" aria-controls="custom-content-above-approval" aria-selected="false">Approval Status</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-content-above-attachment-tab" data-toggle="pill" href="#custom-content-above-attachment" role="tab" aria-controls="custom-content-above-attachment" aria-selected="false">Attachment</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-tools">

                        <a href="{{ url('/approve/pbj') }}" class="btn btn-default btn-sm">
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
                                            <table id="tbl-pbj" class="table table-bordered table-hover table-striped table-sm">
                                                <thead>
                                                    {{-- <th>No</th> --}}
                                                    <th style="text-align:center;">
                                                        <input type="checkbox" id="checkAll" class="filled-in" />
                                                        <label for="checkAll"></label>
                                                    </th>
                                                    <th>PBJ Item</th>
                                                    <th>Part Number</th>
                                                    <th>Description</th>
                                                    <th style="text-align:center;">Quantity</th>
                                                    <th>Unit</th>
                                                    <th>Figure</th>
                                                    <th>Remark</th>
                                                    <th>Kode Budget</th>
                                                </thead>
                                                <tbody>
                                                @foreach($pbjitem as $key => $row)
                                                    <tr>
                                                        {{-- <td>{{ $key+1 }}</td> --}}
                                                        {{-- {{ $row->approval_status == 'A' ? 'background-color:green' : '' }} --}}
                                                        <td style="text-align:center;">
                                                            @if($row->approval_status !== "A" && $row->approval_status !== "R")
                                                            <input class="filled-in checkbox" type="checkbox" id="{{ $row->pbjitem }}" name="ID[]">
                                                            <label for="{{ $row->pbjitem }}"></label>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            {{ $row->pbjitem }}
                                                        </td>
                                                        <td>
                                                            {{ $row->partnumber }}
                                                        </td>
                                                        <td>
                                                            {{ $row->description }}
                                                        </td>
                                                        <td style="text-align:right;">
                                                            {{ number_format($row->quantity,3) }}
                                                        </td>
                                                        <td>
                                                            {{ $row->unit }}
                                                        </td>
                                                        <td>
                                                            {{ $row->figure }}
                                                        </td>
                                                        <td>
                                                            {{ $row->remark }}
                                                        </td>
                                                        <td>
                                                            {{ $row->budget_code }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="9" style="text-align: right;">
                                                            <button type="button" class="btn btn-success pull-right ml-1 btn-sm" id="btn-approve-items">
                                                                <i class="fa fa-check"></i> APPROVE
                                                            </button>

                                                            <button type="button" class="btn btn-danger pull-right btn-sm" id="btn-reject-items">
                                                                <i class="fa fa-xmark"></i> REJECT
                                                            </button>

                                                        </td>
                                                    </tr>
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
                                                    <th>Approver Name</th>
                                                    <th>Approver Level</th>
                                                    <th>PBJ Item</th>
                                                    <th>Approval Status</th>
                                                    <th>Approve/Reject Date</th>
                                                    <th>Approver Note</th>
                                                </thead>
                                                <tbody>
                                                    @foreach($approvals as $key => $row)
                                                    <tr>
                                                        <td>{{ $row->approver_name }}</td>
                                                        <td>{{ $row->approver_level }}</td>
                                                        <td>{{ $row->pbjitem }}</td>
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
                                                                {{-- {{\Carbon\Carbon::parse($row->approval_date)->diffForHumans()}} <br> --}}
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

                                <div class="tab-pane fade" id="custom-content-above-attachment" role="tabpanel" aria-labelledby="custom-content-above-attachment-tab">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <table class="table table-sm">
                                                <thead>
                                                    <th>No</th>
                                                    <th>File Name</th>
                                                    <th>Upload Date</th>
                                                    <th></th>
                                                </thead>
                                                <tbody>
                                                @foreach($attachments as $key => $file)
                                                    <tr>
                                                        <td>{{ $key+1 }}</td>
                                                        <td>
                                                            {{ $file->efile }}
                                                        </td>
                                                        <td>
                                                            <i class="fa fa-clock"></i> {!! formatDateTime($file->createdon) !!}
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-default" onclick="previewFile('files/PBJ/{{$file->efile}}#toolbar=0')">
                                                                <i class="fa fa-search"></i> Preview File
                                                            </button>
                                                        </td>
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
        <form class="form-horizontal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalApprovalTitle">Approval Note</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="position-relative row form-group">
                    <div class="col-lg-12">
                        <textarea name="approver_note" id="approver_note" cols="30" rows="3" class="form-control" placeholder="Approval Note..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="submit-approval"> OK</button>
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
            var tableControl= document.getElementById('tbl-pbj');
            var _splchecked = [];
            _action = 'A';
            $('input[name="ID[]"]:checkbox:checked', tableControl).each(function() {
                _splchecked.push($(this).parent().next().text())
            }).get();
            if(_splchecked.length > 0){
                _action = 'A';
                $('#modalApprovalTitle').html('Approve Note');
                $('#modalApprovalNote').modal('show');

            }else{
                alert('No record selected ');
            }
        });

        $('#btn-reject-items').on('click', function(){
            var tableControl= document.getElementById('tbl-pbj');
            var _splchecked = [];
            _action = 'R';
            $('input[name="ID[]"]:checkbox:checked', tableControl).each(function() {
                _splchecked.push($(this).parent().next().text())
            }).get();
            if(_splchecked.length > 0){
                console.log(_splchecked)
                _action = 'R';
                $('#modalApprovalTitle').html('Reject Note');
                $('#modalApprovalNote').modal('show');
            }else{
                alert('No record selected ');
            }
        });

        $('#submit-approval').on('click', function(){
            approvePBJ();
        });

        function approvePBJ(){
            var tableControl= document.getElementById('tbl-pbj');
            var _splchecked = [];

            $('input[name="ID[]"]:checkbox:checked', tableControl).each(function() {
                _splchecked.push($(this).parent().next().text())
            }).get();

            // console.log(_splchecked)
            var prtemchecked = {
                "pbjitem" : _splchecked,
                "approvernote": $('#approver_note').val(),
                "action"  : _action,
                "_token"  : _token
            }
            console.log(prtemchecked);
            $.ajax({
                url:base_url+'/approve/pbj/approveitems/{{ $pbjhdr->id }}',
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

                    setTimeout(function(){
                        location.reload();
                    }, 2000);
                }
            }).done(function(response){
                console.log(response);
                console.log(response);
                if(response.msgtype === "200"){
                    if(_action === "A"){
                        toastr.success(response.message)
                    }else if(_action === "R"){
                        toastr.success(response.message)
                    }

                    setTimeout(function(){
                        window.location.href = base_url+'/approve/pbj';
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
