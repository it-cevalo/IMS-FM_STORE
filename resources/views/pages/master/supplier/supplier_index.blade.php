@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Data Master Pemasok</h6>
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> Tambah
        </a>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="supplierTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Nama PIC</th>
                        <th>No HP PIC</th>
                        <th>Email PIC</th>
                        <th>Alamat Pengiriman</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
function loadSupplierData() {
    $('#supplierTable').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: '{{ route('suppliers.data') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'code_spl', name: 'code_spl' },
            { data: 'nama_spl', name: 'nama_spl' },
            { data: 'name_pic', name: 'name_pic' },
            { data: 'phone_pic', name: 'phone_pic' },
            { data: 'email_pic', name: 'email_pic' },
            { data: 'address_spl', name: 'address_spl' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });
}

$(document).ready(function () {
    loadSupplierData();

    $(document).on('click', '.btnDeleteSupplier', function (e) {
        e.preventDefault();
        let form = $(this).closest('form');
        let deleteUrl = form.attr('action');

        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: 'Data pemasok ini tidak dapat dikembalikan setelah dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: deleteUrl,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    success: function (res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message
                        });
                        $('#supplierTable').DataTable().ajax.reload();
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