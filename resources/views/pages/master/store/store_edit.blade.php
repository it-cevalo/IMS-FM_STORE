@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Master Data Store</h6>
    </div>
    <div class="card-body">
        <form id="formUpdateStore" action="{{ route('stores.update', $store->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label>Store Code</label>
                <input class="form-control" name="code_store" type="text" value="{{ $store->code_store }}" readonly>
            </div>
            <div class="mb-3">
                <label>Store Name</label>
                <input class="form-control" name="nama_store" type="text" value="{{ $store->nama_store }}" placeholder="Input Store Name">
            </div>
            <div class="mb-3">
                <label>Store Phone</label>
                <input class="form-control" name="phone" type="number" value="{{ $store->phone }}" placeholder="Input Store Phone">
            </div>
            <div class="mb-3">
                <label>Store Email</label>
                <input class="form-control" name="email" type="email" value="{{ $store->email }}" placeholder="Input Store Email">
            </div>
            <div class="mb-3">
                <label>Store Address</label>
                <input class="form-control" name="address" type="text" value="{{ $store->address }}" placeholder="Input Store Address">
            </div>

            <button type="button" class="btn btn-primary" id="btnUpdateStore">Submit</button>
            <a href="{{ route('stores.index') }}" class="btn btn-dark">Back</a>
        </form>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('#btnUpdateStore').on('click', function () {
            const form = $('#formUpdateStore')[0];
            const formData = new FormData(form);
            const url = $('#formUpdateStore').attr('action');

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-HTTP-Method-Override': 'PUT'
                },
                beforeSend: function () {
                    $('#btnUpdateStore').prop('disabled', true).text('Updating...');
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
                    $('#btnUpdateStore').prop('disabled', false).text('Submit');
                    const res = xhr.responseJSON;

                    if (xhr.status === 422) {
                        let errors = '<ul style="text-align:left;">';
                        Object.values(res.errors).forEach(msgs => {
                            msgs.forEach(msg => errors += `<li>${msg}</li>`);
                        });
                        errors += '</ul>';

                        Swal.fire({
                            icon: 'warning',
                            title: 'Validasi Gagal',
                            html: errors
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res.message ?? 'Terjadi kesalahan. Silakan coba lagi.'
                        });
                    }
                }
            });
        });
    });
</script>
@endsection