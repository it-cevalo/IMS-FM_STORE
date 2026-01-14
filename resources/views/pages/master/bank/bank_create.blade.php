@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Master Data Bank</h6>
    </div>
    <div class="card-body">
        @if(\Session::has('error'))
        <div class="alert alert-danger">
            <span>{{ \Session::get('error') }}</span>
            <button type="button" class="close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @elseif(\Session::has('success'))
        <div class="alert alert-success">
            <span>{{ \Session::get('success') }}</span>
            <button type="button" class="close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
        <form id="formBankStore" method="POST">
            @csrf
            <div class="mb-3">
                <label for="exampleFormControlInput1">Kode Bank</label>
                <input class="form-control" id="exampleFormControlInput1" name="code_bank" type="text"
                    placeholder="Masukkan Kode Bank">
            </div>
            <div class="validation"></div>
            @error('code_bank')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Nama Bank</label>
                <input class="form-control" id="exampleFormControlInput1" name="nama_bank" type="text"
                    placeholder="Masukkan Nama Bank">
            </div>
            <div class="validation"></div>
            @error('nama_bank')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Nomor Rekening Bank</label>
                <input class="form-control" id="exampleFormControlInput1" name="norek_bank" type="text"
                    placeholder="Masukkan No Rekening Bank">
            </div>
            <div class="validation"></div>
            @error('norek_bank')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Nama Pemilik /Atas Nama Bank</label>
                <input class="form-control" id="exampleFormControlInput1" name="atasnama_bank" type="text"
                    placeholder="Masukkan Nama Pemilik / Atas Nama Bank">
            </div>
            <div class="validation"></div>
            @error('atasnama_bank')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <button type="button" class="btn btn-primary" id="btnStoreBank">Simpan</button>
            <a href="{{route('bank.index')}}" class="btn btn-dark">Batal</a>
        </form>
    </div>
</div>
<script>
    $('#btnStoreBank').on('click', function () {
        const form = $('#formBankStore')[0];
        const formData = new FormData(form);

        $.ajax({
            url: "{{ route('bank.store') }}",
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                $('#btnStoreBank').prop('disabled', true).text('Submitting...');
            },
            success: function (res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message || 'Bank berhasil ditambahkan.'
                }).then(() => {
                    window.location.href = "{{ route('bank.index') }}";
                });
            },
            error: function (xhr) {
                $('#btnStoreBank').prop('disabled', false).text('Submit');

                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let list = '<ul class="text-start">';
                    $.each(errors, function (key, messages) {
                        messages.forEach(msg => {
                            list += `<li>${msg}</li>`;
                        });
                    });
                    list += '</ul>';
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validasi Gagal!',
                        html: list
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan. Silakan coba kembali.'
                    });
                }
            }
        });
    });
</script>
@endsection