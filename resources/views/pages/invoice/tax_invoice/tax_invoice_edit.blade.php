@extends('layouts.admin')

@section('content')  
<!-- Start Library Signature  -->
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script> 
    <link type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/south-street/jquery-ui.css" rel="stylesheet"> 
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script type="text/javascript" src="http://keith-wood.name/js/jquery.signature.js"></script>
  
    <link rel="stylesheet" type="text/css" href="http://keith-wood.name/css/jquery.signature.css">
<!-- End Library Signature  -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Tax Invoice</h6>
    </div>
    <div class="card-body">
        <form action="{{route('tax_invoice.update',$tax_invoice->id)}}" method="POST">
            @csrf
            @method('PUT')
            
            @if(Auth::user()->position=='DIRECTOR')
                <div class="mb-3">
                    <label for="exampleFormControlInput1">Pemesanan Barang</label>
                        <div class="input-group">
                                <select class="form-control" name="id_po" value="{{old('id_po')}}" readonly>
                                    <option value="">....</option>
                                            @foreach($po as $p)
                                            <option value="{{$p->id}}" @if ($tax_invoice->id_po == $p->id) selected @endif>{{ \Carbon\Carbon::parse($p->tgl_po)->format('Y-m-d')}}/{{$p->no_po}}/{{$p->nama_cust}}</option>
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
                                <select class="form-control" name="id_do" value="{{old('id_do')}}" readonly>
                                    <option value="">....</option>
                                    @foreach($do as $p)
                                    <option value="{{$p->id}}" @if ($tax_invoice->id_do == $p->id) selected @endif>{{ \Carbon\Carbon::parse($p->tgl_do)->format('Y-m-d')}}/{{$p->no_do}}/{{$p->nama_cust}}</option>
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
                    <input class="form-control" id="exampleFormControlInput1" name="tgl_inv" value="{{ \Carbon\Carbon::parse($tax_invoice->tgl_inv)->format('Y-m-d')}}" type="date" readonly="readonly">
                </div>
                <div class="validation"></div>
                    @error('tgl_inv')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                <div class="mb-3">
                    <label for="exampleFormControlInput1">Number</label>
                    <input class="form-control" id="exampleFormControlInput1" name="no_inv" value="{{$tax_invoice->no_inv}}" type="text" placeholder="Masukkan Invoice Number" readonly>
                </div>
                <div class="validation"></div>
                    @error('no_inv')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                <div class="mb-3">
                    <label for="exampleFormControlInput1">Tax Code</label>
                    <input class="form-control" id="exampleFormControlInput1" name="no_seri_pajak" value="{{$tax_invoice->no_seri_pajak}}" type="text" placeholder="Masukkan Tax Code" disabled>
                </div>
                <div class="validation"></div>
                    @error('no_seri_pajak')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                <div class="mb-3">
                    <label for="exampleFormControlInput1">Status</label>
                    <select class="form-control" name="status_faktur_pajak" value="{{old('status_faktur_pajak')}}" disabled>
                            @foreach($status_faktur_pajak as $k => $v)
                                @if($tax_invoice->status_faktur_pajak == $k)
                                    <option value="{{ $k }}" selected="">{{ $v }}</option>
                                @else
                                    <option value="{{ $k }}">{{ $v }}</option>
                                @endif
                            @endforeach
                    </select>
                </div>
                <div class="validation"></div>
                    @error('status_faktur_pajak')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                <div class="mb-3">
                    <label for="exampleFormControlInput1">Reason</label>
                    <textarea class="form-control" id="exampleFormControlInput1" name="reason_faktur_pajak" value="{{$tax_invoice->reason_faktur_pajak}}"  type="text" placeholder="Masukkan Reason" disabled>{{$tax_invoice->reason_faktur_pajak}}</textarea>
                </div>
                <div class="validation"></div>
                    @error('reason_faktur_pajak')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                <div class="mb-3">
                    <label for="exampleFormControlInput1">Invoice Status</label>
                    <select class="form-control" name="status_inv" value="{{old('status_inv')}}" disabled>
                            @foreach($status_inv as $k => $v)
                                @if($tax_invoice->status_inv == $k)
                                    <option value="{{ $k }}" selected="">{{ $v }}</option>
                                @else
                                    <option value="{{ $k }}">{{ $v }}</option>
                                @endif
                            @endforeach
                    </select>
                </div>
                <div class="validation"></div>
                    @error('status_inv')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                <div class="mb-3">
                    <label for="exampleFormControlInput1">Invoice Reason</label>
                    <textarea class="form-control" id="exampleFormControlInput1" name="reason_inv" value="{{$tax_invoice->reason_inv}}" type="text" placeholder="Masukkan Invoice Reason" disabled></textarea>
                </div>
                <div class="validation"></div>
                    @error('reason_inv')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                <div class="mb-3">
                    <label for="exampleFormControlInput1">Grand Total</label>
                    <input class="form-control" id="exampleFormControlInput1" name="grand_total" value="{{$tax_invoice->grand_total}}"  type="number" min="0" placeholder="Masukkan Grand Total" disabled></input>
                </div>
                <div class="validation"></div>
                    @error('grand_total')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                <div class="mb-3">
                    <label for="exampleFormControlInput1">Term</label>
                    <textarea class="form-control" id="exampleFormControlInput1" name="term" type="text" value="{{$tax_invoice->term}}" placeholder="Masukkan Term" disabled></textarea>
                </div>
                <div class="validation"></div>
                    @error('term')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                <div class="mb-3">
                    <label for="exampleFormControlInput1">Signature</label>
                    <br/>
                    <div id="sig" class="form-control" style="width:300px; height:200px;"></div>
                    <br/><br/>
                    <button id="clear" class="btn btn-danger btn-sm">Clear Signature</button>
                    <textarea id="signature64" class="form-control" name="signed" style="display: none"></textarea>
                    <!-- <textarea class="form-control" id="exampleFormControlInput1" name="term" type="text" value="{{$tax_invoice->term}}" placeholder="Masukkan Term" required></textarea> -->
                </div>
                <div class="validation"></div>
                    @error('term')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
            @else 
                <div class="mb-3">
                    <label for="exampleFormControlInput1">Pemesanan Barang</label>
                        <div class="input-group">
                                <select class="form-control" name="id_po" value="{{old('id_po')}}" readonly>
                                    <option value="">....</option>
                                            @foreach($po as $p)
                                            <option value="{{$p->id}}" @if ($tax_invoice->id_po == $p->id) selected @endif>{{ \Carbon\Carbon::parse($p->tgl_po)->format('Y-m-d')}}/{{$p->no_po}}/{{$p->nama_cust}}</option>
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
                                <select class="form-control" name="id_do" value="{{old('id_do')}}" readonly>
                                    <option value="">....</option>
                                    @foreach($do as $p)
                                    <option value="{{$p->id}}" @if ($tax_invoice->id_do == $p->id) selected @endif>{{ \Carbon\Carbon::parse($p->tgl_do)->format('Y-m-d')}}/{{$p->no_do}}/{{$p->nama_cust}}</option>
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
                    <input class="form-control" id="exampleFormControlInput1" name="tgl_inv" value="{{ \Carbon\Carbon::parse($tax_invoice->tgl_inv)->format('Y-m-d')}}" type="date" readonly="readonly">
                </div>
                <div class="validation"></div>
                    @error('tgl_inv')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                <div class="mb-3">
                    <label for="exampleFormControlInput1">Number</label>
                    <input class="form-control" id="exampleFormControlInput1" name="no_inv" value="{{$tax_invoice->no_inv}}" type="text" placeholder="Masukkan Invoice Number" readonly>
                </div>
                <div class="validation"></div>
                    @error('no_inv')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                <div class="mb-3">
                    <label for="exampleFormControlInput1">Tax Code</label>
                    <input class="form-control" id="exampleFormControlInput1" name="no_seri_pajak" value="{{$tax_invoice->no_seri_pajak}}" type="text" placeholder="Masukkan Tax Code" disabled>
                </div>
                <div class="validation"></div>
                    @error('no_seri_pajak')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                <div class="mb-3">
                    <label for="exampleFormControlInput1">Status</label>
                    <select class="form-control" name="status_faktur_pajak" value="{{old('status_faktur_pajak')}}" required>
                            @foreach($status_faktur_pajak as $k => $v)
                                @if($tax_invoice->status_faktur_pajak == $k)
                                    <option value="{{ $k }}" selected="">{{ $v }}</option>
                                @else
                                    <option value="{{ $k }}">{{ $v }}</option>
                                @endif
                            @endforeach
                    </select>
                </div>
                <div class="validation"></div>
                    @error('status_faktur_pajak')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                <div class="mb-3">
                    <label for="exampleFormControlInput1">Reason</label>
                    <textarea class="form-control" id="exampleFormControlInput1" name="reason_faktur_pajak" value="{{$tax_invoice->reason_faktur_pajak}}"  type="text" placeholder="Masukkan Reason" required>{{$tax_invoice->reason_faktur_pajak}}</textarea>
                </div>
                <div class="validation"></div>
                    @error('reason_faktur_pajak')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                <div class="mb-3">
                    <label for="exampleFormControlInput1">Invoice Status</label>
                    <select class="form-control" name="status_inv" value="{{old('status_inv')}}" required>
                            @foreach($status_inv as $k => $v)
                                @if($tax_invoice->status_inv == $k)
                                    <option value="{{ $k }}" selected="">{{ $v }}</option>
                                @else
                                    <option value="{{ $k }}">{{ $v }}</option>
                                @endif
                            @endforeach
                    </select>
                </div>
                <div class="validation"></div>
                    @error('status_inv')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                <div class="mb-3">
                    <label for="exampleFormControlInput1">Invoice Reason</label>
                    <textarea class="form-control" id="exampleFormControlInput1" name="reason_inv" value="{{$tax_invoice->reason_inv}}" type="text" placeholder="Masukkan Invoice Reason" required></textarea>
                </div>
                <div class="validation"></div>
                    @error('reason_inv')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                <div class="mb-3">
                    <label for="exampleFormControlInput1">Term</label>
                    <textarea class="form-control" id="exampleFormControlInput1" name="term" type="text" value="{{$tax_invoice->term}}" placeholder="Masukkan Term" required></textarea>
                </div>
                <div class="validation"></div>
                    @error('term')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                <div class="mb-3">
                    <label for="exampleFormControlInput1">Metode Pengiriman</label>
                    <select class="form-control" name="shipping_via" required>
                        @foreach($shipping_via as $k => $v)
                            @if($tax_invoice->shipping_via == $k)
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
            @endif
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>
</div>

<script type="text/javascript">
    var sig = $('#sig').signature({syncField: '#signature64', syncFormat: 'PNG'});
    $('#clear').click(function(e) {
        e.preventDefault();
        sig.signature('clear');
        $("#signature64").val('');
    });
</script>
@endsection