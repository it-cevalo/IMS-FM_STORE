@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Data Master Kurir</h6>
    </div>

    <div class="card-body">
        <form id="formCourier" action="{{ route('couriers.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="codeCourier">Kode Kurir</label>
                <input class="form-control" id="codeCourier" name="code_courier" type="text"
                    placeholder="Masukkan Kode Kurir">
            </div>

            <div class="mb-3">
                <label for="nameCourier">Nama Kurir</label>
                <input class="form-control" id="nameCourier" name="nama_courier" type="text"
                    placeholder="Masukkan Nama Kurir">
            </div>

            <button type="button" class="btn btn-primary" id="btnSaveCourier">Simpan</button>
            <a href="{{ route('couriers.index') }}" class="btn btn-dark">Kembali</a>
        </form>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#btnSaveCourier').on('click', function () {
            let formData = new FormData($('#formCourier')[0]);
            let storeUrl = $('#formCourier').attr('action');

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
                    $('#btnSaveCourier').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
                },
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: res.message
                    }).then(() => {
                        window.location.href = "{{ route('couriers.index') }}";
                    });
                },
                error: function (xhr) {
                    $('#btnSaveCourier').prop('disabled', false).html('Submit');
                    let res = xhr.responseJSON;

                    if (xhr.status === 422) {
                        // Laravel validation error
                        let errorList = '<ul style="text-align:left;">';
                        $.each(res.errors, function (field, messages) {
                            messages.forEach(msg => {
                                errorList += `<li>${msg}</li>`;
                            });
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
                            title: 'Error!',
                            text: res && res.message
                                ? res.message
                                : 'Terjadi kesalahan. Silakan coba lagi.'
                        });
                    }
                }
            });
        });
    });
</script>
@endsection