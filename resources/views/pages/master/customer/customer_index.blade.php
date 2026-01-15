@extends('layouts.admin')

@section('content')
<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Data Master Pelanggan</h6>
        
        <a href="{{ route('customers.create') }}" class="btn btn-primary btn-flat btn-sm">
            <i class="fa fa-plus"></i> Tambah
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Tipe</th>
                        <th>Alamat Pengiriman</th>
                        <th>NPWP No</th>
                        <th>Alamat NPWP</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody> <!-- Biarkan kosong, diisi oleh AJAX -->
            </table>
        </div>
    </div>
</div>
<!-- DataTables JS (pastikan sudah include jQuery dan DataTables) -->
<script>
    let customerTable;
    
    function loadCustomerData() {
        customerTable = $('#dataTable').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: '{{ route('customers.data') }}',
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'code_cust', name: 'code_cust' },
                { data: 'nama_cust', name: 'nama_cust' },
                {
                    data: 'type_cust',
                    name: 'type_cust',
                    render: function(data) {
                        return data === 'B' ? 'Business' : 'Non Business';
                    }
                },
                { data: 'address_cust', name: 'address_cust' },
                { data: 'npwp_cust', name: 'npwp_cust' },
                { data: 'address_npwp', name: 'address_npwp' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    }
    
    $(document).ready(function () {
        loadCustomerData();
    
        // Hapus Customer
        $(document).on('click', '.btnDeleteCustomer', function (e) {
            e.preventDefault();
    
            let id = $(this).data('id');
            let deleteUrl = "{{ url('customers') }}/" + id;
    
            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: "Data customer ini tidak bisa dikembalikan setelah dihapus.",
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
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE',
                            id: id
                        },
                        success: function (res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: res.message
                            });
                            $('#dataTable').DataTable().ajax.reload(null, false); // reload tanpa reset pagination
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