@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Master Data SKU</h6>
    </div>
    <div class="card-body">

        <form id="formUpdateProductType" action="{{ route('sku.update', $sku->kode) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="exampleFormControlInput1">Kode</label>
                <input class="form-control" id="exampleFormControlInput1" name="kode" type="text"
                    value="{{ $sku->kode }}" placeholder="Input Kode">
            </div>
            <div class="validation"></div>

            <div class="mb-3">
                <label for="exampleFormControlInput1">Name</label>
                <input class="form-control" id="exampleFormControlInput1" name="nama" type="text"
                    value="{{ $sku->nama }}" placeholder="Input  Name">
            </div>
            <div class="validation"></div>

            <button type="button" class="btn btn-primary" id="btnUpdateSKU">Submit</button>
            <a href="{{ route('sku.index') }}" class="btn btn-dark">Cancel</a>
        </form>

    </div>
</div>
<script>
    $(document).ready(function () {
        $('#btnUpdateSKU').on('click', function () {
            let formData = new FormData($('#formUpdateProductType')[0]);
            let updateUrl = $('#formUpdateProductType').attr('action');

            $.ajax({
                url: updateUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-HTTP-Method-Override': 'PUT'
                },
                beforeSend: function () {
                    $('#btnUpdateSKU').prop('disabled', true).text('Updating...');
                    $('.validation').html('');
                },
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message
                    }).then(() => {
                        window.location.href = "{{ route('sku.index') }}";
                    });
                },
                error: function (xhr) {
                    $('#btnUpdateSKU').prop('disabled', false).text('Submit');
                    let res = xhr.responseJSON;

                    if (xhr.status === 422) {
                        let errorList = '<ul style="text-align:left;">';
                        res.errors.forEach(err => {
                            errorList += `<li>${err}</li>`;
                        });
                        errorList += '</ul>';

                        Swal.fire({
                            icon: 'warning',
                            title: 'Input Tidak Valid',
                            html: errorList
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res && res.message ? res.message : 'Terjadi kesalahan tidak terduga.'
                        });
                    }
                }
            });
        });
    });
</script>
@endsection