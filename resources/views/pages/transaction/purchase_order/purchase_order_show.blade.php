@extends('layouts.admin')

@section('content')  
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><a href="{{route('purchase_order.index')}}">Purchase Order</a></h6>
    </div>
    <div class="card-body">
        <!-- <form action="{{route('purchase_order.update',$purchase_order->id)}}" method="POST"> -->
            <!-- @csrf      
            @method('PUT') -->
            <!-- <div class="mb-3">
                <label for="exampleFormControlInput1">Customer</label>
                <div class="input-group">
                        <select class="form-control" name="id_cust" disabled>
                           @forelse($customers as $cust)
                           <option value="{{$cust->id}}" @if ($purchase_order->id_cust == $cust->id) selected @endif>{{$cust->code_cust}} - {{$cust->nama_cust}}</option>
                           @empty
                           @endforelse
                        </select>
                </div> 
            </div>
            <div class="validation"></div>
                @error('id_cust')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror -->
            <div class="mb-3">
                <label for="exampleFormControlInput1">Supplier</label>
                <div class="input-group">
                        <select class="form-control" name="id_supplier" disabled>
                           @forelse($suppliers as $sup)
                           <option value="{{$sup->id}}" @if ($purchase_order->id_supplier == $sup->id) selected @endif>{{$sup->code_spl}} - {{$sup->nama_spl}}</option>
                           @empty
                           @endforelse
                        </select>
                </div> 
            </div>
            <div class="validation"></div>
                @error('id_supplier')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">PO Date</label>
                <input class="form-control" id="exampleFormControlInput1" name="tgl_po" value="{{ \Carbon\Carbon::parse($purchase_order->tgl_po)->format('Y-m-d')}}" type="date" disabled>
            </div>
            <div class="validation"></div>
                @error('tgl_po')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">PO Number</label>
                <input class="form-control" id="exampleFormControlInput1" name="no_po" value="{{$purchase_order->no_po}}" type="text" placeholder="Input PO Number" disabled>
            </div>
            <div class="validation"></div>
                @error('no_po')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <!-- <div class="mb-3">
                <label for="exampleFormControlInput1">SO Number</label>
                <input class="form-control" id="exampleFormControlInput1" name="no_so" value="{{$purchase_order->no_so}}" type="text" placeholder="Input SO Number" disabled>
            </div>
            <div class="validation"></div>
                @error('no_so')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror -->
            <div class="mb-3">
                <label for="exampleFormControlInput1">Status</label>
                <select class="form-control form-control-sm" name="status_po" disabled>
                    @foreach($status_po as $k => $v)
                        @if($purchase_order->status_po == $k)
                            <option value="{{ $k }}" selected="">{{ $v }}</option>
                        @else
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="validation"></div>
                @error('status_po')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Reason</label>
                <input class="form-control" id="exampleFormControlInput1" name="reason_po" type="text" placeholder="Input Reason" value="{{$purchase_order->reason_po}}" disabled>
            </div>
            <div class="validation"></div>
                @error('reason_po')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="table-responsive">
                <label for="exampleFormControlInput1">Product</label>
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th rowspan="2" class="text-center align-middle">Kode Barang</th>
                            <th rowspan="2" class="text-center align-middle">Nama Barang</th>
                            <th rowspan="2" class="text-center align-middle">Qty</th>
                            <th rowspan="2" class="text-center align-middle">Harga Satuan</th>
                            <th rowspan="2" class="text-center align-middle">Total Harga</th>
                        </tr>
                    </thead>
                    <tbody id="append_akun">
                        @foreach($purchase_order_dtl as $k => $val)
                        <tr class="row-akun">
                            <td>
                                <input type="text" class="form-control" name="part_number[]" disabled value="{{ $val->part_number }}"/>
                            </td>
                            <td>
                                <input type="text" class="form-control" name="product_name[]" disabled value="{{ $val->product_name }}"/>
                            </td>
                            <td>
                                <input type="number" autocomplete="off" class="form-control qty text-right " disabled name="qty[]" value="{{ $val->qty }}">
                            </td>
                            <td>
                                <input type="number" autocomplete="off" class="form-control price text-right " disabled name="price[]" value="{{ $val->price }}">
                            </td>
                            <td>
                                <input id="myInput" type="number" autocomplete="off" name="total_price[]"  disabled value="{{ $val->total_price }}" class="form-control text-right data-nilai" readonly="">
                            </td>
                        </tr>
                    </tbody>
                    @endforeach
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-right align-middle">Grand Total (Harga telah ditambah ppn 11%)</td>
                            <td><input id="jumlah" type="text" name="jumlah" readonly="" value="{{$purchase_order->grand_total}}" class="form-control text-right"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <!-- </form> -->
            <a href="{{route('purchase_order.index')}}" class="btn btn-dark">Cancel</a>
    </div>
</div>
@endsection