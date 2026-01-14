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
        <form id="formBankUpdate" method="POST">
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" id="bankId" value="{{ $bank->id }}">
            @csrf
            {{ method_field('PUT') }}
            <div class="mb-3">
                <label for="exampleFormControlInput1">Kode Bank</label>
                <input class="form-control" id="exampleFormControlInput1" name="code_bank" type="text"
                    value="{{$bank->code_bank}}" placeholder="Masukkan Kode Bank">
            </div>
            <div class="validation"></div>
            @error('code_bank')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Nama Bank</label>
                <input class="form-control" id="exampleFormControlInput1" name="nama_bank" type="text"
                    value="{{$bank->nama_bank}}" placeholder="Masukkan Nama bank">
            </div>
            <div class="validation"></div>
            @error('nama_bank')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Nomor Rekening Bank</label>
                <input class="form-control" id="exampleFormControlInput1" name="norek_bank" type="text"
                    value="{{$bank->norek_bank}}" placeholder="Masukkan No Rekening bank">
            </div>
            <div class="validation"></div>
            @error('norek_bank')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Nama Pemilik / Atas Nama Bank</label>
                <input class="form-control" id="exampleFormControlInput1" name="atasnama_bank" type="text"
                    value="{{$bank->atasnama_bank}}" placeholder="Masukkan Nama Pemilik / Atas Nama Bank">
            </div>
            <div class="validation"></div>
            @error('atasnama_bank')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <button type="button" class="btn btn-primary" id="btnUpdateBank">Simpan</button>
            <a href="{{route('bank.index')}}" class="btn btn-dark">Batal</a>
        </form>
    </div>
</div>
<script>
    $('#btnUpdateBank').on('click', function () {
        const id = $('#bankId').val();
        const form = $('#formBankUpdate')[0];
        const formData = new FormData(form);

        $.ajax({
            url: `/bank/${id}`,
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            beforeSend: function () {
                $('#btnUpdateBank').prop('disabled', true).text('Submitting...');
            },
            success: function (res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message || 'Bank berhasil diperbarui.'
                }).then(() => {
                    window.location.href = "{{ route('bank.index') }}";
                });
            },
            error: function (xhr) {
                $('#btnUpdateBank').prop('disabled', false).text('Submit');

                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let message = '<ul class="text-start">';
                    $.each(errors, function (key, value) {
                        value.forEach(v => {
                            message += `<li>${v}</li>`;
                        });
                    });
                    message += '</ul>';
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validasi Gagal!',
                        html: message
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan!',
                        text: 'Mohon coba beberapa saat lagi.'
                    });
                }
            }
        });
    });
</script>
@endsection