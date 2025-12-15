@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Master Data SKU</h6>
    </div>
    <div class="card-body">

        <form id="formProductType" action="{{ route('sku.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="exampleFormControlInput1">SKU </label>
                <input class="form-control" id="exampleFormControlInput1" name="kode" type="text"
                    placeholder="Input kode">
            </div>
            <div class="validation"></div>
            
            {{-- <div class="mb-3">
                <label for="exampleFormControlInput1">Nama </label>
                <input class="form-control" id="exampleFormControlInput1" name="nama" type="text"
                    placeholder="Input nama">
            </div>
            <div class="validation"></div> --}}

            <button type="button" class="btn btn-primary" id="btnSaveType">Submit</button>
            <a href="{{ route('sku.index') }}" class="btn btn-dark">Back</a>
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
                    $('#btnSaveType').prop('disabled', true).text('Saving...');
                    $('.validation').html('');
                },
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message
                    }).then(() => {
                        window.location.href = "{{ route('sku.index') }}";
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
                            title: 'Input Tidak Valid',
                            html: errorList
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res && res.message ? res.message : 'Terjadi kesalahan tidak terduga.'
                        });
                    }
                }
            });
        });
    });
</script>
@endsection