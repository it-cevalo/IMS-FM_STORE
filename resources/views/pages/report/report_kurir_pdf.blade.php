<html>

<head>
    <title>Report Courier</title>
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
            <h2 class="m-0 font-weight-bold text-primary" style="text-align:center;">Report Courier Date : {{$tanggal}}
            </h2>
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
                            <th class="text-center align-middle" rowspan="2">No</th>
                            <th class="text-center align-middle" rowspan="2">Kode Kurir</th>
                            <th class="text-center align-middle" rowspan="2">Nama Kurir</th>
                            <th class="text-center align-middle" colspan="2">Tanggal</th>
                        </tr>
                        <tr>
                            <th class="text-center align-middle">Created</th>
                            <th class="text-center align-middle">Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $no=1;
                            ?>
                        @if($courier)
                        @foreach($courier as $f)
                        <tr>
                            <td>{{$no++}}</td>
                            <td>{{$f->code_courier}}</td>
                            <td>{{$f->nama_courier}}</td>
                            <td>{{$f->created_at}}</td>
                            <td>{{$f->updated_at}}</td>
                        </tr>
                    </tbody>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="5" class="text-center align-middle text-wrap">No Data</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</body>

</html>