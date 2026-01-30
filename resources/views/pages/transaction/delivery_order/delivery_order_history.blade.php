@extends('layouts.admin')

@section('content')
<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            Stock Out History
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
        <a href="{{route('delivery_order.index')}}" class="btn btn-primary">
            <i class="fa fa-arrow-left"> Kembali</i>
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">

            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th colspan="2" class="text-center">Pemasok</th>
                        <th rowspan="2" class="text-center align-middle">Reason</th>
                        <th rowspan="2" class="text-center align-middle">Update Date</th>
                    </tr>
                    <tr>
                        <th class="text-center">Kode</th>
                        <th class="text-center">Nama</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($delivery_order_his as $doh)
                    <tr>
                        <td>{{$doh->supplier->code_spl}}</td>
                        <td>{{$doh->supplier->nama_spl}}</td>
                        <td>{{$doh->reason_do}}</td>
                        <td>{{$doh->updated_at}}</td>
                    </tr>
                </tbody>
                @endforeach
            </table>
        </div>
    </div>
</div>
@endsection