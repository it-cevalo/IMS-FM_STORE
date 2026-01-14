@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Data Satuan Barang</h6>
    </div>
    <div class="card-body">

        <form id="formStoreProductUnit" action="{{ route('product_unit.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="exampleFormControlInput1">Satuan Barang</label>
                <input class="form-control" name="nama_unit" type="text">
            </div>
            <div class="validation"></div>

            <button type="button" class="btn btn-primary" id="btnSaveProductUnit">Simpan</button>
            <a href="{{ route('product_unit.index') }}" class="btn btn-dark">Kembali</a>
        </form>

    </div>
</div>
<script>
    $(document).ready(function () {
        $('#btnSaveProductUnit').on('click', function () {
            let formData = new FormData($('#formStoreProductUnit')[0]);
            let storeUrl = $('#formStoreProductUnit').attr('action');

            $.ajax({
                url: storeUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                beforeSend: function () {
                    $('#btnSaveProductUnit').prop('disabled', true).text('Menyimpan...');
                    $('.validation').html('');
                },
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title:'Berhasil',
                        text: res.message
                    }).then(() => {
                        window.location.href = "{{ route('product_unit.index') }}";
                    });
                },
                error: function (xhr) {
                    $('#btnSaveProductUnit').prop('disabled', false).text('Submit');
                    let res = xhr.responseJSON;

                    if (xhr.status === 422) {
                        let errorList = '<ul style="text-align:left;">';
                        res.errors.forEach(err => {
                            errorList += `<li>${err}</li>`;
                        });
                        errorList += '</ul>';

                        Swal.fire({
                            icon: 'warning',
                            title: 'Data tidak Valid.',
                            html: errorList
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan',
                            text: res && res.message ? res.message : 'Terjadi kesalahan. Silakan coba lagi.'
                        });
                    }
                }
            });
        });
    });
</script>
@endsection