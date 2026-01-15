@extends('layouts.admin')

<!-- Start Style CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
<!-- End Style CSS -->

@section('content')                    
    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><a href="{{route('report_payment.index')}}">Report Payment</a></h6>
        </div>
        <div class="card-header py-3">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
                <i class="fa fa-filter"></i> Filter Report
            </button>
            {{-- <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal2">
                <i class="fa fa-file-export"></i> Export Report
            </button> --}}
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th colspan="2" class="text-center">Customer</th>
                            <th colspan="2" class="text-center">Invoice</th>
                            <th colspan="7" class="text-center">Payment</th>
                            <th rowspan="2" colspan="2" class="text-center align-middle">Aksi</th>
                        </tr>
                        <tr>

                            <th class="text-center align-middle text-wrap">Kode</th>
                            <th class="text-center align-middle text-wrap">Nama</th>
                            
                            <th class="text-center align-middle text-wrap">Tanggal</th>
                            <th class="text-center align-middle text-wrap">Kode</th>
                            
                            <th class="text-center align-middle text-wrap">Payment Via</th>
                            <th class="text-center align-middle text-wrap">Bank Account</th>
                            <th class="text-center align-middle text-wrap">Term</th>
                            <th class="text-center align-middle text-wrap">Invoice Paid</th>
                            <th class="text-center align-middle text-wrap">Amount Paid</th>
                            <th class="text-center align-middle text-wrap">Remaining Payment</th>
                            <th class="text-center align-middle text-wrap">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payment as $f)
                        <tr>
                            <td>{{$f->code_cust ?? 'NA'}}</td>
                            <td>{{$f->nama_cust ?? 'NA'}}</td>
                            <td>{{ \Carbon\Carbon::parse($f->tgl_inv)->format('Y-m-d')}}</td>
                            <td>{{$f->no_inv}}</td>
                            <td>{{$f->payment_via}}</td>
                            <td>{{$f->bank_account ?? 'NA' }}</td>
                            <td>{{$f->term ?? 'NA' }}</td>
                            <td>{{$f->invoice_paid ?? 'NA' }}</td>
                            <td>{{$f->amount_paid ?? 'NA' }}</td>
                            <td>{{$f->invoice_paid - $f->amount_paid ?? 'NA' }}</td>
                                @if($f->invoice_paid > $f->amount_paid)
                                <td>Belum Lunas</td>
                                @elseif($f->invoice_paid == $f->amount_paid)
                                <td>Sudah Lunas</td>
                                @endif
                            <td><a href="{{route('payment.history',$f->id)}}" class="btn btn-flat btn-success btn-sm"><i class="fa fa-history"></i></a></td> 
                            <td><a href="{{route('report_payment.hispdf',$f->id)}}" class="btn btn-flat btn-info btn-sm"><i class="fa fa-file-pdf-o"></i></a></td> 
                        </tr>
                    </tbody>
                    @endforeach
                </table>
            </div>
        </div>
    </div>

    
    <!-- Start Modal Filter -->
        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"><i class="fa fa-filter"></i> Filter Report</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form  method="GET" action="{{route('report_payment.filter')}}">
                            <div class="mb-3">
                                <label for="exampleFormControlInput1">Filter / Export</label>
                                    <div class="input-group">
                                            <select class="form-control" name="opt" value="{{old('opt')}}" required>
                                                <option value="">All</option>
                                                <option value="filter">Saring</option>
                                                <option value="export">Export to PDF</option>
                                            </select>
                                    </div> 
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1">Customer</label>
                                    <div class="input-group">
                                            <select class="form-control" name="id_cust" value="{{old('id_cust')}}">
                                                <option value="">All</option>
                                                @foreach($customer as $p)
                                                <option value="{{$p->id}}">{{$p->code_cust}}/{{$p->nama_cust}}</option>
                                                @endforeach
                                            </select>
                                    </div> 
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1">Status</label>
                                <div class="input-group">
                                        <select class="form-control" name="status">
                                            <option value="">All</option>
                                            <option value="Lunas">Lunas</option>
                                            <option value="Belum">Belum Lunas</option>
                                        </select>
                                </div> 
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1">From Date</label>
                                <div class="input-group">                
                                    <input class="form-control" id="exampleFormControlInput1" name="fd" type="date" required>
                                </div> 
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1">To Date</label>
                                <div class="input-group">                
                                    <input class="form-control" id="exampleFormControlInput1" name="td" type="date" required>
                                </div> 
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Execute</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    <!-- End Modal Filter -->

    <!-- Start Modal Export -->
        {{-- <div class="modal fade" id="exampleModal2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"><i class="fa fa-file-export"></i> Export Report</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{route('report_payment.pdf')}}" method="GET">
                            <div class="mb-3">
                                <label for="exampleFormControlInput1">Customer</label>
                                    <div class="input-group">
                                            <select class="form-control" name="id_cust" value="{{old('id_cust')}}">
                                                <option value="">All</option>
                                                @foreach($customer as $p)
                                                <option value="{{$p->id}}">{{$p->code_cust}}/{{$p->nama_cust}}</option>
                                                @endforeach
                                            </select>
                                    </div> 
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1">Status</label>
                                    <div class="input-group">
                                            <select class="form-control" name="status">
                                                <option value="">All</option>
                                                <option value="Lunas">Lunas</option>
                                                <option value="Belum">Belum Lunas</option>
                                            </select>
                                    </div> 
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1">From Date</label>
                                    <div class="input-group">                
                                        <input class="form-control" id="exampleFormControlInput1" name="fd" type="date" required>
                                    </div> 
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1">To Date</label>
                                    <div class="input-group">                
                                        <input class="form-control" id="exampleFormControlInput1" name="td" type="date" required>
                                    </div> 
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Export to PDF</button>
                    </div>
                    </form>
                </div>
            </div>
        </div> --}}
    <!-- End Modal Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script type="text/javascript">
        $('.show-alert-delete-box').click(function(event){
            var form =  $(this).closest("form");
            var name = $(this).data("name");
            event.preventDefault();
            swal({
                title: "Are you sure you want to delete this record?",
                text: "If you delete this, it will be go to archive.",
                icon: "warning",
                type: "warning",
                buttons: ["Cancel","Yes!"],
                confirmButtonColor: '#0000FF',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((willDelete) => {
                if (willDelete) {
                    form.submit();
                }
            });
        });
    </script>
@endsection