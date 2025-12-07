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
        <form action="{{route('delivery_order.update',$delivery_order->id)}}" method="POST">
            @csrf
            @method('PUT')
            {{-- <div class="mb-3">
                <label for="exampleFormControlInput1">Purchase Order</label>
                <div class="input-group">
                    <select class="form-control" name="id_po" value="{{old('id_po')}}" readonly="readonly">
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
            @enderror --}}
            <div class="mb-3">
                <label for="exampleFormControlInput1">Date</label>
                <input class="form-control" id="exampleFormControlInput1" name="tgl_do"
                    value="{{ \Carbon\Carbon::parse($delivery_order->tgl_do)->format('Y-m-d')}}" type="date"
                    readonly="readonly">
            </div>
            <div class="validation"></div>
            @error('tgl_do')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Number</label>
                <input class="form-control" id="exampleFormControlInput1" name="no_do" type="text"
                    value="{{$delivery_order->no_do}}" placeholder="Input DO Number" readonly="readonly">
            </div>
            <div class="validation"></div>
            @error('no_do')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            {{-- <div class="mb-3">
                <label for="exampleFormControlInput1">Attachment Status</label>
                <select class="form-control" name="status_lmpr_do" required>
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
            @enderror --}}
            <div class="mb-3">
                <label for="exampleFormControlInput1">Shipping Via</label>
                <select class="form-control" name="shipping_via" required>
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
                <label for="exampleFormControlInput1">Note</label>
                <textarea class="form-control" id="exampleFormControlInput1" name="reason_do"
                    value="{{$delivery_order->reason_do}}" type="text" placeholder="Input Note"
                    required>{{$delivery_order->reason_do}}</textarea>
            </div>
            <div class="validation"></div>
            @error('reason_do')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="{{route('delivery_order.index')}}" class="btn btn-dark">Cancel</a>
        </form>
    </div>
</div>
@endsection