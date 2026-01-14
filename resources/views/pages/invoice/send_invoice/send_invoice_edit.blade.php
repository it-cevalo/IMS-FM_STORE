@extends('layouts.admin')

@section('content')  
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Send Invoice</h6>
    </div>
    <div class="card-body">
        <form action="{{route('send_invoice.update',$send_invoice->id)}}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @csrf
            <div class="mb-3">
                <label for="exampleFormControlInput1">Invoice</label>
                    <div class="input-group">
                            <select class="form-control" name="id_inv_rcp" value="{{old('id_inv_rcp')}}" required>
                                <option value="">....</option>
                                @foreach($inv_rcp as $p)
                                <option value="{{$p->id}}" @if ($send_invoice->id_inv_rcp == $p->id) selected @endif>{{ \Carbon\Carbon::parse($p->tgl_inv_rcp)->format('Y-m-d')}}/{{$send_invoice->no_resi}}/{{$p->no_inv}}/{{$p->customer->nama_cust}}-{{$p->courier->nama_courier}}</option>
                                @endforeach
                            </select>
                    </div> 
            </div>
            <div class="validation"></div>
                @error('id_inv_rcp')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Resi Number / Receipt Number </label>
                <input class="form-control" id="exampleFormControlInput1" name="no_resi" value="{{$send_invoice->no_resi}}" type="text" placeholder="Masukkan Resi Number" required>
            </div>
            <div class="validation"></div>
                @error('no_resi')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Upload Receipt</label><br/>
                <input id="exampleFormControlInput1" name="bukti_tanda_terima" type="file" placeholder="Upload Receipt" required>
            </div>
            <div class="validation"></div>
                @error('bukti_tanda_terima')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>
</div>
@endsection