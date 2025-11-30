@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Master Data Store</h6>
    </div>
    <div class="card-body">
        <form id="formStore" action="{{ route('stores.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="code_store">Store Code</label>
                <input class="form-control" name="code_store" type="text" placeholder="Input Store Code">
            </div>
            <div class="mb-3">
                <label for="nama_store">Store Name</label>
                <input class="form-control" name="nama_store" type="text" placeholder="Input Store Name">
            </div>
            <div class="mb-3">
                <label for="phone">Store Phone</label>
                <input class="form-control" name="phone" type="number" min="0" placeholder="Input Store Phone">
            </div>
            <div class="mb-3">
                <label for="email">Store Email</label>
                <input class="form-control" name="email" type="email" placeholder="Input Store Email">
            </div>
            <div class="mb-3">
                <label for="address">Store Address</label>
                <input class="form-control" name="address" type="text" placeholder="Input Store Address">
            </div>

            <button type="button" class="btn btn-primary" id="btnStore">Submit</button>
            <a href="{{ route('stores.index') }}" class="btn btn-dark">Cancel</a>
        </form>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('#btnStore').on('click', function () {
            let formData = new FormData($('#formStore')[0]);
            let storeUrl = $('#formStore').attr('action');

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
                    $('#btnStore').prop('disabled', true).text('Submitting...');
                    $('.validation').html('');
                },
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message
                    }).then(() => {
                        window.location.href = "{{ route('stores.index') }}";
                    });
                },
                error: function (xhr) {
                    $('#btnStore').prop('disabled', false).text('Submit');
                    let res = xhr.responseJSON;

                    if (xhr.status === 422) {
                        let errorList = '<ul style="text-align:left;">';
                        Object.values(res.errors).forEach(errArr => {
                            errArr.forEach(err => {
                                errorList += `<li>${err}</li>`;
                            });
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
                            text: res.message ?? 'Terjadi kesalahan tidak terduga.'
                        });
                    }
                }
            });
        });
    });
</script>
@endsection