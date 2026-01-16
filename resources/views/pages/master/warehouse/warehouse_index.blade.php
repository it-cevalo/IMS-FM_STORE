@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Data Master Gudang</h6>
        <a href="{{ route('warehouses.create') }}" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> Tambah
        </a>
    </div>

    <div class="card-body">
        @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @elseif(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered" id="warehouseTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-center align-middle">No</th>
                        {{-- <th class="text-center align-middle">Toko</th> --}}
                        <th class="text-center align-middle">Kode</th>
                        <th class="text-center align-middle">Nama</th>
                        <th class="text-center align-middle">Alamat</th>
                        <th class="text-center align-middle">No HP</th>
                        <th class="text-center align-middle">Email</th>
                        <th class="text-center align-middle">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
function loadWarehouseData() {
    $('#warehouseTable').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: '{{ route('warehouses.data') }}',
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            // { data: 'store_name', name: 'store_name' },
            { data: 'code_wh', name: 'code_wh' },
            { data: 'nama_wh', name: 'nama_wh' },
            { data: 'address', name: 'address' },
            { data: 'phone', name: 'phone' },
            { data: 'email', name: 'email' },
            { data: 'action', orderable: false, searchable: false, className: 'text-center' }
        ]
    });
}

$(document).ready(function () {
    loadWarehouseData();
    setTimeout(() => $('.alert').fadeOut(), 5000);

    // Event delete (delegation)
    $(document).on('click', '.btnDeleteWarehouse', function (e) {
        e.preventDefault();

        let deleteUrl = $(this).data('url');

        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: 'Data gudang ini tidak dapat dikembalikan setelah dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: deleteUrl,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function (res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message
                        });
                        $('#warehouseTable').DataTable().ajax.reload();
                    },
                    error: function (xhr) {
                        let res = xhr.responseJSON;
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res && res.message ? res.message : 'Terjadi kesalahan saat menghapus data.'
                        });
                    }
                });
            }
        });
    });
});
</script>
@endsection
