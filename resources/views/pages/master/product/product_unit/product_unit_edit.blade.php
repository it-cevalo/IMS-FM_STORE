@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Master Data UOM</h6>
    </div>
    <div class="card-body">

        <form id="formUpdateProductUnit" action="{{ route('product_unit.update', $product_unit->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="exampleFormControlInput1">UOM</label>
                <input class="form-control" name="nama_unit" type="text"
                    value="{{ $product_unit->nama_unit }}">
            </div>
            <div class="validation"></div>

            <button type="button" class="btn btn-primary" id="btnUpdateProductUnit">Submit</button>
            <a href="{{ route('product_unit.index') }}" class="btn btn-dark">Cancel</a>
        </form>

    </div>
</div>
<script>
    $(document).ready(function () {
        $('#btnUpdateProductUnit').on('click', function () {
            let formData = new FormData($('#formUpdateProductUnit')[0]);
            let updateUrl = $('#formUpdateProductUnit').attr('action');

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
                    $('#btnUpdateProductUnit').prop('disabled', true).text('Updating...');
                    $('.validation').html('');
                },
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: res.message
                    }).then(() => {
                        window.location.href = "{{ route('product_unit.index') }}";
                    });
                },
                error: function (xhr) {
                    $('#btnUpdateProductUnit').prop('disabled', false).text('Submit');
                    let res = xhr.responseJSON;

                    if (xhr.status === 422) {
                        let errorList = '<ul style="text-align:left;">';
                        res.errors.forEach(err => {
                            errorList += `<li>${err}</li>`;
                        });
                        errorList += '</ul>';

                        Swal.fire({
                            icon: 'warning',
                            title: 'Invalid Input!',
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