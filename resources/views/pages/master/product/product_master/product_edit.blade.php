@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Master Data Product Type</h6>
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
        <form id="formUpdate" action="{{ route('product.update', $products->id) }}" method="POST">
            @csrf
            {{ method_field('PUT') }}
            <div class="mb-3">
                <label for="SKU">SKU</label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="SKU" 
                    name="SKU" 
                    value="{{ optional($msku->firstWhere('kode', $products->SKU))->nama ?? '' }} ({{ $products->SKU }})" 
                    readonly
                >
            </div>
            <div class="validation"></div>
            @error('SKU')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            
            {{-- Kode Barang (readonly) --}}
            <div class="mb-3">
                <label for="kode_barang">Code</label>
                <input class="form-control" id="kode_barang" name="kode_barang" type="text"
                    value="{{ $products->kode_barang }}" readonly>
            </div>
            
            <div class="mb-3">
                <label for="exampleFormControlInput1">Name</label>
                <input class="form-control" id="exampleFormControlInput1" name="nama_barang" type="text"
                    value="{{$products->nama_barang}}" readonly>
            </div>
            <div class="validation"></div>
            @error('nama_barang')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Type</label>
                <select class="form-control select2" id="search-type" name="id_type" value="{{old('id_type')}}"
                    required>
                    <option value="">....</option>
                    @forelse($product_type as $p)
                    <option value="{{$p->id}}" @if ($products->id_type == $p->id) selected @endif>{{$p->nama_tipe}}
                    </option>
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
                <select class="form-control select2" id="search-type" name="id_unit" value="{{old('id_unit')}}" required>
                    <option value="">....</option>
                    @forelse($product_unit as $p)
                    <option value="{{$p->id}}" @if ($products->id_unit == $p->id) selected @endif>{{$p->nama_unit}}
                    </option>
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
                <input class="form-control" id="exampleFormControlInput1" name="stock_minimum" min="0" type="number" value="{{$products->stock_minimum}}" required>
            </div>
            <div class="validation"></div>
            @error('stock_minimum')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            {{-- <div class="mb-3">
                <label for="exampleFormControlInput1">Buy Price</label>
                <input class="form-control" id="exampleFormControlInput1" name="harga_beli" type="number" value="{{$products->harga_beli}}">
            </div>
            <div class="validation"></div>
            @error('harga_beli')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Sale Price</label>
                <input class="form-control" id="exampleFormControlInput1" name="harga_jual" type="number" value="{{$products->harga_jual}}">
            </div>
            <div class="validation"></div>
            @error('harga_jual')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror --}}
            {{-- <div class="mb-3">
                <label for="exampleFormControlInput1">Average Price</label>
                <input class="form-control" id="exampleFormControlInput1" name="harga_rata_rata" type="number"
                    value="{{$products->harga_rata_rata}}" placeholder="Input Average Price">
            </div>
            <div class="validation"></div>
            @error('harga_rata_rata')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror --}}
            <div class="mb-3">
                <label for="exampleFormControlInput1">Active Status</label>
                <select class="form-control form-control-sm" name="flag_active">
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
            <button type="button" class="btn btn-primary" id="btnUpdate">Submit</button>
            <a href="{{route('product.index')}}" class="btn btn-dark">Back</a>
        </form>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('.select2').select2();
        $('#btnUpdate').on('click', function () {
            let formData = new FormData($('#formUpdate')[0]);
            let updateUrl = $('#formUpdate').attr('action');

            $.ajax({
                url: updateUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-HTTP-Method-Override': 'PUT' // Laravel expects PUT
                },
                beforeSend: function () {
                    $('#btnUpdate').prop('disabled', true).text('Updating...');
                },
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: res.message
                    }).then(() => {
                        window.location.href = "{{ route('product.index') }}";
                    });
                },
                error: function (xhr) {
                    $('#btnUpdate').prop('disabled', false).text('Submit');
                    let res = xhr.responseJSON;

                    if (xhr.status === 422) {
                        let errorList = '<ul style="text-align:left;">';
                        res.errors.forEach(err => {
                            errorList += `<li>${err}</li>`;
                        });
                        errorList += '</ul>';

                        Swal.fire({
                            icon: 'warning',
                            title: 'Invalid Input',
                            html: errorList
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: res && res.message ? res.message : 'An unexpected error occurred. Please try again later.'
                        });
                    }
                }
            });
        });
        
    });
</script>
@endsection