@extends('layouts.admin')

@section('content')  
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Payment</h6>
    </div>
    <div class="card-body">
        <form action="{{route('payment.update',$payment->id)}}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="exampleFormControlInput1">Tax Invoice</label>
                    <div class="input-group">
                            <select class="form-control" name="id_tax_inv" value="{{old('id_tax_inv')}}" disabled>
                                <option value="#">....</option>
                                @foreach($tax_inv as $p)
                                <option value="{{$p->id}}" @if ($payment->id_tax_inv == $p->id) selected @endif>{{ \Carbon\Carbon::parse($p->tgl_inv)->format('Y-m-d')}}/{{$p->no_inv}}</option>
                                @endforeach
                            </select>
                    </div> 
            </div>
            <div class="validation"></div>
                @error('id_tax_inv')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Pemesanan Barang</label>
                    <div class="input-group">
                            <select class="form-control" name="id_po" value="{{old('id_po')}}" disabled>
                                <option value="">....</option>
                                @foreach($po as $p)
                                <option value="{{$p->id}}" @if ($payment->id_po == $p->id) selected @endif>{{ \Carbon\Carbon::parse($p->tgl_po)->format('Y-m-d')}}/{{$p->no_po}}/{{$p->nama_cust}}</option>
                                @endforeach
                            </select>
                    </div> 
            </div>
            <div class="validation"></div>
                @error('id_po')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror              
            <div class="mb-3">
                <label for="exampleFormControlInput1">Payment Via</label>
                    <div class="input-group">
                            <select class="form-control" name="payment_via" value="{{old('payment_via')}}" disabled>
                                @foreach($payment_via as $k => $v)
                                    @if($payment->payment_via == $k)
                                        <option value="{{ $k }}" selected="">{{ $v }}</option>
                                    @else
                                        <option value="{{ $k }}">{{ $v }}</option>
                                    @endif
                                @endforeach
                            </select>
                    </div> 
            </div>
            <div class="validation"></div>
                @error('payment_via')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Bank Account</label>
                    <div class="input-group">
                            <select class="form-control" name="bank_account" value="{{old('bank_account')}}" disabled>      
                                @foreach($bank_account as $k => $v)
                                    @if($payment->bank_account == $k)
                                        <option value="{{ $k }}" selected="">{{ $v }}</option>
                                    @else
                                        <option value="{{ $k }}">{{ $v }}</option>
                                    @endif
                                @endforeach
                            </select>
                    </div> 
            </div>
            <div class="validation"></div>
                @error('bank_account')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Invoice Paid</label>
                <input class="form-control" id="exampleFormControlInput1" value="{{$payment->invoice_paid}}" name="invoice_paid" type="number" min="0" placeholder="Masukkan Invoice Paid" onkeypress="return /[0-9a-zA-Z]/i.test(event.key)" disabled>
            </div>
            <div class="validation"></div>
                @error('invoice_paid')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Tanggal</label>
                <input class="form-control" id="exampleFormControlInput1" value="{{$payment->payment_date}}" name="payment_date" type="date" required>
            </div>
            <div class="validation"></div>
                @error('payment_date')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Amount Paid</label>
                <input class="form-control" id="exampleFormControlInput1" value="{{$payment->amount_paid}}" name="amount_paid" type="number" min="0" placeholder="Masukkan Amount Paid" onkeypress="return /[0-9a-zA-Z]/i.test(event.key)" required>
            </div>
            <div class="validation"></div>
                @error('amount_paid')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>
</div>
@endsection