@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Delivery Order</h6>
    </div>
    <div class="card-body">
        @if(\Session::has('error'))
        <div class="alert alert-danger">
            <span>{{ \Session::get('error') }}</span>
            <button type="button" class="close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @elseif(\Session::has('success'))
        <div class="alert alert-success">
            <span>{{ \Session::get('success') }}</span>
            <button type="button" class="close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
        <div class="mb-3">
            <label for="exampleFormControlInput1">Purchase Order</label>
            <div class="input-group">
                <select class="form-control" name="id_po" value="{{old('id_po')}}" disabled>
                    @foreach($po as $p)
                    <option value="{{$p->id}}" @if ($delivery_order->id_po == $p->id) selected
                        @endif>{{ \Carbon\Carbon::parse($p->tgl_po)->format('Y-m-d')}}/{{$p->no_po}}/{{$p->nama_spl}}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="validation"></div>
        @error('id_po')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
        <div class="mb-3">
            <label for="exampleFormControlInput1">Date</label>
            <input class="form-control" id="exampleFormControlInput1" name="tgl_do"
                value="{{ \Carbon\Carbon::parse($delivery_order->tgl_do)->format('Y-m-d')}}" type="date" disabled>
        </div>
        <div class="validation"></div>
        @error('tgl_do')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
        <div class="mb-3">
            <label for="exampleFormControlInput1">Number</label>
            <input class="form-control" id="exampleFormControlInput1" name="no_do" type="text"
                value="{{$delivery_order->no_do}}" placeholder="Input DO Number" disabled>
        </div>
        <div class="validation"></div>
        @error('no_do')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
        <div class="mb-3">
            <label for="exampleFormControlInput1">Attachment Status</label>
            <select class="form-control" name="status_lmpr_do" disabled>
                @foreach($status_lmpr_do as $k => $v)
                @if($delivery_order->status_lmpr_do == $k)
                <option value="{{ $k }}" selected="">{{ $v }}</option>
                @else
                <option value="{{ $k }}">{{ $v }}</option>
                @endif
                @endforeach
            </select>
        </div>
        <div class="validation"></div>
        @error('status_lmpr_do')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
        <div class="mb-3">
            <label for="exampleFormControlInput1">Shipping Via</label>
            <select class="form-control" name="shipping_via" disabled>
                @foreach($shipping_via as $k => $v)
                @if($delivery_order->shipping_via == $k)
                <option value="{{ $k }}" selected="">{{ $v }}</option>
                @else
                <option value="{{ $k }}">{{ $v }}</option>
                @endif
                @endforeach
            </select>
        </div>
        <div class="validation"></div>
        @error('shipping_via')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
        <div class="mb-3">
            <label for="exampleFormControlInput1">Reason</label>
            <textarea class="form-control" id="exampleFormControlInput1" name="reason_do"
                value="{{$delivery_order->reason_do}}" type="text" placeholder="Input Reason"
                disabled>{{$delivery_order->reason_do}}</textarea>
        </div>
        <div class="validation"></div>
        @error('reason_do')
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
                    @foreach($po_dtl as $k => $val)
                    <tr class="row-akun">
                        <td>
                            <input type="text" class="form-control" name="part_number[]" disabled
                                value="{{ $val->part_number }}" />
                        </td>
                        <td>
                            <input type="text" class="form-control" name="product_name[]" disabled
                                value="{{ $val->product_name }}" />
                        </td>
                        <td>
                            <input type="number" autocomplete="off" class="form-control price text-right " disabled
                                name="price[]" value="{{ $val->price }}">
                        </td>
                        <td>
                            <input type="number" autocomplete="off" class="form-control qty text-right " disabled
                                name="qty[]" value="{{ $val->qty }}">
                        </td>
                        <td>
                            <input id="myInput" type="number" autocomplete="off" name="total_price[]" disabled
                                value="{{ $val->total_price }}" class="form-control text-right data-nilai" readonly="">
                        </td>
                    </tr>
                </tbody>
                @endforeach
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-right align-middle">Grand Total (Harga telah ditambah ppn 11%)</td>
                        <td><input id="grand_total" type="text" name="grand_total" readonly=""
                                value="{{$delivery_order->po->grand_total}}" class="form-control text-right"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <a href="{{route('delivery_order.index')}}" class="btn btn-dark">Cancel</a>
    </div>
</div>
@endsection