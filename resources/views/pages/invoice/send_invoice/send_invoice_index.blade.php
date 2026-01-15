@extends('layouts.admin')

@section('content')                    
    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Send Invoice</h6>
        </div>
        <div class="card-header py-3">
            <a href="{{route('send_invoice.create')}}" class="btn btn-primary btn-flat btn-sm"><i class="fa fa-plus"></i> Tambah</a>
            <!-- <a href="{{route('send_invoice.bin')}}" class="btn btn-primary btn-flat btn-sm"><i class="fa fa-archive"></i> See Archive</a> -->
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th colspan="2" class="text-center">Customer</th>
                            <th rowspan="2" class="text-center align-middle">Invoice</th>
                            <th rowspan="2" class="text-center align-middle">Send Invoice Date</th>
                            <th colspan="2" rowspan="2" class="text-center align-middle">Metode Pengiriman</th>
                            <th rowspan="2" class="text-center align-middle">Bukti Terima</th>
                            <th rowspan="2" class="text-center align-middle">Resi Number / Receipt Number</th>
                            <th rowspan="2" class="text-center align-middle">Aksi</th>
                        </tr>
                        <tr>

                                <th class="text-center align-middle text-wrap">Kode</th>
                                <th class="text-center align-middle text-wrap">Nama</th>

                            </tr>
                    </thead>
                    <tbody>
                        <?php
                            $no=1;
                        ?>
                        @if(!$send_invoice)
                            <tr>
                                <td colspan="11" class="text-center align-middle text-wrap">No Data</td>
                            </tr>
                    @else
                            @foreach($send_invoice as $f)
                            <tr>
                                <!-- <td>{{$f->no_tti}}</td> -->
                                <td>{{$f->customer->code_cust ?? 'NA'}}</td>
                                <td>{{$f->customer->nama_cust ?? 'NA'}}</td>
                                <td>{{$f->no_inv}}</td>
                                <td>{{$f->created_at}}</td>
                                <td>{{$f->inv_rcp->shipping_via ?? 'NA'}}</td>
                                <td>{{$f->courier->nama_courier ?? 'NA'}}</td>
                                <td>
                                    <a href="{{$f->bukti_tanda_terima}}" target="__blank">
                                        <img src="{{$f->bukti_tanda_terima}}" width="50px" height="50px">
                                    </a>
                                </td>
                                {{-- @if($f->bukti_tanda_terima !='')
                                    <td>Ada</td>
                                @else
                                    <td>Tidak Ada</td>
                                @endif --}}
                                <td>{{$f->no_resi}}</td>
                                <td><a href="{{route('send_invoice.show',$f->id)}}" class="btn btn-flat btn-primary btn-sm"><i class="fa fa-eye"></i></a></td>
                                <td><a href="{{route('send_invoice.history',$f->id)}}" class="btn btn-flat btn-success btn-sm"><i class="fa fa-history"></i></a></td>
                                <td><a href="{{route('send_invoice.edit',$f->id)}}" class="btn btn-warning btn-flat btn-sm"><i class="fa fa-edit"></i></a></td>
                                <td>
                                    <form action="{{route('send_invoice.delete',$f->id)}}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-flat btn-danger show-alert-delete-box btn-sm" data-toggle="tooltip" title='Delete'><i class="fa fa-trash"></i></button></td>
                                    </form>
                                </td>
                            </tr>
                        </tbody>
                        @endforeach
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