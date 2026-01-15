@extends('layouts.admin')

@section('content')                    
                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                Receipt Invoice History
                            </h6>
                        </div>
                        <div class="card-header py-3">
                            {{-- <div class="input-group">
                                <form method="" action="">
                                    <label>
                                        <input type="search" class="form-control form-control-sm" placeholder="Search" aria-controls="dataTable">
                                    </label>
                                    <button class="btn btn-primary btn-sm" type="button">
                                        <i class="fas fa-search fa-sm"></i>
                                    </button>
                                </form>
                            </div> --}}
                            <a href="{{route('receipt_invoice.index')}}" class="btn btn-primary">                                        
                                <i class="fa fa-arrow-left"> Kembali</i>
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th colspan="2" class="text-center">Courier</th>
                                        <th colspan="2" class="text-center">Receipt</th>
                                        <th rowspan="2" class="text-center align-middle">Metode Pengiriman</th>
                                        <th rowspan="2" class="text-center align-middle">Grand Total</th>
                                        <th rowspan="2" class="text-center align-middle">Term</th>
                                        <th rowspan="2" class="text-center align-middle">Update Date</th>
                                    </tr>
                                    <tr>
                                        <th class="text-center">Kode</th>
                                        <th class="text-center">Nama</th>
                                        <th class="text-center">Number</th>
                                        <th class="text-center">Tanggal</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($receipt_invoice_his as $receipt_invoice_his)
                                    <tr>
                                        <td>{{$receipt_invoice_his->code_courier}}</td>
                                        <td>{{$receipt_invoice_his->courier->nama_courier}}</td>
                                        <td>{{$receipt_invoice_his->no_tti}}</td>
                                        <td>{{\Carbon\Carbon::parse($receipt_invoice_his->created_at)->format('Y-m-d')}}</td>
                                        <td>{{$receipt_invoice_his->shipping_via}}</td>
                                        <td>{{$receipt_invoice_his->grand_total}}</td>
                                        <td>{{$receipt_invoice_his->term}}</td>
                                        <td>{{$receipt_invoice_his->updated_at}}</td>
                                    </tr>
                                </tbody>
                                @endforeach
                            </table>
                            </div>
                        </div>
                    </div>
                    @endsection