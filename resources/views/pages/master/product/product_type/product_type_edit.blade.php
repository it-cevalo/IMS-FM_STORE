@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Master Data Product Type</h6>
    </div>
    <div class="card-body">

        <form id="formUpdateProductType" action="{{ route('product_type.update', $product_type->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="exampleFormControlInput1">Name</label>
                <input class="form-control" id="exampleFormControlInput1" name="nama_tipe" type="text"
                    value="{{ $product_type->nama_tipe }}">
            </div>
            <div class="validation"></div>

            <button type="button" class="btn btn-primary" id="btnUpdateProductType">Submit</button>
            <a href="{{ route('product_type.index') }}" class="btn btn-dark">Cancel</a>
        </form>

    </div>
</div>
<script>
    $(document).ready(function () {
        $('#btnUpdateProductType').on('click', function () {
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
                    $('#btnUpdateProductType').prop('disabled', true).text('Updating...');
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
                    $('#btnUpdateProductType').prop('disabled', false).text('Submit');
                    let res = xhr.responseJSON;

                    if (xhr.status === 422) {
                        let errorList = '<ul style="text-align:left;">';
                        res.errors.forEach(err => {
                            errorList += `<li>${err}</li>`;
                        });
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
    });
</script>
@endsection