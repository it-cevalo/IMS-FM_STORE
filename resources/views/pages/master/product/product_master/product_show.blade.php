@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Master Data Product</h6>
    </div>
    <div class="card-body">
        <!-- <form action="{{route('product.update', $products->id)}}" method="POST"> -->
        @csrf
        {{ method_field('PUT') }}
        <div class="mb-3">
            <label for="exampleFormControlInput1">No SKU</label>
            <input class="form-control" id="exampleFormControlInput1" name="SKU" type="text" value="{{$products->SKU}}"
                placeholder="Input SKU" readonly>
        </div>
        <div class="validation"></div>
        @error('SKU')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
        <div class="mb-3">
            <label for="exampleFormControlInput1">Name</label>
            <input class="form-control" id="exampleFormControlInput1" name="nama_barang" type="text"
                value="{{$products->nama_barang}}" placeholder="Input Name" readonly>
        </div>
        <div class="validation"></div>
        @error('nama_barang')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
        <div class="mb-3">
            <label for="exampleFormControlInput1">Type</label>
            <select class="form-control select2" id="search-type" name="id_type" value="{{old('id_type')}}" readonly>
                <option value="">....</option>
                @forelse($product_type as $p)
                <option value="{{$p->id}}" @if ($products->id_type == $p->id) selected @endif>{{$p->nama_tipe}}</option>
                @empty
                @endforelse
            </select>
        </div>
        <div class="validation"></div>
        @error('id_type')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
        <div class="mb-3">
            <label for="exampleFormControlInput1">UOM</label>
            <select class="form-control select2" id="search-type" name="id_unit" value="{{old('id_unit')}}" readonly>
                <option value="">....</option>
                @forelse($product_unit as $p)
                <option value="{{$p->id}}" @if ($products->id_unit == $p->id) selected @endif>{{$p->nama_unit}}</option>
                @empty
                @endforelse
            </select>
        </div>
        <div class="validation"></div>
        @error('id_unit')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
        <div class="mb-3">
            <label for="exampleFormControlInput1">Stock Minimum</label>
            <input class="form-control" id="exampleFormControlInput1" name="stock_minimum" type="number"
                value="{{$products->stock_minimum}}" placeholder="Input Buy Price" readonly>
        </div>
        <div class="validation"></div>
        @error('stock_minimum')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
        <div class="mb-3">
            <label for="exampleFormControlInput1">Buy Price</label>
            <input class="form-control" id="exampleFormControlInput1" name="harga_beli" type="number"
                value="{{$products->harga_beli}}" placeholder="Input Buy Price" readonly>
        </div>
        <div class="validation"></div>
        @error('harga_beli')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
        <div class="mb-3">
            <label for="exampleFormControlInput1">Sale Price</label>
            <input class="form-control" id="exampleFormControlInput1" name="harga_jual" type="number"
                value="{{$products->harga_jual}}" placeholder="Input Sale Price" readonly>
        </div>
        <div class="validation"></div>
        @error('harga_jual')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
        {{-- <div class="mb-3">
            <label for="exampleFormControlInput1">Average Price</label>
            <input class="form-control" id="exampleFormControlInput1" name="harga_rata_rata" type="number"
                value="{{$products->harga_rata_rata}}" placeholder="Input Average Price" readonly>
        </div>
        <div class="validation"></div>
        @error('harga_rata_rata')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror --}}
        <div class="mb-3">
            <label for="exampleFormControlInput1">Active Status</label>
            <select class="form-control form-control-sm" name="flag_active" disabled>
                @foreach($flag_active as $k => $v)
                @if($products->flag_active == $k)
                <option value="{{ $k }}" selected="">{{ $v }}</option>
                @else
                <option value="{{ $k }}">{{ $v }}</option>
                @endif
                @endforeach
            </select>
        </div>
        <div class="validation"></div>
        @error('flag_active')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
        
        <a href="{{route('product.index')}}" class="btn btn-dark">Back</a>
        <!-- </form> -->
    </div>
</div>
@endsection