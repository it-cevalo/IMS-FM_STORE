@extends('layouts.admin')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="font-weight-bold text-dark mb-0">
                <i class="fas fa-layer-group text-info mr-2"></i>Status Batch Cetak QR
            </h5>
            <small class="text-muted">PO: <strong>{{ $po->no_po ?? '#'.$id }}</strong></small>
        </div>
        <a href="{{ route('purchase_order.show', $id) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali ke PO
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- Tabel Batch --}}
    <div class="card shadow mb-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:120px">Batch</th>
                            <th>Detail SKU &amp; Sequence</th>
                            <th style="width:80px" class="text-center">Label</th>
                            <th style="width:150px">Waktu Generate</th>
                            <th style="width:140px" class="text-center">Status</th>
                            <th style="width:160px" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batches as $batch)
                        <tr id="row-batch-{{ $batch->id }}">
                            <td class="font-weight-bold">{{ $batch->batch_name }}</td>
                            <td><span class="text-muted small">{{ $batch->content_summary }}</span></td>
                            <td class="text-center">{{ $batch->total_labels }}</td>
                            <td class="small text-muted">
                                {{ \Carbon\Carbon::parse($batch->created_at)->format('d M Y H:i') }}
                            </td>
                            <td class="text-center" id="status-{{ $batch->id }}">
                                @if($batch->status === 'Pending')
                                    <span class="badge badge-secondary"><i class="fas fa-clock mr-1"></i>Menunggu</span>
                                @elseif($batch->status === 'Processing')
                                    <span class="badge badge-warning text-dark"><i class="fas fa-spinner fa-spin mr-1"></i>Diproses</span>
                                @elseif($batch->status === 'Ready')
                                    <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Siap Cetak</span>
                                @elseif($batch->status === 'Printed')
                                    <span class="badge badge-info"><i class="fas fa-check-double mr-1"></i>Sudah Dicetak</span>
                                @else
                                    <span class="badge badge-danger" title="{{ $batch->error_message }}"><i class="fas fa-times-circle mr-1"></i>Gagal</span>
                                @endif
                            </td>
                            <td class="text-center" id="action-{{ $batch->id }}">
                                @if(in_array($batch->status, ['Ready', 'Printed']))
                                    <button
                                        id="btn-batch-{{ $batch->id }}"
                                        class="btn {{ $batch->status === 'Printed' ? 'btn-warning' : 'btn-primary' }} btn-sm btn-block"
                                        onclick="validateAndPreview(
                                            '{{ asset('storage/temp_prints/'.$batch->file_path) }}',
                                            {{ $batch->id }},
                                            {{ $id }}
                                        )">
                                        <i class="fas {{ $batch->status === 'Printed' ? 'fa-redo' : 'fa-eye' }} mr-1"></i>
                                        {{ $batch->status === 'Printed' ? 'Cetak Ulang' : 'Lihat PDF' }}
                                    </button>
                                @elseif($batch->status === 'Failed')
                                    <button class="btn btn-danger btn-sm btn-block" disabled title="{{ $batch->error_message }}">
                                        <i class="fas fa-times mr-1"></i>Error
                                    </button>
                                @else
                                    <button class="btn btn-outline-secondary btn-sm btn-block" disabled>
                                        <i class="fas fa-spinner fa-spin mr-1"></i>Menunggu...
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                Belum ada antrian cetak untuk PO ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- PDF Preview Container --}}
    <div id="pdfPreviewContainer" style="display:none;" class="card shadow">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <span class="font-weight-bold text-dark">
                <i class="fas fa-file-pdf text-danger mr-2"></i>
                Preview: <span id="pdfPreviewLabel">-</span>
            </span>
            <div>
                <button class="btn btn-success btn-sm mr-1" id="btnPrintActive">
                    <i class="fas fa-print mr-1"></i>Cetak &amp; Selesai
                </button>
                <button class="btn btn-outline-secondary btn-sm" onclick="closePreview()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0 position-relative">
            <div id="pdfLoading"
                 style="display:none; position:absolute; inset:0; background:rgba(255,255,255,.85);
                        z-index:10; display:flex; align-items:center; justify-content:center;">
                <div class="text-center text-muted">
                    <i class="fas fa-spinner fa-spin fa-2x mb-2 d-block"></i>
                    Memuat PDF...
                </div>
            </div>
            <iframe id="pdfPreviewFrame"
                    style="width:100%; height:80vh; border:none; display:block;">
            </iframe>
        </div>
    </div>

</div>

<script>
(function () {
    const CSRF  = '{{ csrf_token() }}';
    const PO_ID = {{ $id }};
    let activeBatchId = null;

    // ── Validasi dulu sebelum buka PDF ──────────────────────────────────────
    window.validateAndPreview = function (url, batchId, poId) {
        const btn = document.getElementById('btn-batch-' + batchId);
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Mengecek...';
        }

        $.ajax({
            url    : `/purchase_order/${poId}/qr/batch/${batchId}/validate-view`,
            method : 'POST',
            data   : { _token: CSRF },
        })
        .done(function () {
            // Izinkan — update tombol jadi "Cetak Ulang"
            if (btn) {
                btn.disabled = false;
                btn.className = 'btn btn-warning btn-sm btn-block';
                btn.innerHTML = '<i class="fas fa-redo mr-1"></i>Cetak Ulang';
            }
            // Update badge status di tabel
            const statusCell = document.getElementById('status-' + batchId);
            if (statusCell) {
                statusCell.innerHTML = '<span class="badge badge-info"><i class="fas fa-check-double mr-1"></i>Sudah Dicetak</span>';
            }
            openPreview(url, batchId);
        })
        .fail(function (xhr) {
            if (btn) {
                btn.disabled = false;
                // Kembalikan label sesuai status saat ini
                const isPrinted = btn.className.includes('btn-warning');
                btn.innerHTML = isPrinted
                    ? '<i class="fas fa-redo mr-1"></i>Cetak Ulang'
                    : '<i class="fas fa-eye mr-1"></i>Lihat PDF';
            }

            if (xhr.status === 409 && xhr.responseJSON?.code === 'QR_ALREADY_PRINTED') {
                showReprintDialog(xhr.responseJSON.conflicts || [], PO_ID);
                return;
            }

            Swal.fire('Error', xhr.responseJSON?.error || 'Gagal memvalidasi batch.', 'error');
        });
    };

    // ── Dialog reprint request ───────────────────────────────────────────────
    function showReprintDialog(conflicts, poId) {
        const list = conflicts.map(c =>
            `• <b>${c.product_name}</b> (${c.sku}) → <b>${c.printed_range}</b>`
        ).join('<br>');

        Swal.fire({
            title : 'Cetak Ulang Diperlukan',
            html  : `
                <div style="text-align:left;font-size:14px;line-height:1.6">
                    <p><b>QR berikut sudah pernah dicetak.</b><br>
                    Ajukan <b>cetak ulang</b> dan tunggu persetujuan.</p>
                    <hr>
                    ${list}
                </div>
            `,
            icon              : 'warning',
            input             : 'textarea',
            inputPlaceholder  : 'Alasan cetak ulang (wajib)',
            showCancelButton  : true,
            confirmButtonText : 'Ajukan Cetak Ulang',
            cancelButtonText  : 'Batal',
            preConfirm: (reason) => {
                if (!reason || !reason.trim()) {
                    Swal.showValidationMessage('Alasan wajib diisi');
                    return false;
                }
                return reason;
            }
        }).then(r => {
            if (!r.isConfirmed) return;

            $.post('/qr/reprint/request', {
                id_po  : poId,
                reason : r.value,
                _token : CSRF,
                items  : conflicts,
            })
            .done(resp => {
                Swal.fire('Berhasil', resp.message || 'Pengajuan cetak ulang berhasil dikirim.', 'success');
            })
            .fail(xhr2 => {
                const msg = xhr2.responseJSON?.code === 'REPRINT_PENDING'
                    ? xhr2.responseJSON.message
                    : (xhr2.responseJSON?.message || 'Gagal mengajukan cetak ulang.');
                Swal.fire('Gagal', msg, 'error');
            });
        });
    }

    // ── Buka PDF di iframe ────────────────────────────────────────────��──────
    window.openPreview = function (url, batchId) {
        activeBatchId = batchId;

        const container = document.getElementById('pdfPreviewContainer');
        const frame     = document.getElementById('pdfPreviewFrame');
        const loading   = document.getElementById('pdfLoading');
        const label     = document.getElementById('pdfPreviewLabel');

        const row = document.getElementById('row-batch-' + batchId);
        label.textContent = row
            ? row.querySelector('td:first-child').textContent.trim()
            : 'Batch #' + batchId;

        container.style.display = 'block';
        loading.style.display   = 'flex';
        frame.src = '';

        frame.onload = function () {
            if (frame.src && frame.src !== 'about:blank') {
                loading.style.display = 'none';
            }
        };

        frame.src = url + '#toolbar=0&navpanes=0&scrollbar=0';

        setTimeout(function () {
            container.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    };

    window.closePreview = function () {
        document.getElementById('pdfPreviewContainer').style.display = 'none';
        document.getElementById('pdfPreviewFrame').src = 'about:blank';
        activeBatchId = null;
    };

    document.getElementById('btnPrintActive').addEventListener('click', function () {
        if (!activeBatchId) return;

        const frame = document.getElementById('pdfPreviewFrame');
        if (!frame.src || frame.src === 'about:blank') {
            alert('PDF belum dimuat.');
            return;
        }

        try {
            frame.contentWindow.focus();
            frame.contentWindow.onafterprint = function () {
                window.location.href = '/print-batch/' + activeBatchId;
            };
            frame.contentWindow.print();
        } catch (e) {
            window.open(frame.src, '_blank');
        }
    });

    @php $hasPending = $batches->whereIn('status', ['Pending', 'Processing'])->count(); @endphp
    @if($hasPending > 0)
    setInterval(function () {
        fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                document.querySelectorAll('[id^="status-"]').forEach(el => {
                    const newEl = doc.getElementById(el.id);
                    if (newEl) el.innerHTML = newEl.innerHTML;
                });
                if (!document.querySelector('.badge-secondary, .badge-warning')) location.reload();
            });
    }, 5000);
    @endif

}());
</script>
@endsection
