@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Master Data Courier</h6>
        <a href="{{ route('couriers.create') }}" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> Add
        </a>
    </div>

    <div class="card-body">
        @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @elseif(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered" id="courierTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-center align-middle">No</th>
                        <th class="text-center align-middle">Courier Code</th>
                        <th class="text-center align-middle">Courier Name</th>
                        <th class="text-center align-middle">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
function loadCourierData() {
    $('#courierTable').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: '{{ route('couriers.data') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: "text-center" },
            { data: 'code_courier', name: 'code_courier' },
            { data: 'nama_courier', name: 'nama_courier' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: "text-center" }
        ]
    });
}

$(document).ready(function () {
    loadCourierData();

    // Auto fade alert
    setTimeout(() => $('.alert').fadeOut(), 5000);

    // Event delete (delegation karena tombol delete dinamis dari DataTables)
    $(document).on('click', '.btnDeleteCourier', function (e) {
        e.preventDefault();

        let form = $(this).closest('form');
        let deleteUrl = form.attr('action');

        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: "Data kurir ini tidak bisa dikembalikan setelah dihapus.",
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
                        $('#courierTable').DataTable().ajax.reload();
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
