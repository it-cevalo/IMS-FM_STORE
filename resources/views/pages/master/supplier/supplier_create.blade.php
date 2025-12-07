@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Master Data Supplier</h6>
    </div>
    <div class="card-body">
        <form id="formSupplierStore" action="{{ route('suppliers.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label>Supplier Code</label>
                <input class="form-control" name="code_spl" type="text">
            </div>
            <div class="mb-3">
                <label>Supplier Name</label>
                <input class="form-control" name="nama_spl" type="text">
            </div>
            <div class="mb-3">
                <label>Supplier Phone</label>
                <input class="form-control" name="phone" type="number">
            </div>
            <div class="mb-3">
                <label>Supplier Email</label>
                <input class="form-control" name="email" type="email">
            </div>
            <div class="mb-3">
                <label>Supplier NPWP</label>
                <input class="form-control" name="npwp_spl" type="text">
            </div>
            <div class="mb-3">
                <label>Supplier Address</label>
                <input class="form-control" name="address_spl" type="text">
            </div>
            <div class="mb-3">
                <label>Supplier Address NPWP</label>
                <input class="form-control" name="address_npwp" type="text">
            </div>
            <div class="mb-3">
                <label>PIC Name</label>
                <input class="form-control" name="name_pic" type="text">
            </div>
            <div class="mb-3">
                <label>PIC Phone</label>
                <input class="form-control" name="phone_pic" type="number">
            </div>
            <div class="mb-3">
                <label>PIC Email</label>
                <input class="form-control" name="email_pic" type="email">
            </div>
            <button type="button" class="btn btn-primary" id="btnSubmitSupplier">Submit</button>
            <a href="{{ route('suppliers.index') }}" class="btn btn-dark">Back</a>
        </form>
    </div>
</div>
<script>
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
                $('#btnSubmitSupplier').prop('disabled', true).text('Saving...');
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
                        text: res && res.message ? res.message : 'An unexpected error occurred. Please try again later.'
                    });
                }
            }
        });
    });
</script>
@endsection