@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Ajukan Ulang Cetak QR</h6>
        <a href="{{ route('purchase_order.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
    </div>
    <div class="card-body">
        @foreach($requestsGrouped as $poNo => $items)
        <h5 class="mt-3">PO: {{ $poNo }} ({{ $items[0]->tgl_po }})</h5>

        {{-- Bulk Action Buttons --}}
        <div class="mb-2" id="bulk-actions-{{ $poNo }}">
            <button class="btn btn-success btn-sm" onclick="bulkApprove('{{ $poNo }}')">Setujui</button>
            <button class="btn btn-danger btn-sm" onclick="bulkReject('{{ $poNo }}')">Tolak</button>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th><input type="checkbox" class="check-all" data-po="{{ $poNo }}"></th>
                    <th>SKU</th>
                    <th>Nama Barang</th>
                    <th>No Urut</th>
                    <th>Alasan</th>
                    <th>Status</th>
                    <th>Tanggal Pengajuan</th>
                    <th>Yang Mengajukan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $r)
                <tr id="row-{{ $r->request_id }}">
                    <td>
                        @if($r->status == 'PENDING')
                        <input type="checkbox" class="check-item" data-po="{{ $poNo }}" data-id="{{ $r->request_id }}">
                        @endif
                    </td>
                    <td>{{ $r->part_number }}</td>
                    <td>{{ $r->product_name }}</td>
                    <td>{{ $r->sequence_no }}</td>
                    <td>{{ $r->reason }}</td>
                    <td id="status-{{ $r->request_id }}">{{ $r->status }}</td>
                    <td>{{ $r->requested_at }}</td>
                    <td>{{ $r->requested_by }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endforeach
    </div>
</div>

<script>
$(document).ready(function () {
    // Check-all toggle
    $('.check-all').change(function () {
        var poNo = $(this).data('po');
        $('input.check-item[data-po="' + poNo + '"]').prop('checked', $(this).is(':checked'));
    });
    $('.check-item').change(function () {
        var poNo = $(this).data('po');
        var all  = $('input.check-item[data-po="' + poNo + '"]');
        $('input.check-all[data-po="' + poNo + '"]').prop('checked', all.length === all.filter(':checked').length);
    });

    // Sembunyikan tombol aksi jika tidak ada item PENDING
    $('[id^="bulk-actions-"]').each(function () {
        var poNo = $(this).attr('id').replace('bulk-actions-', '');
        if ($('input.check-item[data-po="' + poNo + '"]').length === 0) {
            $(this).hide();
        }
    });
});

function hideButtonsIfAllProcessed(poNo) {
    if ($('input.check-item[data-po="' + poNo + '"]').length === 0) {
        $('#bulk-actions-' + poNo).hide();
    }
}

function bulkAction(poNo, action) {
    var ids = [];
    $('input.check-item[data-po="' + poNo + '"]:checked').each(function () {
        ids.push($(this).data('id'));
    });

    if (ids.length === 0) {
        Swal.fire('Oops', 'Pilih minimal 1 barang.', 'warning');
        return;
    }

    Swal.fire({
        title            : action === 'approve' ? 'Proses Persetujuan...' : 'Proses Menolak...',
        allowOutsideClick: false,
        didOpen: function () {
            Swal.showLoading();
            $.post(
                action === 'approve' ? '{{ route("reprint.approve") }}' : '{{ route("reprint.reject") }}',
                { _token: '{{ csrf_token() }}', ids: ids },
                function (res) {
                    Swal.close();

                    if (!res.success) {
                        Swal.fire('Error', 'Operasi gagal.', 'error');
                        return;
                    }

                    if (action === 'approve') {
                        // Langsung pindah ke print-status agar bisa cetak
                        Swal.fire({
                            icon             : 'success',
                            title            : 'Disetujui!',
                            text             : 'Mengarahkan ke halaman cetak...',
                            timer            : 1500,
                            showConfirmButton: false,
                        }).then(function () {
                            window.location.href = res.redirect_url;
                        });
                    } else {
                        ids.forEach(function (id) {
                            $('#status-' + id).text('REJECTED');
                            $('#row-' + id + ' input.check-item').remove();
                        });
                        hideButtonsIfAllProcessed(poNo);
                        Swal.fire('Ditolak', 'Pengajuan berhasil ditolak.', 'success');
                    }
                }
            ).fail(function () {
                Swal.close();
                Swal.fire('Error', 'Request gagal.', 'error');
            });
        }
    });
}

function bulkApprove(poNo) { bulkAction(poNo, 'approve'); }
function bulkReject(poNo)  { bulkAction(poNo, 'reject');  }
</script>
@endsection
