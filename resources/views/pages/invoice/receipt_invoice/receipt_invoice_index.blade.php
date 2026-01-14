@extends('layouts.admin')

<!-- Start Style CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
<!-- End Style CSS -->

@section('content')                    
    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Receipt Invoice</h6>
        </div>
        <div class="card-header py-3">
            <a href="{{route('receipt_invoice.create')}}" class="btn btn-primary btn-flat btn-sm"><i class="fa fa-plus"></i> Tambah</a>
            <a href="{{route('receipt_invoice.bin')}}" class="btn btn-primary btn-flat btn-sm"><i class="fa fa-archive"></i> See Archive</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th rowspan="2" class="text-center align-middle">Number</th>
                            <th colspan="2" class="text-center">Customer</th>
                            <th colspan="2" class="text-center">Invoice</th>
                            <th rowspan="2" class="text-center align-middle">Tax Code</th>
                            <th rowspan="2" class="text-center align-middle">Term</th>
                            <th rowspan="2" class="text-center align-middle">Grand Total</th>
                            <th colspan="2" rowspan="2" class="text-center align-middle">Metode Pengiriman</th>
                            <th rowspan="2" colspan="5" class="text-center align-middle">Aksi</th>
                        </tr>
                        <tr>

                                <th class="text-center align-middle text-wrap">Code</th>
                                <th class="text-center align-middle text-wrap">Nama</th>
                                
                                <th class="text-center align-middle text-wrap">Tanggal</th>
                                <th class="text-center align-middle text-wrap">No</th>

                            </tr>
                    </thead>
                    <tbody>
                        <?php
                            $no=1;
                        ?>
                        @if($receipt_invoice)
                            @foreach($receipt_invoice as $f)
                            <tr>
                                <td>{{$f->no_tti}}</td>
                                <td>{{$f->customer->code_cust ?? 'NA'}}</td>
                                <td>{{$f->customer->nama_cust ?? 'NA'}}</td>
                                <td>{{ \Carbon\Carbon::parse($f->inv->tgl_inv)->format('Y-m-d')}}</td>
                                <td>{{$f->inv->no_inv}}</td>
                                <td>{{$f->no_seri_pajak}}</td>
                                <td>{{$f->term}}</td>
                                <td>{{$f->grand_total}}</td>
                                <td>{{$f->shipping_via}}</td>
                                <td>{{$f->courier->nama_courier ?? 'NA'}}</td>
                                @if(Auth::user()->position=='SUPERADMIN' || Auth::user()->position=='INVOICINGTAX')
                                    <td><a href="{{route('receipt_invoice.show',$f->id)}}" class="btn btn-flat btn-primary btn-sm"><i class="fa fa-eye"></i></a></td>
                                    <td><a href="{{route('receipt_invoice.history',$f->id)}}" class="btn btn-flat btn-success btn-sm"><i class="fa fa-history"></i></a></td> 
                                    <td><a href="{{route('receipt_invoice.pdf',$f->id)}}" class="btn btn-flat btn-info btn-sm"><i class="fa fa-file-pdf-o"></i></a></td> 
                                    <td><a href="{{route('receipt_invoice.edit',$f->id)}}" class="btn btn-warning btn-flat btn-sm"><i class="fa fa-edit"></i></a></td>
                                    <td>
                                        <form action="{{route('receipt_invoice.delete',$f->id)}}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-flat btn-danger show-alert-delete-box btn-sm" data-toggle="tooltip" title='Delete'><i class="fa fa-trash"></i></button></td>
                                        </form>
                                    </td>
                                @else
                                @endif
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
        </div>
    </div>
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