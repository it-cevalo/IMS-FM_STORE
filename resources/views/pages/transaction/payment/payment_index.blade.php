@extends('layouts.admin')

@section('content')                    
    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Payment</h6>
        </div>
        <div class="card-header py-3">
            <a href="{{route('payment.create')}}" class="btn btn-primary btn-flat btn-sm"><i class="fa fa-plus"></i> Tambah</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th rowspan="2" class="text-center align-middle">No</th>
                            <th colspan="2" class="text-center">Customer</th>
                            <th colspan="4" class="text-center">Invoice</th>
                            <th rowspan="2" class="text-center align-middle">Term</th>
                            <th rowspan="2" class="text-center align-middle">Grand Total</th>
                            <th rowspan="2" class="text-center align-middle">Payment Via</th>
                            <th rowspan="2" class="text-center align-middle">Bank Account</th>
                            <th colspan="4" class="text-center">Paid</th>
                            <th rowspan="2" colspan="2" class="text-center align-middle">Aksi</th>
                        </tr>
                        <tr>

                                <th class="text-center align-middle text-wrap">Kode</th>
                                <th class="text-center align-middle text-wrap">Nama</th>
                                
                                <th class="text-center align-middle text-wrap">Tanggal</th>
                                <th class="text-center align-middle text-wrap">No</th>
                                <th class="text-center align-middle text-wrap">Status</th>
                                <th class="text-center align-middle text-wrap">Reason</th>

                                
                                <th class="text-center align-middle text-wrap">Invoice</th>
                                <th class="text-center align-middle text-wrap">Amount</th>
                                <th class="text-center align-middle text-wrap">Remaining</th>
                                <th class="text-center align-middle text-wrap">Status</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $no=1;
                        ?>
                        @if($payment->isEmpty())
                        <tr>
                            <td class="text-center align-middle text-wrap" colspan="22">No Data</td>
                        </tr>
                        @else
                        @foreach($payment as $f)
                        <tr>
                            <td>{{$no++}}</td>
                            <td>{{$f->tax_inv->code_cust ?? ''}}</td>
                            <td>{{$f->customer->nama_cust ?? ''}}</td>
                            <td>{{\Carbon\Carbon::parse($f->tax_inv->tgl_inv)->format('Y-m-d')}}</td>
                            <td>{{$f->no_tax_inv ?? ''}}</td>
                            <td>{{$f->tax_inv->status_faktur_pajak ?? ''}}</td>
                            <td>{{$f->tax_inv->reason_faktur_pajak ?? ''}}</td>
                            <td>{{$f->tax_inv->term ?? ''}}</td>
                            <td>{{$f->tax_inv->grand_total ?? ''}}</td>
                            <td>{{$f->payment_via ?? ''}}</td>
                            <td>{{$f->bank_account ?? ''}}</td>
                            <td>{{$f->invoice_paid ?? ''}}</td>
                            <td>{{$f->amount_paid ?? ''}}</td>
                            <td>{{$f->invoice_paid - $f->amount_paid ?? ''}}</td>
                            @if($f->invoice_paid > $f->amount_paid)
                            <td>Belum Lunas</td>
                            @elseif($f->invoice_paid == $f->amount_paid)
                            <td>Sudah Lunas</td>
                            @endif
                                @if($f->invoice_paid > $f->amount_paid)
                                    <td><a href="{{route('payment.history',$f->id)}}" class="btn btn-flat btn-success btn-sm"><i class="fa fa-history"></i></a></td> 
                                    <td><a href="{{route('payment.edit',$f->id)}}" class="btn btn-warning btn-flat btn-sm"><i class="fa fa-edit"></i></a></td>
                                @elseif($f->invoice_paid == $f->amount_paid)
                                    <td colspan="2"><a href="{{route('payment.history',$f->id)}}" class="btn btn-flat btn-success btn-sm"><i class="fa fa-history"></i></a></td> 
                                @endif
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