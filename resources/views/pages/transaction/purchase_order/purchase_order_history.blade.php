@extends('layouts.admin')

@section('content')                    
                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                Purchase Order History
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
                            <a href="{{route('purchase_order.index')}}" class="btn btn-primary">                                        
                                <i class="fa fa-arrow-left"> Go Back</i>
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <!-- <th colspan="2" class="text-center">Customer</th> -->
                                        <th colspan="2" class="text-center">Supplier</th>
                                        <th rowspan="2" class="text-center align-middle">Reason</th>
                                        <th rowspan="2" class="text-center align-middle">Update Date</th>
                                    </tr>
                                    <tr>
                                        <!-- <th class="text-center">Code</th>
                                        <th class="text-center">Name</th> -->
                                        <th class="text-center">Code</th>
                                        <th class="text-center">Name</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchase_order_his as $purchase_order_his)
                                    <tr>
                                        <!-- <td>{{$purchase_order_his->code_cust}}</td>
                                        <td>{{$purchase_order_his->nama_cust}}</td> -->
                                        <td>{{$purchase_order_his->code_spl}}</td>
                                        <td>{{$purchase_order_his->nama_spl}}</td>
                                        <td>{{$purchase_order_his->reason_po}}</td>
                                        <td>{{$purchase_order_his->updated_at}}</td>
                                    </tr>
                                </tbody>
                                @endforeach
                            </table>
                            </div>
                        </div>
                    </div>
                    @endsection