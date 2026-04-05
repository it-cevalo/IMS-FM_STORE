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

        {{-- Notifikasi + tombol preview muncul setelah approve (diisi JS) --}}
        <div id="reprint-notice-{{ $poNo }}" class="mb-2" style="display:none;"></div>

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

{{-- Modal Preview PDF --}}
<div class="modal fade" id="modalReprintPreview" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title font-weight-bold">
                    <i class="fas fa-qrcode text-info mr-1"></i> Preview Cetak Ulang QR
                </h6>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0 position-relative">
                {{-- Loading overlay —tampil selama iframe memuat PDF --}}
                <div id="previewLoadingOverlay"
                     style="position:absolute; inset:0; background:rgba(255,255,255,.9);
                            z-index:10; display:flex; align-items:center; justify-content:center;">
                    <div class="text-center text-muted">
                        <i class="fas fa-spinner fa-spin fa-2x mb-2 d-block"></i>
                        <div>Membuat PDF, harap tunggu...</div>
                        <small class="text-muted">Proses lebih lama jika QR banyak</small>
                    </div>
                </div>
                <iframe id="reprintPreviewFrame"
                        style="width:100%; height:80vh; border:none; display:block;">
                </iframe>
            </div>
            <div class="modal-footer py-2">
                <button class="btn btn-success btn-sm" id="btnPrintReprint" style="display:none;">
                    <i class="fas fa-print mr-1"></i>Cetak
                </button>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>
        </div>
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

    // Cetak dari modal
    $('#btnPrintReprint').on('click', function () {
        var frame = document.getElementById('reprintPreviewFrame');
        try {
            frame.contentWindow.focus();
            frame.contentWindow.print();
        } catch (e) {
            window.open(frame.src, '_blank');
        }
    });

    // Reset modal saat ditutup
    $('#modalReprintPreview').on('hidden.bs.modal', function () {
        document.getElementById('reprintPreviewFrame').src = 'about:blank';
        document.getElementById('previewLoadingOverlay').style.display = 'flex';
        document.getElementById('btnPrintReprint').style.display = 'none';
    });
});

function hideButtonsIfAllProcessed(poNo) {
    if ($('input.check-item[data-po="' + poNo + '"]').length === 0) {
        $('#bulk-actions-' + poNo).hide();
    }
}

// Buka modal preview — PDF di-generate on-demand di server
function openReprintPreview(batchId) {
    var frame   = document.getElementById('reprintPreviewFrame');
    var overlay = document.getElementById('previewLoadingOverlay');
    var btnPrint = document.getElementById('btnPrintReprint');

    overlay.style.display  = 'flex';
    btnPrint.style.display = 'none';
    frame.src = 'about:blank';

    frame.onload = function () {
        if (frame.src && frame.src !== 'about:blank') {
            overlay.style.display  = 'none';
            btnPrint.style.display = 'inline-block';
        }
    };

    frame.src = '/reprint/batch/' + batchId + '/preview';
    $('#modalReprintPreview').modal('show');
}

// Bulk action: approve / reject
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

                    // Update label status di tabel
                    ids.forEach(function (id) {
                        $('#status-' + id).text(action === 'approve' ? 'APPROVED' : 'REJECTED');
                        $('#row-' + id + ' input.check-item').remove();
                    });
                    hideButtonsIfAllProcessed(poNo);

                    if (action === 'approve') {
                        // Tampilkan tombol Preview Cetak langsung — PDF dibuat saat diklik
                        $('#reprint-notice-' + poNo).show().html(
                            '<div class="alert alert-success py-2 d-flex align-items-center justify-content-between">' +
                            '<span><i class="fas fa-check-circle mr-1"></i>' + (res.message || 'Disetujui.') + '</span>' +
                            '<button class="btn btn-primary btn-sm ml-3" onclick="openReprintPreview(' + res.batch_id + ')">' +
                            '<i class="fas fa-eye mr-1"></i>Preview Cetak' +
                            '</button>' +
                            '</div>'
                        );
                        Swal.fire({
                            icon             : 'success',
                            title            : 'Disetujui',
                            text             : res.message || 'Klik "Preview Cetak" untuk mencetak.',
                            confirmButtonText: 'OK',
                        });
                    } else {
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
