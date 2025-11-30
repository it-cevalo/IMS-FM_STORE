@extends('layouts.admin')

@section('content')                    
                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><a href="{{route('tax_invoice.bin')}}">Tax Invoice BIN</a></h6>
                        </div>
                        <div class="card-header py-3">
                            <!-- <div class="input-group">
                                <form method="GET" action="{{route('purchase_order.search')}}">
                                    <label>
                                        <input type="search" class="form-control form-control-sm" placeholder="Search" name="search" aria-controls="dataTable">
                                    </label>
                                    <button class="btn btn-primary btn-sm" type="submit">
                                        <i class="fas fa-search fa-sm"></i>
                                    </button>
                                </form>
                            </div> -->
                            <!-- <a href="{{route('purchase_order.create')}}">Create Purchase Order</a> -->
                            <!-- <a href="#" class="btn btn-link btn-sm" data-toggle="modal" data-target="#exampleModal"><i class="fa fa-upload"></i> Upload Excel</a> -->
                            <!-- <a download="Template_po.xlsx" href="{{ Storage::url('tpl/template_po.xlsx') }}" title="Template_po.xlsx"><i class="fa fa-download"></i> Download Template Excel</a> -->

                            <!-- <a href="{{route('purchase_order.download')}}" class="btn btn-link btn-sm"><i class="fa fa-download"></i> Download Template Excel</a> -->
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
                                            <th colspan="2" rowspan="2" class="text-center align-middle">Shipping Via</th>
                                            <th rowspan="2" colspan="4" class="text-center align-middle">Action</th>
                                        </tr>
                                        <tr>

                                                <th class="text-center align-middle text-wrap">Code</th>
                                                <th class="text-center align-middle text-wrap">Name</th>
                                                
                                                <th class="text-center align-middle text-wrap">Date</th>
                                                <th class="text-center align-middle text-wrap">Code</th>

                                            </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $no=1;
                                        ?>
                                        @foreach($data as $f)
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
                                            <td>
                                                <a href="{{route('receipt_invoice.rollback', $f->id)}}" class="btn btn-flat btn-primary btn-sm show-alert-rollback-box"><i class="fa fa-undo"></i></a>
                                            </td>
                                        </tr>
                                    </tbody>
                                    @endforeach
                                    <tr>
                                        <td colspan="10" style="align-items: center;">
                                            {{ $data->links() }}   
                                            Total : {{ $data->total() }} data
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endsection