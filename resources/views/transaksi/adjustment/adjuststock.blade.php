@extends('layouts/App')

@section('title', 'Upload Adjust Stock')

@section('additional-css')
@endsection

@section('content')
<div class="container-fluid">
<div class="row">
    <div class="col-lg-12 mt-2">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Upload Adjust Stock</h3>
                <div class="card-tools">
                    <a href="/excel/Template AdjOut Stock.xlsx" target="_blank" class="btn btn-primary btn-sm">
                        <i class="fa fa-download"></i> Download Template
                    </a>

                    <a href="/master/player" class="btn btn-primary btn-sm">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">


                <div class="row">
                    <div class="col-lg-12">
                        <form action="{{ url('/adjust/stockout') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label for="">Tanggal Upload</label>
                                            <input type="date" name="tglupload" class="form-control" required>
                                        </div>
                                        <div class="col-lg-12">
                                            <label for="">Ajustment Type</label>
                                            <select name="adjtype" class="form-control" required>
                                                <option value="">-----</option>
                                                <option value="IN">Adjustment In</option>
                                                <option value="OUT">Adjustment Out</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-12">
                                            <label for="">Keterangan</label>
                                            <textarea name="remark" id="" cols="30" rows="3" class="form-control"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-10">
                                    <label for="browse-file">File</label>
                                    <input type="file" name="file" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-sm" style="margin-top:27px; width:100%;">
                                        <i class="fa fa-folder-open"></i> Upload Data
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-footer">

            </div>
        </div>
    </div>
</div>
</div>
@endsection

@section('additional-js')
<script>

</script>
@endsection
