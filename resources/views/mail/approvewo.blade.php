<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Approve WO</title>

    <style>
        #customers {
        font-family: Arial, Helvetica, sans-serif;
        border-collapse: collapse;
        width: 100%;
        }

        #customers td, #customers th {
        border: 1px solid #ddd;
        padding: 8px;
        }

        #customers tr:nth-child(even){background-color: #f2f2f2;}

        #customers tr:hover {background-color: #ddd;}

        #customers th {
        padding-top: 12px;
        padding-bottom: 12px;
        text-align: left;
        background-color: #04AA6D;
        color: white;
        }

        a:link, a:visited {
            background-color: #f44336;
            color: white;
            padding: 14px 25px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        a:hover, a:active {
            background-color: rgb(0, 145, 255);
        }
    </style>
</head>
<body>
    <div class="table-responsive">
        <p>Mohon untuk Approve/Reject WO berikut :</p><br>
        <table id="customers" class="table table-bordered table-hover table-striped table-sm" style="width:100%;">
            <thead>
                <th>No</th>
                <th>Nomor WO</th>
                <th>Tanggal WO</th>
                <th>WO Desc</th>
                <th>No. Plat</th>
                <th>No. Checklist</th>
                <th>Material</th>
                <th>Description</th>
                <th>Quantity</th>
                <th>Created By</th>
            </thead>
            <tbody>
                @foreach ($data as $key => $row)
                <tr>
                    <td>{{ $key+1 }}</td>
                    <td>{{ $row->wonum }}</td>
                    <td>{{ $row->wodate }}</td>
                    <td>{{ $row->description }}</td>
                    <td>{{ $row->license_number }}</td>
                    <td>{{ $row->refdoc }}</td>
                    <td>{{ $row->material }}</td>
                    <td>{{ $row->matdesc }}</td>
                    <td style="text-align: right;">{{ number_format($row->quantity,0) }} {{ $row->unit }}</td>
                    <td>{{ $row->createdby }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <br>
        <a href="{{ url('approve/spk/detail') }}/{{ $woid }}" target="_blank" >
            Approve / Reject WO
        </a>
    </div>
</body>
</html>