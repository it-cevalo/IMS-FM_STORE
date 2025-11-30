<html>
    <head>
        <title>Receipt Invoice</title>
        <!-- Start CSS Style -->
            <style>
            table, th, td {
                border: 1px solid black;
                border-collapse: collapse;
            }
            </style>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
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
       $tgl = date('d F Y', strtotime($tanggal));
    ?>         
    <div class="container-fluid">       
        <div class="card mb-4">
            <div class="card py-3">
                <img src="{{ public_path('assets/img/logo.png') }}" width="300px" height="70px"/>
                <h2 class="m-0 font-weight-bold text-primary" style="text-align:center;">Tanda Terima Invoice</h2>
            </div>
            <div class="card py-3">
                @foreach($receipt_invoice as $f)
                <div class="col-md-9">
                        <p>Diterima Oleh      : {{$f->customer->nama_cust}} </p>
                        <p>Invoice Total      : {{$grand_total}} (IDR/USD)</p>
                        <p>Nomor Tanda Terima : {{$f->no_tti}}</p>
                </div>
                @endforeach
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="text-center">Tanggal Invoice</th>
                                <th class="text-center">Nomor Invoice</th>
                                <th class="text-center">Jumlah IDR/USD</th>
                                <th class="text-center">Nomor Seri Pajak</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $no=1;
                            ?>
                            @if($receipt_invoice)
                                @foreach($receipt_invoice as $f)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($f->inv->tgl_inv)->format('Y-m-d')}}</td>
                                    <td>{{$f->inv->no_inv}}</td>
                                    <td>{{$grand_total}}</td>
                                    <td>{{$f->no_seri_pajak}}</td>
                                    <!-- <td>{{$f->term}}</td> -->
                                    <!-- <td>{{$f->no_tti}}</td> -->
                                    <!-- <td>{{$f->shipping_via}}</td>
                                    <td>{{$f->courier->nama_courier ?? 'NA'}}</td> -->
                                </tr>
                            </tbody>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="11" class="text-center align-middle text-wrap">No Data</td>
                            </tr>
                        @endif
                    </table>
                </div>
                <table style="border:0px;" class="table table-borderless" >
                    <tbody>
                        <tr>
                            <td style="width: 50%; padding:0%;">
                                <div class="table-responsive" >
                                    <table style="border:0px;" class="table table-borderless table-sm">
                                        <tbody>
                                            <tr>
                                                <th scope="row" style="width: 20%"><strong>Payment:</strong></th>
                                                <td>
                                                    <ul>
                                                        <li>BCA A/C : 408 306 6060</li>
                                                        <li>PANIN A/C : 0195 000 799</li>
                                                        <li>MANDIRI A/C : 168 0068 869 997</li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                            <td style="width: 50%; padding:0%;">
                                <div class="table-responsive">
                                    <table style="border:0px;" class="table table-borderless table-sm" >
                                        <tbody>
                                            <tr>
                                                <th scope="row" style="width: 30%"><strong>Jakarta, {{$tgl}}</strong></th>
                                                <!-- <td>
                                                    FICT-ASSIGN-202207034356
                                                </td> -->
                    
                                            </tr>
                                            <tr>
                                                <th scope="row"><strong>Diterima Oleh</strong></th>
                                                <!-- <td>
                                                </td> -->
                                            </tr><br/><br/>
                                            <tr>
                                                <th scope="row"><strong>(.............)</strong></th>
                                                <!-- <td>
                                                    1
                                                </td> -->
                                            </tr>
                                            <tr>
                                                @foreach($receipt_invoice as $p)
                                                <th style="text-align: right">{{$p->courier->nama_courier}}</th>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div> 
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </body>
</html>