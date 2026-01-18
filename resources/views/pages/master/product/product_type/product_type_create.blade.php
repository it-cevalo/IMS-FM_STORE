@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Data Jenis Barang</h6>
    </div>
    <div class="card-body">

        <form id="formProductType" action="{{ route('product_type.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="exampleFormControlInput1">Nama Jenis Barang</label>
                <input class="form-control" id="exampleFormControlInput1" name="nama_tipe" type="text">
            </div>
            <div class="validation"></div>

            <button type="button" class="btn btn-primary" id="btnSaveType">Simpan</button>
            <a href="{{ route('product_type.index') }}" class="btn btn-dark">Kembali</a>
        </form>

    </div>
</div>
<script>
    $(document).ready(function () {
        $('#btnSaveType').on('click', function () {
            let formData = new FormData($('#formProductType')[0]);
            let storeUrl = $('#formProductType').attr('action');

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
                    $('#btnSaveType').prop('disabled', true).text('Menyimpan...');
                    $('.validation').html('');
                },
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: res.message
                    }).then(() => {
                        window.location.href = "{{ route('product_type.index') }}";
                    });
                },
                error: function (xhr) {
                    $('#btnSaveType').prop('disabled', false).text('Submit');
                    let res = xhr.responseJSON;

                    if (xhr.status === 422) {
                        let errorList = '<ul style="text-align:left;">';
                        res.errors.forEach(err => {
                            errorList += `<li>${err}</li>`;
                        });
                        errorList += '</ul>';

                        Swal.fire({
                            icon: 'warning',
                            title: 'Invalid input',
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