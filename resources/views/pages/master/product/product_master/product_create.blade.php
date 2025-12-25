@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Master Data Product</h6>
    </div>

    <div class="card-body">
        <form id="productForm">
            @csrf

            {{-- CODE --}}
            <div class="mb-3">
                <label for="sku">SKU</label>
                <input class="form-control" id="sku" name="sku" type="text" required>
            </div>

            {{-- NAME --}}
            <div class="mb-3">
                <label for="nama_barang">Name</label>
                <input class="form-control" id="nama_barang" name="nama_barang" type="text" required>
            </div>

            {{-- TYPE --}}
            <div class="mb-3">
                <label>Type</label>
                <select class="form-control select2" id="id_type" name="id_type" required>
                    <option value="">....</option>
                    @foreach($product_type as $p)
                        <option value="{{ $p->id }}">{{ $p->nama_tipe }}</option>
                    @endforeach
                </select>
            </div>

            {{-- UOM --}}
            <div class="mb-3">
                <label>UOM</label>
                <select class="form-control select2" id="id_unit" name="id_unit" required>
                    <option value="">....</option>
                    @foreach($product_unit as $p)
                        <option value="{{ $p->id }}">{{ $p->nama_unit }}</option>
                    @endforeach
                </select>
            </div>

            {{-- STOCK MINIMUM --}}
            <div class="mb-3">
                <label>Stock Minimum</label>
                <input class="form-control" id="stock_minimum" name="stock_minimum" type="number" min="0" required>
            </div>

            {{-- ACTIVE --}}
            <div class="mb-3">
                <label>Active Status</label>
                <select class="form-control select2" id="flag_active" name="flag_active" required>
                    <option value="">....</option>
                    <option value="Y">Yes</option>
                    <option value="N">No</option>
                </select>
            </div>

            <button type="button" class="btn btn-primary" id="btnSubmit">Submit</button>
            <a href="{{ route('product.index') }}" class="btn btn-dark">Back</a>
        </form>
    </div>
</div>

<script>
$(document).ready(function () {
    $('.select2').select2();

    // === SUBMIT AJAX ===
    $('#btnSubmit').on('click', function () {
        let formData = new FormData($('#productForm')[0]);

        $.ajax({
            url: "{{ route('product.store') }}",
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: () => {
                $('#btnSubmit').prop('disabled', true).text('Submitting...');
            },
            success: res => {
                Swal.fire('Success', res.message, 'success')
                    .then(() => window.location.href = "{{ route('product.index') }}");
            },
            error: xhr => {
                $('#btnSubmit').prop('disabled', false).text('Submit');

                if (xhr.status === 422 && xhr.responseJSON.errors) {
                    let html = '<ul>';
                    xhr.responseJSON.errors.forEach(e => html += `<li>${e}</li>`);
                    html += '</ul>';

                    Swal.fire('Invalid Input', html, 'warning');
                } else {
                    Swal.fire('Error', xhr.responseJSON?.message || 'System Error', 'error');
                }
            }
        });
    });
});
</script>
@endsection
