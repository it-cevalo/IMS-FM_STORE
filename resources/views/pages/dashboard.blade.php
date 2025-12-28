@extends('layouts.admin')

@section('content')

<div class="row">
    @if(Auth::user()->position=='SUPERADMIN' || Auth::user()->position=='MANAGER_FINANCE')
    <!-- PO -->
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="{{route('purchase_order.index')}}">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-gray-900 text-uppercase mb-1">
                                PO</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{$total_po}}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- DO -->
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="{{route('product_inbound.index')}}">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-gray-900 text-uppercase mb-1">
                                Product In</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{$total_do}}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- INV (TAX INV) -->
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="{{route('product_outbound.index')}}">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-gray-900 text-uppercase mb-1">Product Out
                            </div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{$total_inv}}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    @elseif(Auth::user()->position=='PURCHASING')
    <!-- PO -->
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="{{route('purchase_order.index')}}">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-gray-900 text-uppercase mb-1">
                                PO</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{$total_po}}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    @elseif(Auth::user()->position=='WAREHOUSE_ADMIN')
    <!-- PO -->
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="{{route('purchase_order.index')}}">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-gray-900 text-uppercase mb-1">
                                PO</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{$total_po}}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    @endif
</div>
@endsection