@extends('layouts.admin')

@section('content')
<div class="row">

    <!-- PURCHASE ORDER -->
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="{{ route('purchase_order.index') }}">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-gray-900 text-uppercase mb-1">
                                Purchase Order
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $total_po }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-signature fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- DELIVERY ORDER -->
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="{{ route('delivery_order.index') }}">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-gray-900 text-uppercase mb-1">
                                Delivery Order
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $total_do }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- PRODUCT IN -->
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="{{ route('product_inbound.index') }}">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-gray-900 text-uppercase mb-1">
                                Product In
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $total_inb }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-arrow-circle-down fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- PRODUCT OUT -->
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="{{ route('product_outbound.index') }}">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-gray-900 text-uppercase mb-1">
                                Product Out
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $total_outb }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-arrow-circle-up fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

</div>
@endsection
