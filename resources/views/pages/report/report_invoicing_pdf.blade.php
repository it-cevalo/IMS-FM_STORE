<html>

<head>
    <title>Report Invoicing</title>
    <!-- Start CSS Style -->
    <style>
    table,
    th,
    td {
        border: 1px solid black;
        border-collapse: collapse;
    }
    </style>
    <!-- Custom fonts for this template-->
    <!-- <link href="{{asset('assets/vendor/fontawesome-free/css/all.min.css')}}" rel="stylesheet" type="text/css">
            <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
            Custom styles for this template
            <link href="{{asset('assets/css/sb-admin-2.min.css')}}" rel="stylesheet">    
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> -->
    <!-- End CSS Style -->

    <!-- Start JS -->
    <!-- Bootstrap core JavaScript
            <script src="{{asset('assets/vendor/jquery/jquery.min.js')}}"></script>
            <script src="{{asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>

            Core plugin JavaScript
            <script src="{{asset('assets/vendor/jquery-easing/jquery.easing.min.js')}}"></script>

            Custom scripts for all pages
            <script src="{{asset('assets/js/sb-admin-2.min.js')}}"></script>

            Page level plugins -->
    <!-- <script src="{{asset('assets/vendor/chart.js/Chart.min.js')}}"></script> -->

    <!-- Page level custom scripts -->
    <!-- <script src="{{asset('assets/js/demo/chart-area-demo.js')}}"></script>
            <script src="{{asset('assets/js/demo/chart-pie-demo.js')}}"></script>
            <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>    
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> -->
    <!-- End JS -->
</head>

<body>
    <?php 
       $tanggal = date('Y-m-d');
    ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <img src="{{ public_path('assets/img/logo_customer.png') }}" width="300px" height="70px" />
            <h2 class="m-0 font-weight-bold text-primary" style="text-align:center;">Report Invoicing Date :
                {{$tanggal}}</h2>
        </div>
        <div class="card-header py-3">
            <!-- <a href="{{route('tax_invoice.create')}}" class="btn btn-primary btn-flat btn-sm"><i class="fa fa-plus"></i> Tambah</a> -->
            <!-- <a href="{{route('tax_invoice.bin')}}" class="btn btn-primary btn-flat btn-sm"><i class="fa fa-archive"></i> See Archive</a> -->
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th rowspan="2" class="text-center align-middle">Number</th>
                            <th colspan="2" class="text-center">Customer</th>
                            <th colspan="2" class="text-center">Invoice</th>
                            <th rowspan="2" class="text-center align-middle">Grand Total</th>
                        </tr>
                        <tr>

                            <th class="text-center align-middle text-wrap">Kode</th>
                            <th class="text-center align-middle text-wrap">Nama</th>

                            <th class="text-center align-middle text-wrap">Tanggal</th>
                            <th class="text-center align-middle text-wrap">Kode</th>

                            <!-- <th class="text-center align-middle text-wrap">PO</th>
                        <th class="text-center align-middle text-wrap">DO</th>
                        <th class="text-center align-middle text-wrap">Invoice</th> -->

                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no=1;
                    ?>
                        @if($invoice)
                        @foreach($invoice as $f)
                        <tr>
                            <td>{{$no++}}</td>
                            <td>{{$f->code_cust ?? 'NA'}}</td>
                            <td>{{$f->nama_cust ?? 'NA'}}</td>
                            <td>{{ \Carbon\Carbon::parse($f->tgl_inv)->format('Y-m-d')}}</td>
                            <td>{{$f->no_inv ?? 'NA'}}</td>
                            <td>{{$f->grand_total ?? 'NA'}}</td>
                        </tr>
                    </tbody>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="14" class="text-center align-middle text-wrap">No Data</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</body>

</html>