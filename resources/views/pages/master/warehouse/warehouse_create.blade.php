@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Data Master Gudang</h6>
    </div>
    <div class="card-body">

        <form id="formWarehouseCreate" method="POST" action="{{ route('warehouses.store') }}">
            @csrf
            <div class="mb-3">
                <label>Toko</label>
                <select class="form-control select2" name="id_store" required>
                    <option value="">...</option>
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}">{{ $s->nama_store }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Kode Gudang</label>
                <input class="form-control" name="code_wh" type="text">
            </div>

            <div class="mb-3">
                <label>Nama Gudang</label>
                <input class="form-control" name="nama_wh" type="text">
            </div>

            <div class="mb-3">
                <label>No HP Gudang</label>
                <input class="form-control" name="phone" type="number" min="0">
            </div>

            <div class="mb-3">
                <label>Email Gudang</label>
                <input class="form-control" name="email" type="email">
            </div>

            <div class="mb-3">
                <label>Alamat Gudang</label>
                <input class="form-control" name="address" type="text">
            </div>

            <button type="button" class="btn btn-primary" id="btnCreateWarehouse">Simpan</button>
            <a href="{{ route('warehouses.index') }}" class="btn btn-dark">Kembali</a>
        </form>

    </div>
</div>

<script>
$(document).ready(function () {
    $('.select2').select2({
        placeholder: "-- Select Store --",
        allowClear: true
    });

    $('#btnCreateWarehouse').on('click', function () {
        let form = $('#formWarehouseCreate')[0];
        let formData = new FormData(form);
        let storeUrl = $('#formWarehouseCreate').attr('action');

        $.ajax({
            url: storeUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            beforeSend: function () {
                $('#btnCreateWarehouse').prop('disabled', true).text('Menyimpan...');
            },
            success: function (res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: res.message
                }).then(() => {
                    window.location.href = "{{ route('warehouses.index') }}";
                });
            },
            error: function (xhr) {
                $('#btnCreateWarehouse').prop('disabled', false).text('Submit');
                let res = xhr.responseJSON;

                if (xhr.status === 422) {
                    // Laravel returns nested errors (object of arrays), flatten them safely
                    let errorList = '<ul style="text-align:left;">';

                    if (res && res.errors && typeof res.errors === 'object') {
                        $.each(res.errors, function (field, messages) {
                            if (Array.isArray(messages)) {
                                messages.forEach(msg => {
                                    errorList += `<li>${msg}</li>`;
                                });
                            } else if (typeof messages === 'string') {
                                errorList += `<li>${messages}</li>`;
                            }
                        });
                    } else if (typeof res.message === 'string') {
                        errorList += `<li>${res.message}</li>`;
                    } else {
                        errorList += `<li>Data tidak Valid. Silahkan cek data kembali.</li>`;
                    }

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