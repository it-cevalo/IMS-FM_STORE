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
        <form id="formCustomerUpdate" method="POST">
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" id="customersId" value="{{ $customers->id }}">
            @csrf
            {{ method_field('PUT') }}
            <div class="mb-3">
                <label for="exampleFormControlInput1">Kode Pelanggan</label>
                <input class="form-control" id="exampleFormControlInput1" name="code_cust" type="text"
                    value="{{$customers->code_cust}}" placeholder="Masukkan Kode Pelanggan">
            </div>
            <div class="validation"></div>
            @error('code_cust')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Nama Pelanggan</label>
                <input class="form-control" id="exampleFormControlInput1" name="nama_cust" type="text"
                    value="{{$customers->nama_cust}}" placeholder="Masukkan Nama Pelanggan">
            </div>
            <div class="validation"></div>
            @error('nama_cust')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">No HP Pelanggan</label>
                <input class="form-control" id="exampleFormControlInput1" name="phone" type="number" min="0"
                    value="{{$customers->phone}}" placeholder="Masukkan No HP Pelanggan">
            </div>
            <div class="validation"></div>
            @error('phone')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Email Pelanggan</label>
                <input class="form-control" id="exampleFormControlInput1" name="email" type="email"
                    value="{{$customers->email}}" placeholder="Masukkan Email Pelanggan">
            </div>
            <div class="validation"></div>
            @error('email')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Tipe Pelanggan</label>
                <select class="form-control form-control-sm" name="type_cust">
                    @foreach($type_cust as $k => $v)
                    @if($customers->type_cust == $k)
                    <option value="{{ $k }}" selected="">{{ $v }}</option>
                    @else
                    <option value="{{ $k }}">{{ $v }}</option>
                    @endif
                    @endforeach
                </select>
            </div>
            <div class="validation"></div>
            @error('type_cust')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror          
            <div class="mb-3">
                <label for="exampleFormControlInput1">NPWP Pelanggan</label>
                <input class="form-control" id="exampleFormControlInput1" name="npwp_cust" type="text"
                    value="{{$customers->npwp_cust}}" placeholder="Masukkan NPWP Pelanggan">
            </div>
            <div class="validation"></div>
            @error('npwp_cust')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Alamat Pelanggan</label>
                <input class="form-control" id="exampleFormControlInput1" name="address_cust" type="text"
                    value="{{$customers->address_cust}}" placeholder="Masukkan Alamat Pelanggan">
            </div>
            <div class="validation"></div>
            @error('address_cust')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Alamat Pelanggan NPWP</label>
                <input class="form-control" id="exampleFormControlInput1" name="address_npwp" type="text"
                    value="{{$customers->address_npwp}}" placeholder="Masukkan Alamat Pelanggan NPWP">
            </div>
            <div class="validation"></div>
            @error('address_npwp')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <button type="button" class="btn btn-primary" id="btnUpdateCustomer">Simpan</button>
            <a href="{{route('customers.index')}}" class="btn btn-dark">Batal</a>
        </form>
    </div>
</div>
<script>
    $('#btnUpdateCustomer').on('click', function () {
        const id = $('#customersId').val();
        const form = $('#formCustomerUpdate')[0];
        const formData = new FormData(form);

        $.ajax({
            url: `/customers/${id}`,
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            beforeSend: function () {
                $('#btnUpdateCustomer').prop('disabled', true).text('Submitting...');
            },
            success: function (res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message || 'Customer berhasil diperbarui.'
                }).then(() => {
                    window.location.href = "{{ route('customers.index') }}";
                });
            },
            error: function (xhr) {
                $('#btnUpdateCustomer').prop('disabled', false).text('Submit');

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