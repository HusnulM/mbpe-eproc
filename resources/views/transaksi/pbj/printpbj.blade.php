<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Print PBJ</title>
	<style>
        .customers {
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 100%;
            font-size:12px;
            font-weight:bold;
            margin-bottom:5px;
        }

        .customers td, .customers th {
            /* border: 1px solid #000; */
            /* padding: 5px; */
        }

        /* .customers tr:nth-child(even){background-color: #f2f2f2;}

        .customers tr:hover {background-color: #ddd;} */

        .customers th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            color: black;
        }

        #items {
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 100%;
            font-size:10px;
        }

        #items td, #items th {
            border: 1px solid #000;
            padding: 1px;
        }

        /* #items tr:nth-child(even){background-color: #f2f2f2;} */

        /* #items tr:hover {background-color: #ddd;} */

        #items th {
            /* padding-top: 12px;
            padding-bottom: 12px; */
            text-align: left;
            background-color: #B4B1B1;
            color: black;
        }

        .tbl-footer {
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 100%;
            font-size:10px;
        }

        .tbl-footer td, .tbl-footer th {
            /* border: 1px solid #000; */
            padding: 1px;
        }

        /* #items tr:nth-child(even){background-color: #f2f2f2;} */

        /* #items tr:hover {background-color: #ddd;} */

        .tbl-footer th {
            /* padding-top: 12px;
            padding-bottom: 12px; */
            text-align: left;
            background-color: #B4B1B1;
            color: black;
        }

        #container{width:100%;}
        #left{float:left;width:100px;}
        #right{float:right;width:100px;}
        #center{margin:0 auto;width:100px;}

        #header,
        #footer {
        position: fixed;
        left: 0;
            right: 0;
            color: #aaa;
            font-size: 0.9em;
        }
        #header {
        top: 0;
            border-bottom: 0.1pt solid #aaa;
        }
        #footer {
        bottom: 0;
        border-top: 0.1pt solid #aaa;
        }
        .page-number:before {
        content: "Page " counter(page);
        }
    </style>
</head>
<body>
    <!-- <h2 style="text-align:center; font-family: Arial, Helvetica, sans-serif;">PERMINTAAN BARANG / JASA</h2> -->
    <table border="0" cellspacing="0" cellpadding="0" id="items">
        <tr>
            <td style="text-align:center; width:130px;" rowspan="3">
                <!-- LOGO -->
                <img src="{{ asset(getCompanyLogo()) }}" class="img-thumbnail" alt="E-Logo" style="width:90px; height:60px;">
                <!-- <img src="{{ public_path('/assets/img/logo.png') }}" class="img-thumbnail" alt="E-Logo" style="width:90px; height:60px;"> -->
            </td>
            <td style="text-align:center; width:303px;" rowspan="3">
                <h2>
                PERMINTAAN BARANG / JASA<br>
                PBJ
                </h2>
            </td>
            <td style="width:72.5px;">
                PBJ No.
            </td>
            <td>
                {{ $hdr->pbjnumber }}
            </td>
        </tr>
        <tr>
            <td>
                PBJ Date
            </td>
            <td>
                {{ formatDate($hdr->tgl_pbj) }}
            </td>
        </tr>
        <tr>
            <td>Project</td>
            <td>{{ $project->nama_project ?? '' }}</td>
        </tr>
    </table>

    <table border="0" cellspacing="0" cellpadding="0" id="items" style="margin-top:5px;">
        <tr>
            <td style="width:130px;">Tujuan Permintaan</td>
            <td style="width:303px;">{{ $hdr->tujuan_permintaan }}</td>
            <td style="width:100px;">Kepada</td>
            <td>{{ $hdr->kepada }}</td>
        </tr>
        <tr>
            <td style="width:130px;">Ref. Permintaan</td>
            <td>{{ $hdr->reference }}</td>
            <td style="width:100px;">Requestor</td>
            <td>{{ $hdr->requestor }}</td>
        </tr>
    </table>
    <table id="items" style="border-top: #fff 2px solid;">
        <tr>
            <td style="width:130px;">Unit Desc/Code</td>
            <td style="width:120px;">{{ $hdr->unit_desc }}</td>
            <td style="width:70px;">Type/Model</td>
            <td style="width:107px;">{{ $hdr->type_model }}</td>
            <td style="width:100px;">User</td>
            <td>{{ $hdr->user }}</td>
        </tr>

        <tr>
            <td style="width:130px;">Engine Model</td>
            <td style="width:120px;">{{ $hdr->engine_model }}</td>
            <td style="width:70px;">Chassis S/N</td>
            <td style="width:95px;">{{ $hdr->chassis_sn }}</td>
            <td style="width:100px;">Kode Barang/Jasa</td>
            <td>{{ $hdr->kode_brg_jasa }}</td>
        </tr>

        <tr>
            <td style="width:130px;">Engine S/N</td>
            <td style="width:120px;">{{ $hdr->engine_sn }}</td>
            <td style="width:70px;">HM / KM</td>
            <td style="width:95px;">{{ $hdr->hm_km }}</td>
            <td style="width:100px;">Budget/Cost Code</td>
            <td>
                @if($item[0]->budget_code == '0')
                NONBUDGET
                @else
                {{ $item[0]->budget_code ?? 'NONBUDGET' }}
                @endif
                {{-- @if($item[0]->budget_code === '1' || $hdr->budget_cost_code === 'BUDGET')
                    Budget
                @else
                    Non-Budget
                @endif --}}
                {{-- @if($hdr->budget_cost_code === '1' || $hdr->budget_cost_code === 'BUDGET')
                    Budget
                @else
                    Non-Budget
                @endif --}}
            </td>
        </tr>
    </table>

    <table border="0" cellspacing="0" cellpadding="0" id="items" style="margin-top:5px;">
        <thead>
            <th style="width:20px;">No</th>
            <th style="width:100px;">Part No. / Type</th>
            <th>Description</th>
            <th style="width:80px;">Quantity</th>
            <th style="width:50px;">Unit</th>
            <th>Figure</th>
            <th>Remark</th>
        </thead>
        <tbody>
            @foreach($item as $key => $row)
            <tr>
                <td>{{ $key+1 }}</td>
                <td>{{ $row->partnumber }}</td>
                <td>{{ $row->description }}</td>
                <td style="text-align:right;">{{ number_format($row->quantity, 3) }}</td>
                <td>{{ $row->unit }}</td>
                <td>{{ $row->figure }}</td>
                <td>{{ $row->remark }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div id="container">
        <div id="left" style="width:400px;" class="tbl-footer">
            <table width="100%;">
                <tr>
                    <td style="text-align:center;">{{ $firstApprover->jabatan ?? '' }},</td>
                    <td></td>
                    <td style="text-align:center;">{{ $secondApprover->jabatan ?? '' }},</td>
                    <td></td>
                    <td style="text-align:center;">
                        @if($thirdApprover)
                        {{ $thirdApprover->jabatan ?? '' }},
                        @endif
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td style="text-align: center;">
                        @if($hdr->pbj_status == 'A' || $hdr->pbj_status == 'C')
                        <img src="{{ asset($firstApprover->s_signfile ?? '') }}" class="img-thumbnail" alt="E-sign" style="width:100px; height:100px;">
                        @endif
                    </td>
                    <td></td>
                    <td style="text-align: center;">
                        @if($secondApprover)
                        @if($hdr->pbj_status == 'A' || $hdr->pbj_status == 'C')
                        <img src="{{ asset($secondApprover->s_signfile ?? '') }}" class="img-thumbnail" alt="E-sign" style="width:100px; height:100px;">
                        @endif
                        @endif
                    </td>
                    <td></td>
                    <td style="text-align: center;">
                        @if($thirdApprover)
                        @if($hdr->pbj_status == 'A' || $hdr->pbj_status == 'C')
                        <img src="{{ asset($thirdApprover->s_signfile ?? '') }}" class="img-thumbnail" alt="E-sign" style="width:100px; height:100px;">
                        @endif
                        @endif
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td>
                        <hr>
                    </td>
                    <td></td>
                    <td>
                        @if($secondApprover)
                        <hr>
                        @endif
                    </td>
                    <td></td>
                    <td>@if($thirdApprover)
                        <hr>@endif
                    </td>
                </tr>
                <tr>
                    <td style="text-align:center;">
                        {{ $firstApprover->name ?? '' }} <br>
                        Date: {{ formatDate($firstApprovalDate->approval_date ?? null) }}
                    </td>
                    <td></td>
                    <td style="text-align:center;">
                        @if($secondApprover)
                        {{ $secondApprover->name ?? '' }} <br>
                        Date: {{ formatDate($secondApprovalDate->approval_date ?? null) }}
                        @endif
                    </td>
                    <td></td>
                    <td style="text-align:center;">
                        @if($thirdApprover)
                        {{ $thirdApprover->name ?? '' }} <br>
                        Date: {{ formatDate($thirdApprovalDate->approval_date ?? null) }}
                        @endif
                    </td>
                    <td></td>
                </tr>
                <tfoot>
                    <tr>
                        <td>
                            F LOG-01 Rev.00
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div id="right" style="width:150px;" class="tbl-footer">
            Note:
            <div style="width:100%; height:60px; border-style: inset;">
                {{ $hdr->remark }}
            </div>
        </div>
    </div>
    <!-- <br> -->

    <script type="text/php">
        if (isset($pdf)) {
            $text = "page {PAGE_NUM} of {PAGE_COUNT}";
            $size = 10;
            $font = $fontMetrics->getFont("Verdana");
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 35;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>
</body>
</html>
