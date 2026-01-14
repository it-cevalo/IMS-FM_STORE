@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Data Satuan Barang</h6>
        <a href="{{ route('product_unit.create') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Tambah</a>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="productUnitTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-center align-middle">No</th>
                        <th class="text-center align-middle">Nama Satuan</th>
                        <th class="text-center align-middle">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function loadProductUnitData() {
        $('#productUnitTable').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: '{{ route('product_unit.data') }}',
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: "text-center" },
                { data: 'nama_unit', name: 'nama_unit' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: "text-center" }
            ]
        });
    }

    $(document).ready(function () {
        loadProductUnitData();

        // Auto close alert
        setTimeout(() => $('.alert').fadeOut(), 5000);

        // Event delete (delegation, untuk tombol delete dinamis dari DataTables)
        $(document).on('click', '.btnDeleteProductUnit', function (e) {
            e.preventDefault();

            let form = $(this).closest('form');
            let deleteUrl = form.attr('action');

            Swal.fire({
                title: 'Yakin ingin menghapus data ini?',
                text: 'Data satuan barang yang dihapus tidak bisa dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
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
                                title: 'Berhasil Dihapus',
                                text: res.message
                            });
                            $('#productUnitTable').DataTable().ajax.reload();
                        },
                        error: function (xhr) {
                            let res = xhr.responseJSON;
                            Swal.fire({
                                icon: 'error',
                                title: 'Terjadi Kesalahan',
                                text: res && res.message ? res.message : 'An error occurred while deleting the data.'
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
