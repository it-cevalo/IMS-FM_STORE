@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Data Master Toko</h6>
        <a href="{{ route('stores.create') }}" class="btn btn-primary btn-sm">
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
            <table class="table table-bordered" id="storeTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-center align-middle">No</th>
                        <th class="text-center align-middle">Code</th>
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
function loadStoreData() {
    $('#storeTable').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: '{{ route('stores.data') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: "text-center" },
            { data: 'code_store', name: 'code_store' },
            { data: 'nama_store', name: 'nama_store' },
            { data: 'address', name: 'address' },
            { data: 'phone', name: 'phone' },
            { data: 'email', name: 'email' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: "text-center" }
        ]
    });
}

$(document).ready(function () {
    loadStoreData();

    setTimeout(() => $('.alert').fadeOut(), 5000);

    // Event delete (delegation)
    $(document).on('click', '.btnDeleteStore', function (e) {
    e.preventDefault();

    let deleteUrl = $(this).data('url');

    Swal.fire({
        title: 'Yakin ingin menghapus?',
        text: 'Data toko ini tidak dapat dikembalikan setelah dihapus.',
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
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message
                    });

                    $('#storeTable').DataTable().ajax.reload(null, false);
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: xhr.responseJSON?.message ?? 'Terjadi kesalahan saat menghapus data.'
                    });
                }
            });
        }
    });
});

});
</script>
@endsection
