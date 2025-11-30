<html>
    <head>
        <title>Tax Invoice</title>
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
                <h2 class="m-0 font-weight-bold text-primary" style="text-align:center;">Invoice</h2>
            </div>
            <div class="card py-3">
                @foreach($tax_invoice as $f)
                <table style="border:0px;" class="table table-borderless" >
                    <tbody>
                        <tr>
                            <td style="width: 50%; padding:0%;">
                                <div class="table-responsive" >
                                    <table style="border:0px;" class="table table-borderless table-sm">
                                            <tbody>
                                                <ul style="list-style-type:none;">
                                                    <li style="border:0px;">No. Invoice      : {{$f->no_inv}}</li>
                                                    <li style="border:0px;">Customer         : {{$f->customer->nama_cust}}</li>
                                                    <li style="border:0px;">Date             : {{\Carbon\Carbon::parse($f->tgl_inv)->format('Y-m-d')}}</li>
                                                    <li style="border:0px;">Payment Term     : {{$f->term}}</li>
                                                    <li style="border:0px;">No. SO           : {{$f->po->no_so}}</li>
                                                    <li style="border:0px;">No. PO           : {{$f->po->no_po}}</li>
                                                    <li style="border:0px;">No. NPWP         : {{$f->customer->npwp_cust}}</li>
                                                </ul>
                                            </tbody>
                                    </table>
                                </div>
                            </td>
                            <td style="width: 50%; padding:0%;">
                                <div class="table-responsive" >
                                    <table style="border:0px;" class="table table-borderless table-sm">
                                            <tbody>
                                                <strong>Kepada Yth, :</strong><br/>
                                                    <ul style="list-style-type:none;">
                                                        <li style="border:0px;">{{$f->customer->nama_cust}}</li>
                                                        <li style="border:0px;">{{$f->customer->address_cust}}</li>
                                                    </ul>
                                            </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <!-- <div class="col-md-9">
                        <p>No. Invoice      : {{$f->no_inv}}</p>
                        <p>Customer         : {{$f->customer->nama_cust}}</p>
                        <p>Date             : {{\Carbon\Carbon::parse($f->tgl_inv)->format('Y-m-d')}}</p>
                        <p>Payment Term     : {{$f->term}}</p>
                        <p>No. SO           : {{$f->po->no_so}}</p>
                        <p>No. PO           : {{$f->po->no_po}}</p>
                        <p>No. NPWP         : {{$f->no_seri_pajak}}</p>
                </div> -->
                @endforeach
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <!-- <th class="text-center">No. DO</th> -->
                                <th class="text-center">Kode Barang</th>
                                <th class="text-center">Nama Barang</th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Net Price</th>
                                <th class="text-center">Total Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($po_dtl)
                                @foreach($po_dtl as $f)
                                <tr>
                                    <td>{{$f->part_number}}</td>
                                    <td>{{$f->product_name}}</td>
                                    <td>{{$f->qty}}</td>
                                    <td>{{$f->price}}</td>
                                    <td>{{$f->total_price}}</td>
                                </tr>
                            </tbody>
                            @endforeach
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-right align-middle text-wrap">Grand Total : Rp {{$grand_total}}</td>
                                </tr>
                            </tfoot>
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
                                                <ul style="list-style-type:none; text-align:left;">
                                                    <li><strong>Hormat Kami:</strong></li>
                                                    <li>
                                                        @if($signed == '')
                                                        <strong style="font-size: 10px">Tertanda</strong>
                                                        @else
                                                        <img src="{{public_path($signed)}}" alt="sign" width="100px" height="150px">
                                                        @endif
                                                    </li>
                                                    <li>
                                                        <strong>YAKOB HENRY</strong>
                                                    </li>
                                                </ul>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                            <!-- <td style="width: 50%; padding:0%;">
                                <div class="table-responsive">
                                    <table style="border:0px;" class="table table-borderless table-sm" >
                                        <tbody>
                                            <tr>
                                                <th scope="row" style="width: 30%"><strong>Jakarta, {{$tgl}}</strong></th>
                                                <td>
                                                    FICT-ASSIGN-202207034356
                                                </td>
                    
                                            </tr>
                                            <tr>
                                                <th scope="row"><strong>Diterima Oleh</strong></th>
                                            </tr><br/><br/>
                                            <tr>
                                                <th scope="row"><strong>(.............)</strong></th>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div> 
                            </td> -->
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </body>
</html>