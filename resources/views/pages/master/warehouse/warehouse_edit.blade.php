@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Data Master Gudang</h6>
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
        <form id="formWarehouseUpdate" method="POST">
            @csrf
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" id="warehouseId" value="{{ $warehouses->id }}">
            {{ method_field('PUT') }}
            {{-- <div class="mb-3">
                <label for="exampleFormControlInput1">Toko</label>
                <select class="form-control select2" id="search-type" name="id_store" value="{{old('id_store')}}" required>
                    <option value="">....</option>
                    @forelse($stores as $p)
                    <option value="{{$p->id}}" @if ($warehouses->id_store == $p->id) selected @endif>{{$p->nama_store}}
                    </option>
                    @empty
                    @endforelse
                </select>
            </div>
            <div class="validation"></div>
            @error('id_store')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror --}}
            <div class="mb-3">
                <label for="exampleFormControlInput1">Kode Gudang</label>
                <input class="form-control" id="exampleFormControlInput1" name="code_wh" type="text"
                    value="{{$warehouses->code_wh}}" placeholder="Masukkan Kode Gudang">
            </div>
            <div class="validation"></div>
            @error('code_wh')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Nama Gudang</label>
                <input class="form-control" id="exampleFormControlInput1" name="nama_wh" type="text"
                    value="{{$warehouses->nama_wh}}" placeholder="Masukkan Nama Gudang">
            </div>
            <div class="validation"></div>
            @error('nama_wh')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">No HP Gudang</label>
                <input class="form-control" id="exampleFormControlInput1" name="phone" type="number" min="0"
                    value="{{$warehouses->phone}}" placeholder="Masukkan Warehouse Phone">
            </div>
            <div class="validation"></div>
            @error('phone')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Email Gudang</label>
                <input class="form-control" id="exampleFormControlInput1" name="email" type="email"
                    value="{{$warehouses->email}}" placeholder="Masukkan Warehouse Email">
            </div>
            <div class="validation"></div>
            @error('email')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Alamat Gudang</label>
                <input class="form-control" id="exampleFormControlInput1" name="address" type="text"
                    value="{{$warehouses->address}}" placeholder="Masukkan Warehouse Address">
            </div>
            <div class="validation"></div>
            @error('address')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <button type="button" class="btn btn-primary" id="btnUpdateWarehouse">Update</button>
            <a href="{{route('warehouses.index')}}" class="btn btn-dark">Batal</a>
        </form>
    </div>
</div>
<script>
    $('#btnUpdateWarehouse').on('click', function () {
        const warehouseId = $('#warehouseId').val();
        const form = $('#formWarehouseUpdate')[0];
        const formData = new FormData(form);

        $.ajax({
            url: `/warehouses/${warehouseId}`,
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            beforeSend: function () {
                $('#btnUpdateWarehouse').prop('disabled', true).text('Updating...');
            },
            success: function (res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message || 'Warehouse berhasil diperbarui.'
                }).then(() => {
                    window.location.href = "{{ route('warehouses.index') }}";
                });
            },
            error: function (xhr) {
                $('#btnUpdateWarehouse').prop('disabled', false).text('Update');

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
                        title: 'Terjadi Kesalahan!',
                        text: 'Gagal memperbarui warehouse. Silakan coba lagi.'
                    });
                }
            }
        });
    });
</script>
@endsection