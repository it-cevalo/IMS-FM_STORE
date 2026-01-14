@extends('layouts.admin')

@section('content')  
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Receipt Invoice</h6>
    </div>
    <div class="card-body">
        <form action="{{route('receipt_invoice.update',$receipt_invoice->id)}}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="exampleFormControlInput1">Invoice</label>
                    <div class="input-group">
                            <select class="form-control" name="id_inv" value="{{old('id_inv')}}" disabled>
                                <option value="">....</option>
                                @foreach($inv as $p)
                                <option value="{{$p->id}}" @if ($receipt_invoice->id_inv == $p->id) selected @endif>{{ \Carbon\Carbon::parse($p->tgl_inv)->format('Y-m-d')}}/{{$p->no_inv}}/{{$p->code_cust}}</option>
                                @endforeach
                            </select>
                    </div> 
            </div>
            <div class="validation"></div>
                @error('id_inv')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Number</label>
                <input class="form-control" id="exampleFormControlInput1" name="no_tti" value="{{$receipt_invoice->no_tti}}" type="text" placeholder="Masukkan Receipt Invoice Number" disabled>
            </div>
            <div class="validation"></div>
                @error('no_tti')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Courier</label>
                    <div class="input-group">
                            <select class="form-control" name="code_courier" value="{{old('code_courier')}}" disabled>
                                <option value="">....</option>
                                @foreach($courier as $p)
                                <option value="{{$p->code_courier}}" @if ($receipt_invoice->code_courier == $p->code_courier) selected @endif>{{$p->code_courier}} / {{$p->nama_courier}}</option>
                                @endforeach
                            </select>
                    </div> 
            </div>
            <div class="validation"></div>
                @error('code_courier')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
        </form>
    </div>
</div>
@endsection