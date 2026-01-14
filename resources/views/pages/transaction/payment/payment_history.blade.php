@extends('layouts.admin')

@section('content')                    
    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Payment History</h6>
        </div>
        <div class="card-header py-3">
            <!-- <a href="{{route('payment.create')}}" class="btn btn-primary btn-flat btn-sm"><i class="fa fa-plus"></i> Tambah</a> -->
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th rowspan="2" class="text-center align-middle">No</th>
                            <th colspan="2" class="text-center align-middle">Payment</th>
                            <th colspan="2" class="text-center">Paid</th>
                            <!-- <th rowspan="2" colspan="2" class="text-center align-middle">Aksi</th> -->
                        </tr>
                        <tr>                                
                                <th class="text-center align-middle text-wrap">Customer</th>
                                <th class="text-center align-middle text-wrap">Tanggal</th>

                                
                                <th class="text-center align-middle text-wrap">Invoice</th>
                                <th class="text-center align-middle text-wrap">Amount</th>

                            </tr>
                    </thead>
                    <tbody>
                        <?php
                            $no=1;
                        ?>
                        @if($payment_his->isEmpty())
                        <tr>
                            <td class="text-center align-middle text-wrap" colspan="22">No Data</td>
                        </tr>
                        @else
                        @foreach($payment_his as $f)
                        <tr>
                            <td>{{$no++}}</td>
                            <td>{{$f->payment->code_cust}}</td>
                            <td>{{\Carbon\Carbon::parse($f->payment_date)->format('Y-m-d')}}</td>
                            <td>{{$f->invoice_paid}}</td>
                            <td>{{$f->amount_paid}}</td>
                        </tr>
                    </tbody>
                    @endforeach
                    @endif
                </table>
            </div>
        </div>
    </div>
@endsection