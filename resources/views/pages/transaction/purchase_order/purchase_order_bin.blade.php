@extends('layouts.admin')

@section('content')

<!-- DataTables Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <a href="{{route('purchase_order.bin')}}">Pemesanan Barang BIN</a>
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="binPurchaseOrderTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th rowspan="2" class="text-center align-middle">No</th>
                        <th rowspan="2" class="text-center align-middle">Tanggal</th>
                        <th colspan="2" class="text-center">Pemasok</th>
                        <th rowspan="2" class="text-center align-middle">Nomor</th>
                        <th rowspan="2" class="text-center align-middle">Status PO</th>
                        <th rowspan="2" class="text-center align-middle">Reason</th>
                        <th rowspan="2" class="text-center align-middle">Aksi</th>
                    </tr>
                    <tr>
                        <th class="text-center">Code</th>
                        <th class="text-center">Nama</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        loadBinPurchaseOrderData();
        // Re-bind rollback button event setiap kali datatable selesai render
        $('#binPurchaseOrderTable').on('draw.dt', function () {
            bindRollbackButtons();
        });
    });

    function loadBinPurchaseOrderData() {
        $('#binPurchaseOrderTable').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: '{{ route('purchase_order.bin.data') }}',
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'tgl_po', name: 'tgl_po' },
                { data: 'code_spl', name: 'supplier.code_spl' },
                { data: 'nama_spl', name: 'supplier.nama_spl' },
                { data: 'no_po', name: 'no_po' },
                { data: 'status_po', name: 'status_po' },
                { data: 'reason_po', name: 'reason_po' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ]
        });
    }

    function bindRollbackButtons() {
        $('.show-alert-rollback-box').off('click').on('click', function (e) {
            e.preventDefault();
            const id = $(this).data('id');

            Swal.fire({
                title: 'Yakin ingin rollback?',
                text: 'Data akan dikembalikan dari arsip.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, rollback',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Tampilkan loading screen
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Mohon tunggu sebentar.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ route('purchase_order.rollback') }}",
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: id
                        },
                        success: function (response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                $('#binPurchaseOrderTable').DataTable().ajax.reload();
                            });
                        },
                        error: function (xhr) {
                            let message = 'Terjadi kesalahan tidak diketahui.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: message
                            });
                        }
                    });
                }
            });
        });
    }
</script>
@endsection