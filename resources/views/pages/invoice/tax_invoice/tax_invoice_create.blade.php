@extends('layouts.admin')

@section('content')  
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Tax Invoice</h6>
    </div>
    <div class="card-body">
        <form action="{{route('tax_invoice.store')}}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="exampleFormControlInput1">Pemesanan Barang</label>
                    <div class="input-group">
                            <select class="form-control" name="id_po" value="{{old('id_po')}}" required>
                                <option value="">....</option>
                                @foreach($po as $p)
                                <option value="{{$p->id}}">{{ \Carbon\Carbon::parse($p->tgl_po)->format('Y-m-d')}}/{{$p->no_po}}/{{$p->nama_cust}}</option>
                                @endforeach
                            </select>
                    </div> 
            </div>
            <div class="validation"></div>
                @error('id_po')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror           
            <div class="mb-3">
                <label for="exampleFormControlInput1">Pengiriman Barang</label>
                    <div class="input-group">
                            <select class="form-control" name="id_do" value="{{old('id_do')}}" required>
                                <option value="">....</option>
                                @foreach($do as $p)
                                <option value="{{$p->id}}">{{ \Carbon\Carbon::parse($p->tgl_do)->format('Y-m-d')}}/{{$p->no_do}}/{{$p->nama_cust}}</option>
                                @endforeach
                            </select>
                    </div> 
            </div>
            <div class="validation"></div>
                @error('id_do')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Tanggal</label>
                <input class="form-control" id="exampleFormControlInput1" name="tgl_inv" type="date" required>
            </div>
            <div class="validation"></div>
                @error('tgl_inv')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Number</label>
                <input class="form-control" id="exampleFormControlInput1" name="no_inv" type="text" placeholder="Masukkan Invoice Number" required>
            </div>
            <div class="validation"></div>
                @error('no_inv')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Tax Code</label>
                <input class="form-control" id="exampleFormControlInput1" name="no_seri_pajak" type="text" placeholder="Masukkan Tax Code" required>
            </div>
            <div class="validation"></div>
                @error('no_seri_pajak')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Status</label>
                <select class="form-control" name="status_faktur_pajak" value="{{old('status_faktur_pajak')}}" required>
                    <option value="">....</option>
                    <option value="OK">OK</option>
                    <option value="HOLD">HOLD</option>
                </select>
            </div>
            <div class="validation"></div>
                @error('status_faktur_pajak')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Reason</label>
                <textarea class="form-control" id="exampleFormControlInput1" name="reason_faktur_pajak" type="text" placeholder="Masukkan Reason" required></textarea>
            </div>
            <div class="validation"></div>
                @error('reason_faktur_pajak')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Invoice Status</label>
                <select class="form-control" name="status_inv" value="{{old('status_inv')}}" required>
                    <option value="">....</option>
                    <option value="OK">OK</option>
                    <option value="HOLD">HOLD</option>
                </select>
            </div>
            <div class="validation"></div>
                @error('status_inv')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Invoice Reason</label>
                <textarea class="form-control" id="exampleFormControlInput1" name="reason_inv" type="text" placeholder="Masukkan Invoice Reason" required></textarea>
            </div>
            <div class="validation"></div>
                @error('reason_inv')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Term</label>
                <textarea class="form-control" id="exampleFormControlInput1" name="term" type="text" placeholder="Masukkan Term" required></textarea>
            </div>
            <div class="validation"></div>
                @error('term')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Metode Pengiriman</label>
                <select class="form-control" name="shipping_via" value="{{old('shipping_via')}}" required>
                    <option value="">....</option>
                    <option value="HANDCARRY">HANDCARRY</option>
                    <option value="EKSPEDISI">EKSPEDISI</option>
                </select>
            </div>
            <div class="validation"></div>
                @error('shipping_via')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>
</div>
@endsection