@extends('layouts.admin')

@section('content')
<style>
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    @keyframes gl-indeterminate {
        0%   { left: -35%; right: 100%; }
        60%  { left: 100%;  right: -90%; }
        100% { left: 100%;  right: -90%; }
    }
    @keyframes gl-indeterminate2 {
        0%   { left: -200%; right: 100%; }
        60%  { left: 107%;  right: -8%; }
        100% { left: 107%;  right: -8%; }
    }
    @keyframes fadeInDown {
        from { opacity:0; transform:translateY(-12px); }
        to   { opacity:1; transform:translateY(0); }
    }
    @keyframes pulse-dot {
        0%, 80%, 100% { transform:scale(0.6); opacity:.4; }
        40%            { transform:scale(1);   opacity:1; }
    }

    #globalLoadingOverlay {
        position: fixed; inset: 0;
        background: rgba(15, 23, 42, 0.72);
        backdrop-filter: blur(3px);
        z-index: 99999;
        display: none;
        align-items: center;
        justify-content: center;
    }
    .gl-card {
        background: #fff;
        border-radius: 18px;
        padding: 40px 44px;
        max-width: 420px;
        width: 92%;
        text-align: center;
        box-shadow: 0 24px 60px rgba(0,0,0,.28);
        animation: fadeInDown .3s ease;
    }
    .gl-spinner-ring {
        width: 68px; height: 68px;
        border: 5px solid #e2e8f0;
        border-top-color: #3b82f6;
        border-radius: 50%;
        animation: spin .75s linear infinite;
        margin: 0 auto 22px;
    }
    .gl-progress-wrap {
        background: #f1f5f9;
        border-radius: 999px;
        height: 7px;
        overflow: hidden;
        position: relative;
        margin: 0 0 14px;
    }
    .gl-progress-bar {
        position: absolute;
        left: -35%; right: 100%;
        top: 0; bottom: 0;
        background: linear-gradient(90deg, #3b82f6, #818cf8);
        border-radius: 999px;
        animation: gl-indeterminate 2.1s cubic-bezier(.65,.815,.735,.395) infinite;
    }
    .gl-progress-bar::after {
        content: '';
        position: absolute;
        left: -200%; right: 100%;
        top: 0; bottom: 0;
        background: linear-gradient(90deg, #3b82f6, #818cf8);
        border-radius: 999px;
        animation: gl-indeterminate2 2.1s cubic-bezier(.165,.84,.44,1) infinite;
        animation-delay: 1.15s;
    }
    .gl-dots span {
        display: inline-block;
        width: 7px; height: 7px;
        border-radius: 50%;
        background: #94a3b8;
        margin: 0 3px;
        animation: pulse-dot 1.4s ease-in-out infinite;
    }
    .gl-dots span:nth-child(2) { animation-delay: .2s; }
    .gl-dots span:nth-child(3) { animation-delay: .4s; }

    /* PDF preview slide-in */
    #pdfPreviewContainer {
        animation: fadeInDown .35s ease;
    }

    /* Smooth badge transitions */
    [id^="status-"] .badge {
        transition: opacity .25s ease;
    }

    /* Reprint modal overlay polished */
    #reprintLoadingOverlay .rl-spinner {
        width: 52px; height: 52px;
        border: 4px solid #e2e8f0;
        border-top-color: #f59e0b;
        border-radius: 50%;
        animation: spin .8s linear infinite;
        margin: 0 auto 16px;
    }
    #pdfLoading .pdf-spin {
        width: 48px; height: 48px;
        border: 4px solid #e2e8f0;
        border-top-color: #3b82f6;
        border-radius: 50%;
        animation: spin .8s linear infinite;
        margin: 0 auto 12px;
    }
</style>

{{-- ═══════════════════ GLOBAL LOADING OVERLAY ═══════════════════ --}}
<div id="globalLoadingOverlay">
    <div class="gl-card">
        <div class="gl-spinner-ring"></div>
        <h5 id="glTitle" style="font-weight:700;color:#1e293b;margin-bottom:6px;font-size:17px;">Membuat QR Code...</h5>
        <p id="glSubtitle" style="color:#64748b;font-size:13.5px;margin-bottom:18px;line-height:1.5;">
            Harap tunggu, proses ini memerlukan beberapa saat.
        </p>
        <div class="gl-progress-wrap">
            <div class="gl-progress-bar"></div>
        </div>
        <div style="min-height:20px;">
            <span id="glStatusMsg" style="color:#94a3b8;font-size:12px;"></span>
        </div>
        <div class="gl-dots mt-3">
            <span></span><span></span><span></span>
        </div>
    </div>
</div>

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
                            <th style="width:170px" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batches as $batch)
                        @php $isReprint = ($batch->batch_type ?? 'regular') === 'reprint'; @endphp
                        <tr id="row-batch-{{ $batch->id }}" class="{{ $isReprint ? 'table-warning' : '' }}">
                            <td class="font-weight-bold">
                                {{ $batch->batch_name }}
                                @if($isReprint)
                                    <br><span class="badge badge-warning text-dark" style="font-size:9px">CETAK ULANG</span>
                                @endif
                            </td>
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
                                @if($isReprint)
                                    @if($batch->status === 'Failed')
                                        <span class="text-danger small" title="{{ $batch->error_message }}">
                                            <i class="fas fa-times-circle"></i> Gagal
                                        </span>
                                    @elseif($batch->status === 'Processing')
                                        <button class="btn btn-outline-warning btn-sm btn-block" disabled>
                                            <i class="fas fa-spinner fa-spin mr-1"></i>Sedang diproses...
                                        </button>
                                    @else
                                        <button
                                            class="btn btn-primary btn-sm btn-block"
                                            onclick="openReprintPreview({{ $batch->id }})">
                                            <i class="fas fa-print mr-1"></i>Preview &amp; Cetak Ulang
                                        </button>
                                    @endif
                                @else
                                    @if($batch->status === 'Pending')
                                        <button
                                            id="btn-batch-{{ $batch->id }}"
                                            class="btn btn-primary btn-sm btn-block"
                                            onclick="generateAndPreview({{ $batch->id }}, {{ $id }})">
                                            <i class="fas fa-qrcode mr-1"></i>Generate &amp; Cetak
                                        </button>
                                    @elseif($batch->status === 'Processing')
                                        <button id="btn-batch-{{ $batch->id }}" class="btn btn-outline-warning btn-sm btn-block" disabled>
                                            <i class="fas fa-spinner fa-spin mr-1"></i>Sedang diproses...
                                        </button>
                                    @elseif(in_array($batch->status, ['Ready', 'Printed']))
                                        <button
                                            id="btn-batch-{{ $batch->id }}"
                                            class="btn {{ $batch->status === 'Printed' ? 'btn-warning' : 'btn-success' }} btn-sm btn-block"
                                            onclick="validateAndPreview(
                                                '{{ asset('storage/temp_prints/'.$batch->file_path) }}',
                                                {{ $batch->id }},
                                                {{ $id }}
                                            )">
                                            <i class="fas {{ $batch->status === 'Printed' ? 'fa-redo' : 'fa-eye' }} mr-1"></i>
                                            {{ $batch->status === 'Printed' ? 'Cetak Ulang' : 'Lihat QR' }}
                                        </button>
                                    @elseif($batch->status === 'Failed')
                                        <button
                                            id="btn-batch-{{ $batch->id }}"
                                            class="btn btn-outline-danger btn-sm btn-block"
                                            onclick="generateAndPreview({{ $batch->id }}, {{ $id }})"
                                            title="{{ $batch->error_message }}">
                                            <i class="fas fa-redo mr-1"></i>Coba Lagi
                                        </button>
                                    @endif
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

    {{-- PDF Preview Container (regular batch) --}}
    <div id="pdfPreviewContainer" style="display:none;" class="card shadow">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <span class="font-weight-bold text-dark">
                <i class="fas fa-qrcode text-info mr-2"></i>
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
                 style="display:none; position:absolute; inset:0; background:rgba(255,255,255,.9);
                        z-index:10; align-items:center; justify-content:center; flex-direction:column;">
                <div class="pdf-spin"></div>
                <span style="color:#64748b;font-size:13px;">Memuat dokumen QR...</span>
            </div>
            <iframe id="pdfPreviewFrame"
                    style="width:100%; height:80vh; border:none; display:block;">
            </iframe>
        </div>
    </div>

</div>

{{-- Modal Preview Reprint (on-demand generate) --}}
<div class="modal fade" id="modalReprintPreview" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title font-weight-bold">
                    <i class="fas fa-print text-warning mr-1"></i> Preview Cetak Ulang QR
                </h6>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0 position-relative">
                <div id="reprintLoadingOverlay"
                     style="position:absolute; inset:0; background:rgba(255,255,255,.95);
                            z-index:10; display:flex; align-items:center; justify-content:center; flex-direction:column;">
                    <div class="rl-spinner"></div>
                    <div style="color:#1e293b;font-weight:600;font-size:14px;margin-bottom:4px;">Membuat PDF cetak ulang...</div>
                    <small style="color:#94a3b8;">Proses lebih lama jika jumlah QR besar</small>
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
(function () {
    const CSRF  = '{{ csrf_token() }}';
    const PO_ID = {{ $id }};
    let activeBatchId = null;

    // ── Global loading overlay helpers ───────────────────────────────────────
    const glOverlay   = document.getElementById('globalLoadingOverlay');
    const glTitle     = document.getElementById('glTitle');
    const glSubtitle  = document.getElementById('glSubtitle');
    const glStatusMsg = document.getElementById('glStatusMsg');

    const GENERATE_MESSAGES = [
        'Mempersiapkan data QR...',
        'Menghasilkan kode QR unik...',
        'Menyusun label cetak...',
        'Merender halaman PDF...',
        'Mengoptimalkan ukuran file...',
        'Hampir selesai, mohon tunggu...',
    ];

    let glMsgInterval = null;

    function showGlobalLoading(title, subtitle) {
        glTitle.textContent    = title    || 'Membuat QR Code...';
        glSubtitle.textContent = subtitle || 'Harap tunggu, proses ini memerlukan beberapa saat.';
        glStatusMsg.textContent = GENERATE_MESSAGES[0];
        glOverlay.style.display = 'flex';

        let idx = 0;
        glMsgInterval = setInterval(function () {
            idx = (idx + 1) % GENERATE_MESSAGES.length;
            glStatusMsg.textContent = GENERATE_MESSAGES[idx];
        }, 2800);

        // Prevent scroll
        document.body.style.overflow = 'hidden';
    }

    function hideGlobalLoading() {
        clearInterval(glMsgInterval);
        glMsgInterval = null;
        glOverlay.style.display = 'none';
        document.body.style.overflow = '';
    }

    // ── Reprint: buka modal, generate PDF on-demand ──────────────────────────
    window.openReprintPreview = function (batchId) {
        const frame    = document.getElementById('reprintPreviewFrame');
        const overlay  = document.getElementById('reprintLoadingOverlay');
        const btnPrint = document.getElementById('btnPrintReprint');
        const targetUrl = '/reprint/batch/' + batchId + '/preview';

        // Reset state
        overlay.style.display  = 'flex';
        btnPrint.style.display = 'none';
        frame.onload = null;
        frame.src = 'about:blank';

        // Open modal first so user gets instant feedback
        $('#modalReprintPreview').modal('show');

        // Small delay so modal animation completes before heavy iframe load
        setTimeout(function () {
            var loaded = false;
            frame.onload = function () {
                if (loaded) return;
                // about:blank fires onload too — skip it
                if (!frame.src || frame.src === 'about:blank') return;
                loaded = true;
                overlay.style.display  = 'none';
                btnPrint.style.display = 'inline-block';
            };
            frame.src = targetUrl;
        }, 250);
    };

    document.getElementById('btnPrintReprint').addEventListener('click', function () {
        const frame = document.getElementById('reprintPreviewFrame');
        try {
            frame.contentWindow.focus();
            frame.contentWindow.print();
        } catch (e) {
            window.open(frame.src, '_blank');
        }
    });

    $('#modalReprintPreview').on('hidden.bs.modal', function () {
        const frame = document.getElementById('reprintPreviewFrame');
        frame.onload = null;
        frame.src = 'about:blank';
        document.getElementById('reprintLoadingOverlay').style.display = 'flex';
        document.getElementById('btnPrintReprint').style.display = 'none';
    });

    // ── Regular batch: generate PDF on-demand lalu buka ─────────────────────
    window.generateAndPreview = function (batchId, poId) {
        const btn = document.getElementById('btn-batch-' + batchId);
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Memproses...';
        }

        const statusCell = document.getElementById('status-' + batchId);
        if (statusCell) {
            statusCell.innerHTML = '<span class="badge badge-warning text-dark"><i class="fas fa-spinner fa-spin mr-1"></i>Diproses</span>';
        }

        // Show friendly full-screen overlay
        showGlobalLoading(
            'Membuat QR Code...',
            'Harap tunggu, proses ini memerlukan beberapa saat.'
        );

        $.ajax({
            url    : `/purchase_order/${poId}/qr/batch/${batchId}/process`,
            method : 'POST',
            timeout: 180000,
            data   : { _token: CSRF },
        })
        .done(function (res) {
            hideGlobalLoading();

            if (statusCell) {
                statusCell.innerHTML = '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Siap Cetak</span>';
            }
            if (btn) {
                btn.innerHTML = '<i class="fas fa-eye mr-1"></i>Lihat QR';
            }

            // Brief success toast before opening preview
            Swal.fire({
                toast             : true,
                position          : 'top-end',
                icon              : 'success',
                title             : 'QR berhasil dibuat!',
                showConfirmButton : false,
                timer             : 1800,
                timerProgressBar  : true,
            }).then(function () {
                const fileUrl = '/storage/temp_prints/' + res.file_path;
                validateAndPreview(fileUrl, batchId, poId);
            });
        })
        .fail(function (xhr) {
            hideGlobalLoading();

            if (btn) {
                btn.disabled = false;
                btn.className = 'btn btn-outline-danger btn-sm btn-block';
                btn.innerHTML = '<i class="fas fa-redo mr-1"></i>Coba Lagi';
                btn.setAttribute('onclick', `generateAndPreview(${batchId}, ${poId})`);
            }
            if (statusCell) {
                statusCell.innerHTML = '<span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i>Gagal</span>';
            }
            Swal.fire({
                icon  : 'error',
                title : 'Gagal Membuat QR',
                text  : xhr.responseJSON?.error || 'Terjadi kesalahan saat membuat QR. Silakan coba lagi.',
                confirmButtonText: 'Tutup',
            });
        });
    };

    // ── Regular batch: validasi sebelum buka PDF ─────────────────────────────
    window.validateAndPreview = function (url, batchId, poId) {
        const btn = document.getElementById('btn-batch-' + batchId);
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Menyiapkan...';
        }

        $.ajax({
            url    : `/purchase_order/${poId}/qr/batch/${batchId}/validate-view`,
            method : 'POST',
            data   : { _token: CSRF },
        })
        .done(function () {
            if (btn) {
                btn.disabled = false;
                btn.className = 'btn btn-warning btn-sm btn-block';
                btn.innerHTML = '<i class="fas fa-redo mr-1"></i>Cetak Ulang';
                btn.setAttribute('onclick', `validateAndPreview('${url}', ${batchId}, ${poId})`);
            }
            const statusCell = document.getElementById('status-' + batchId);
            if (statusCell) {
                statusCell.innerHTML = '<span class="badge badge-info"><i class="fas fa-check-double mr-1"></i>Sudah Dicetak</span>';
            }
            openPreview(url, batchId);
        })
        .fail(function (xhr) {
            if (btn) {
                btn.disabled = false;
                const isPrinted = btn.className.includes('btn-warning');
                btn.innerHTML = isPrinted
                    ? '<i class="fas fa-redo mr-1"></i>Cetak Ulang'
                    : '<i class="fas fa-eye mr-1"></i>Lihat QR';
            }

            if (xhr.status === 409 && xhr.responseJSON?.code === 'QR_ALREADY_PRINTED') {
                showReprintDialog(xhr.responseJSON.conflicts || [], PO_ID);
                return;
            }

            Swal.fire({
                icon  : 'error',
                title : 'Validasi Gagal',
                text  : xhr.responseJSON?.error || 'Gagal memvalidasi batch.',
            });
        });
    };

    // ── Dialog reprint request ────────────────────────────────────────────────
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

            Swal.fire({
                title             : 'Mengirim pengajuan...',
                allowOutsideClick : false,
                didOpen           : () => Swal.showLoading(),
            });

            $.post('/qr/reprint/request', {
                id_po  : poId,
                reason : r.value,
                _token : CSRF,
                items  : conflicts,
            })
            .done(resp => {
                Swal.fire({
                    icon  : 'success',
                    title : 'Pengajuan Terkirim',
                    text  : resp.message || 'Pengajuan cetak ulang berhasil dikirim.',
                    timer : 2500,
                    timerProgressBar: true,
                    showConfirmButton: false,
                });
            })
            .fail(xhr2 => {
                const msg = xhr2.responseJSON?.code === 'REPRINT_PENDING'
                    ? xhr2.responseJSON.message
                    : (xhr2.responseJSON?.message || 'Gagal mengajukan cetak ulang.');
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            });
        });
    }

    // ── Regular batch: buka PDF di inline card ────────────────────────────────
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
        frame.onload = null;
        frame.src = 'about:blank';

        setTimeout(function () {
            var loaded = false;
            frame.onload = function () {
                if (loaded) return;
                if (!frame.src || frame.src === 'about:blank') return;
                loaded = true;
                loading.style.display = 'none';
            };
            frame.src = url + '#toolbar=0&navpanes=0&scrollbar=0';
        }, 100);

        setTimeout(function () {
            container.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 150);
    };

    window.closePreview = function () {
        document.getElementById('pdfPreviewContainer').style.display = 'none';
        const frame = document.getElementById('pdfPreviewFrame');
        frame.onload = null;
        frame.src = 'about:blank';
        activeBatchId = null;
    };

    document.getElementById('btnPrintActive').addEventListener('click', function () {
        if (!activeBatchId) return;

        const frame = document.getElementById('pdfPreviewFrame');
        if (!frame.src || frame.src === 'about:blank') {
            Swal.fire({ icon: 'warning', title: 'Belum Siap', text: 'QR belum selesai dimuat.', timer: 2000, showConfirmButton: false });
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

    // ── Polling jika ada batch regular yang sedang diproses ──────────────────
    @php $hasProcessing = $batches->where('status', 'Processing')->where('batch_type', 'regular')->count(); @endphp
    @if($hasProcessing > 0)
    const processingPoller = setInterval(function () {
        fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                document.querySelectorAll('[id^="status-"]').forEach(el => {
                    const newEl = doc.getElementById(el.id);
                    if (newEl) el.innerHTML = newEl.innerHTML;
                });
                document.querySelectorAll('[id^="action-"]').forEach(el => {
                    const newEl = doc.getElementById(el.id);
                    if (newEl) el.innerHTML = newEl.innerHTML;
                });
                if (!doc.querySelector('.badge-warning')) {
                    clearInterval(processingPoller);
                }
            });
    }, 4000);
    @endif

}());
</script>
@endsection
