@extends('layouts.admin')

@section('content')                    
<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><a href="{{route('delivery_order.bin')}}">Stock Out BIN</a></h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="binDeliveryOrderTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kode Pemasok</th>
                        <th>Nama Pemasok</th>
                        <th>SO Number</th>
                        <th>Nomor</th>
                        <th>Nomor</th>
                        <th>Metode Pengiriman</th>
                        <th>Status</th>
                        <th>Reason</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
<script>
    function loadBinDOData() {
        $('#binDeliveryOrderTable').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: '{{ route('delivery_order.bin2.data') }}',
            columns: [
                { data: 'tgl_do' },
                { data: 'code_spl' },
                { data: 'nama_spl' },
                { data: 'no_so' },
                { data: 'no_po' },
                { data: 'no_do' },
                { data: 'shipping_via' },
                { data: 'status_lmpr_do' },
                { data: 'reason_do' },
                { data: 'action', orderable: false, searchable: false }
            ]
        });
    }
    $(document).ready(function () {
        loadBinDOData();
        $(document).on('click', '.btn-rollback', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
    
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Data akan dipulihkan kembali ke daftar utama.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, pulihkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('delivery_order.rollback.post') }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: id
                        },
                        success: function (res) {
                            Swal.fire('Berhasil', res.message, 'success');
                            $('#binDeliveryOrderTable').DataTable().ajax.reload();
                        },
                        error: function (xhr) {
                            Swal.fire('Gagal', xhr.responseJSON.message || 'Terjadi kesalahan saat rollback.', 'error');
                        }
                    });
                }
            });
        });
    });
    </script>
@endsection