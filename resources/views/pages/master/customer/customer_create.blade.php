@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Data Master Pelanggan</h6>
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
        <form id="formCustomerStore" method="POST">
            @csrf
            <div class="mb-3">
                <label for="exampleFormControlInput1">Kode Pelanggan</label>
                <input class="form-control" id="exampleFormControlInput1" name="code_cust" type="text"
                    placeholder="Masukkan Kode Pelanggan">
            </div>
            <div class="validation"></div>
            @error('code_cust')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Nama Pelanggan</label>
                <input class="form-control" id="exampleFormControlInput1" name="nama_cust" type="text"
                    placeholder="Masukkan Nama Pelanggan">
            </div>
            <div class="validation"></div>
            @error('nama_cust')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">No HP Pelanggan</label>
                <input class="form-control" id="exampleFormControlInput1" name="phone" type="number" min="0"
                    placeholder="Masukkan No HP Pelanggan">
            </div>
            <div class="validation"></div>
            @error('phone')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Email Pelanggan</label>
                <input class="form-control" id="exampleFormControlInput1" name="email" type="email"
                    placeholder="Masukkan Email Pelanggan">
            </div>
            <div class="validation"></div>
            @error('email')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Tipe Pelanggan</label>
                <select class="form-control select2" id="type_cust" name="type_cust" value="{{old('type_cust')}}"
                    required>
                    <option value="#">....</option>
                    <option value="B">Business</option>
                    <option value="C">Non Business</option>
                </select>
            </div>
            <div class="validation"></div>
            @error('type_cust')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">NPWP Pelanggan</label>
                <input class="form-control" id="exampleFormControlInput1" name="npwp_cust" type="number" max="16"
                    placeholder="Masukkan NPWP Pelanggan">
            </div>
            <div class="validation"></div>
            @error('npwp_cust')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Alamat Pelanggan</label>
                <input class="form-control" id="exampleFormControlInput1" name="address_cust" type="text"
                    placeholder="Masukkan Alamat Pelanggan">
            </div>
            <div class="validation"></div>
            @error('address_cust')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Alamat Pelanggan NPWP</label>
                <input class="form-control" id="exampleFormControlInput1" name="address_npwp" type="text"
                    placeholder="Masukkan Alamat Pelanggan NPWP">
            </div>
            <div class="validation"></div>
            @error('address_npwp')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <button type="button" class="btn btn-primary" id="btnStoreCustomer">Simpan</button>
            <a href="{{route('customers.index')}}" class="btn btn-dark">Batal</a>
        </form>
    </div>
</div>
<script>
    $('#btnStoreCustomer').on('click', function () {
        const form = $('#formCustomerStore')[0];
        const formData = new FormData(form);

        $.ajax({
            url: "{{ route('customers.store') }}",
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                $('#btnStoreCustomer').prop('disabled', true).text('Submitting...');
            },
            success: function (res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message || 'Customer berhasil ditambahkan.'
                }).then(() => {
                    window.location.href = "{{ route('customers.index') }}";
                });
            },
            error: function (xhr) {
                $('#btnStoreCustomer').prop('disabled', false).text('Submit');

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