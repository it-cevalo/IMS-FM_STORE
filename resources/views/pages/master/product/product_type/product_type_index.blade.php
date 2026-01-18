@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Data Jenis Barang</h6>
        <a href="{{ route('product_type.create') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Tambah</a>
    </div>

    <div class="card-body">
        @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @elseif(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered" id="productTypeTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-center align-middle">No</th>
                        <th class="text-center align-middle">Nama Jenis Barang</th>
                        <th class="text-center align-middle">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function loadProductTypeData() {
        $('#productTypeTable').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: '{{ route('product_type.data') }}',
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'nama_tipe', name: 'nama_tipe' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    }

    $(document).ready(function () {
        loadProductTypeData();

        // Optional: auto hide alert after 5 seconds
        setTimeout(() => $('.alert').fadeOut(), 5000);
    });

    $(document).on('click', '.btnDelete', function () {
        let url = $(this).data('url');

        Swal.fire({
            title: 'Yakin ingin menghapus data ini?',
            text: 'Data yang dihapus tidak bisa dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (res) {
                        Swal.fire(
                            'Berhasil Dihapus',
                            res.message,
                            'success'
                        );

                        $('#productTypeTable').DataTable().ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        Swal.fire(
                            'Terjadi Kesalahan',
                            xhr.responseJSON?.message || 'Terjadi kesalahan sistem',
                            'error'
                        );
                    }
                });
            }
        });
    });
</script>
@endsection