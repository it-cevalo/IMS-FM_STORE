@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Master Data Product</h6>
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
        <form id="productForm">
            @csrf
            <div class="mb-3">
                <label for="SKU">SKU</label>
                <select class="form-control select2" id="SKU" name="SKU" required>
                    <option value="">...</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="kode_barang">Code</label>
                <input class="form-control" id="kode_barang" name="kode_barang" type="text">
            </div>
            
            <div class="mb-3">
                <label for="exampleFormControlInput1">Name</label>
                <input class="form-control" id="exampleFormControlInput1" id="nama_barang" name="nama_barang" type="text">
            </div>
            <div class="validation"></div>
            @error('nama_barang')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Type</label>
                <select class="form-control select2" id="id_type" name="id_type" value="{{old('id_type')}}"
                    required>
                    <option value="">....</option>
                    @foreach($product_type as $p)
                    <option value="{{$p->id}}">{{$p->nama_tipe}}</option>
                    @endforeach
                </select>
            </div>
            <div class="validation"></div>
            @error('id_type')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">UOM</label>
                <select class="form-control select2" id="id_unit" name="id_unit" value="{{old('id_unit')}}"
                    required>
                    <option value="">....</option>
                    @foreach($product_unit as $p)
                    <option value="{{$p->id}}">{{$p->nama_unit}}</option>
                    @endforeach
                </select>
            </div>
            <div class="validation"></div>
            @error('id_unit')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Stock Minimum</label>
                <input class="form-control" id="exampleFormControlInput1" id="stock_minimum" name="stock_minimum" min="0" type="number" required>
            </div>
            <div class="validation"></div>
            @error('stock_minimum')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Buy Price</label>
                <input class="form-control" id="exampleFormControlInput1" id="harga_beli" name="harga_beli" type="number">
            </div>
            <div class="validation"></div>
            @error('harga_beli')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Sale Price</label>
                <input class="form-control" id="exampleFormControlInput1" id="harga_jual" name="harga_jual" type="number" >
            </div>
            <div class="validation"></div>
            @error('harga_jual')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Active Status</label>
                <select class="form-control select2" id="flag_active" name="flag_active" value="{{old('flag_active')}}"
                    required>
                    <option value="#">....</option>
                    <option value="Y">Yes</option>
                    <option value="N">No</option>
                </select>
            </div>
            <div class="validation"></div>
            @error('flag_active')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <button type="button" class="btn btn-primary" id="btnSubmit">Submit</button>
            <a href="{{route('product.index')}}" class="btn btn-dark">Back</a>
        </form>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('.select2').select2();

        // --- Inisialisasi Select2 SKU dengan AJAX ---
        $('#SKU').select2({
            ajax: {
                url: "{{ route('product.sku') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { search: params.term };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                id: item.kode,
                                text: item.nama + ' (' + item.kode + ')'
                            };
                        })
                    };
                },
                cache: true
            },
            placeholder: 'Cari SKU...',
            minimumInputLength: 0,
            width: '100%'
        });

        // --- Load otomatis 10 SKU pertama saat halaman dibuka ---
        $.ajax({
            url: "{{ route('product.sku') }}",
            dataType: 'json',
            success: function (data) {
                let defaultOptions = data.map(function (item) {
                    return new Option(item.nama + ' (' + item.kode + ')', item.kode, false, false);
                });
                $('#SKU').append(defaultOptions).trigger('change');
            }
        });

        $('#btnSubmit').on('click', function () {
            let form = $('#productForm')[0]; // ambil form
            let formData = new FormData(form);

            $.ajax({
                url: "{{ route('product.store') }}",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                beforeSend: function () {
                    $('#btnSubmit').prop('disabled', true).text('Submitting...');
                },
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = "{{ route('product.index') }}";
                    });
                },
                error: function (xhr) {
                    $('#btnSubmit').prop('disabled', false).text('Submit');

                    let res = xhr.responseJSON;

                    // Jika error validasi (422)
                    if (xhr.status === 422 && res.errors) {
                        let allErrors = '<ul style="text-align:left;">';
                        res.errors.forEach(function (msg) {
                            allErrors += `<li>${msg}</li>`;
                        });
                        allErrors += '</ul>';

                        Swal.fire({
                            icon: 'warning',
                            title: 'Invalid Input',
                            html: allErrors,
                            confirmButtonText: 'Ok'
                        });
                    } else {
                        // Error server lainnya
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: res && res.message ? res.message : 'An unexpected error occurred. Please try again later.',
                            confirmButtonText: 'Close'
                        });
                    }
                }
            });
        });
    });
    </script>
<!-- End JS  -->
@endsection