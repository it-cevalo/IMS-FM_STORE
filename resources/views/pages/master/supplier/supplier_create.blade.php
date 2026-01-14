@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Data Master Pemasok</h6>
    </div>
    <div class="card-body">
        <form id="formSupplierStore" action="{{ route('suppliers.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label>Kode Pemasok</label>
                <input class="form-control" name="code_spl" type="text">
            </div>
            <div class="mb-3">
                <label>Nama Pemasok</label>
                <input class="form-control" name="nama_spl" type="text">
            </div>
            <div class="mb-3">
                <label>No HP Pemasok</label>
                <input class="form-control" name="phone" type="number">
            </div>
            <div class="mb-3">
                <label>Email Pemasok</label>
                <input class="form-control" name="email" type="email">
            </div>
            <div class="mb-3">
                <label>NPWP Pemasok</label>
                <input
                    class="form-control"
                    name="npwp_spl"
                    type="text"
                    maxlength="20"
                    placeholder="NPWP / NIK"
                    oninput="formatNPWP(this)"
                >
            </div>
            <div class="mb-3">
                <label>Alamat Pemasok</label>
                <input class="form-control" name="address_spl" type="text">
            </div>
            <div class="mb-3">
                <label>Alamat Pemasok NPWP</label>
                <input class="form-control" name="address_npwp" type="text">
            </div>
            <div class="mb-3">
                <label>Nama PIC</label>
                <input class="form-control" name="name_pic" type="text">
            </div>
            <div class="mb-3">
                <label>No HP PIC</label>
                <input class="form-control" name="phone_pic" type="number">
            </div>
            <div class="mb-3">
                <label>Email PIC</label>
                <input class="form-control" name="email_pic" type="email">
            </div>
            <button type="button" class="btn btn-primary" id="btnSubmitSupplier">Simpan</button>
            <a href="{{ route('suppliers.index') }}" class="btn btn-dark">Kembali</a>
        </form>
    </div>
</div>
<script>
    function formatNPWP(el) {
        // Hanya angka, titik, dan strip
        el.value = el.value.replace(/[^0-9.-]/g, '');
    }
    $('#btnSubmitSupplier').on('click', function () {
        let form = $('#formSupplierStore')[0];
        let formData = new FormData(form);
        let url = $('#formSupplierStore').attr('action');

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            beforeSend: function () {
                $('#btnSubmitSupplier').prop('disabled', true).text('Menyimpan...');
            },
            success: function (res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: res.message
                }).then(() => {
                    window.location.href = "{{ route('suppliers.index') }}";
                });
            },
            error: function (xhr) {
                $('#btnSubmitSupplier').prop('disabled', false).text('Submit');
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let errorList = '<ul style="text-align:left;">';
                    for (let key in errors) {
                        errors[key].forEach(function (msg) {
                            errorList += `<li>${msg}</li>`;
                        });
                    }
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
                        text: res && res.message ? res.message : 'Terjadi kesalahan. Silakan coba lagi.'
                    });
                }
            }
        });
    });
</script>
@endsection