@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Data Master Kurir</h6>
    </div>
    <div class="card-body">

        <form id="formUpdateCourier" action="{{ route('couriers.update', $courier->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="codeCourier">Kode Kurir</label>
                <input class="form-control" id="codeCourier" name="code_courier" type="text"
                    value="{{ $courier->code_courier }}" placeholder="Masukkan Kode Kurir">
            </div>

            <div class="mb-3">
                <label for="namaCourier">Nama Kurir</label>
                <input class="form-control" id="namaCourier" name="nama_courier" type="text"
                    value="{{ $courier->nama_courier }}" placeholder="Masukkan Nama Kurir">
            </div>

            <button type="button" class="btn btn-primary" id="btnUpdateCourier">Simpan</button>
            <a href="{{ route('couriers.index') }}" class="btn btn-dark">Batal</a>
        </form>

    </div>
</div>

<script>
    $(document).ready(function () {
        $('#btnUpdateCourier').on('click', function () {
            let formData = new FormData($('#formUpdateCourier')[0]);
            let updateUrl = $('#formUpdateCourier').attr('action');

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
                    $('#btnUpdateCourier').prop('disabled', true)
                        .html('<i class="fa fa-spinner fa-spin"></i> Updating...');
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
                    $('#btnUpdateCourier').prop('disabled', false).html('Submit');
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
