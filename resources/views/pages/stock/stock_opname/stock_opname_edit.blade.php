@extends('layouts.admin')

@section('content')

@php
    $selisih      = $stock_opname->qty_last - $qrCount;
    $selisihClass = $selisih == 0 ? 'success' : ($selisih > 0 ? 'warning' : 'danger');
    $selisihLabel = $selisih == 0
        ? 'Sesuai — stok sistem cocok dengan QR aktif'
        : ($selisih > 0
            ? 'Lebih di sistem — ada ' . abs($selisih) . ' unit tercatat tapi tidak ada QR-nya'
            : 'Kurang di sistem — ada ' . abs($selisih) . ' QR aktif yang belum tercatat');
@endphp

{{-- ===== GUIDE STRIP ===== --}}
<div class="card border-0 shadow-sm mb-3" style="background:linear-gradient(90deg,#4e73df 0%,#224abe 100%);">
    <div class="card-body py-2 px-3">
        <div class="d-flex align-items-center flex-wrap text-white" style="gap:.5rem;font-size:.82rem;">
            <strong class="mr-2"><i class="fas fa-route mr-1"></i>Langkah:</strong>
            <span class="badge badge-light text-primary px-2 py-1">① Cek selisih di bawah</span>
            <i class="fas fa-chevron-right" style="font-size:.65rem;opacity:.6;"></i>
            <span class="badge badge-light text-primary px-2 py-1">② Buka riwayat QR jika ada selisih</span>
            <i class="fas fa-chevron-right" style="font-size:.65rem;opacity:.6;"></i>
            <span class="badge badge-light text-primary px-2 py-1">③ Hitung fisik barang</span>
            <i class="fas fa-chevron-right" style="font-size:.65rem;opacity:.6;"></i>
            <span class="badge badge-light text-primary px-2 py-1">④ Isi qty fisik & simpan</span>
        </div>
    </div>
</div>

{{-- ===== KONTEKS BARANG ===== --}}
<div class="card border-0 shadow-sm mb-3" style="border-left:4px solid #f6c23e !important;">
    <div class="card-body py-2 px-3">
        <div class="d-flex align-items-center flex-wrap" style="gap:.6rem;">
            <span class="text-muted small font-weight-bold text-uppercase">Sedang edit:</span>
            <span class="badge badge-light border px-2 py-1">
                <i class="fas fa-warehouse text-primary mr-1"></i>
                {{ $stock_opname->warehouse->code_wh ?? '-' }} — {{ $stock_opname->warehouse->nama_wh ?? '-' }}
            </span>
            <span class="badge badge-light border px-2 py-1">
                <i class="fas fa-box text-success mr-1"></i>
                {{ $stock_opname->product->sku ?? '-' }} — {{ $stock_opname->product->nama_barang ?? '-' }}
            </span>
        </div>
        <div class="d-flex align-items-center flex-wrap mt-2 text-muted" style="gap:.9rem;font-size:.75rem;">
            <span>
                <i class="fas fa-user-plus mr-1"></i>
                Dicatat oleh <strong>{{ $stock_opname->creator->username ?? '-' }}</strong>
                @if($stock_opname->created_at)
                    pada {{ $stock_opname->created_at->format('d/m/Y H:i') }}
                @endif
            </span>
            <span>
                <i class="fas fa-user-edit mr-1"></i>
                Terakhir diubah oleh
                @if($stock_opname->updated_by)
                    <strong>{{ $stock_opname->updater->username ?? '-' }}</strong>
                    @if($stock_opname->updated_at)
                        pada {{ $stock_opname->updated_at->format('d/m/Y H:i') }}
                    @endif
                @else
                    <strong>-</strong>
                @endif
            </span>
        </div>
    </div>
</div>

{{-- ===== PANEL PERBANDINGAN STOK ===== --}}
<div class="card shadow-sm mb-3">
    <div class="card-header py-2 bg-white">
        <span class="font-weight-bold text-primary" style="font-size:.9rem;">
            <i class="fas fa-balance-scale mr-1"></i> Perbandingan Stok
        </span>
    </div>
    <div class="card-body py-3">
        <div class="row text-center">
            <div class="col-4">
                <div class="text-xs text-muted text-uppercase font-weight-bold mb-1">Stok Sistem</div>
                <div class="h2 font-weight-bold text-primary mb-0">{{ $stock_opname->qty_last }}</div>
                <small class="text-muted">angka terakhir tercatat</small>
            </div>
            <div class="col-4" style="border-left:1px solid #e3e6f0;border-right:1px solid #e3e6f0;">
                <div class="text-xs text-muted text-uppercase font-weight-bold mb-1">QR Aktif</div>
                <div class="h2 font-weight-bold text-info mb-0">{{ $qrCount }}</div>
                <small class="text-muted">label QR belum keluar</small>
            </div>
            <div class="col-4">
                <div class="text-xs text-muted text-uppercase font-weight-bold mb-1">Selisih</div>
                <div class="h2 font-weight-bold text-{{ $selisihClass }} mb-0">
                    {{ $selisih >= 0 ? '+' : '' }}{{ $selisih }}
                </div>
                <small class="text-muted">sistem vs QR</small>
            </div>
        </div>
        <div class="mt-3 py-2 px-3 rounded d-flex align-items-start"
            style="background-color:{{ $selisih == 0 ? '#d4edda' : ($selisih > 0 ? '#fff3cd' : '#f8d7da') }};">
            <i class="fas fa-{{ $selisih == 0 ? 'check-circle text-success' : 'exclamation-triangle text-' . $selisihClass }} mr-2 mt-1"></i>
            <strong class="text-{{ $selisihClass }}" style="font-size:.85rem;">{{ $selisihLabel }}</strong>
        </div>
    </div>
</div>

{{-- ===== 2-KOLOM: FORM EDIT (kiri) | RIWAYAT (kanan) ===== --}}
<div class="row align-items-start">

    {{-- KOLOM KIRI: Form Edit --}}
    <div class="col-lg-5 mb-4">
        <div class="card shadow">
            <div class="card-header py-2">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-pencil-alt mr-1"></i> Isi Hasil Hitung Fisik
                </h6>
            </div>
            <div class="card-body">

                @if(\Session::has('error'))
                <div class="alert alert-danger alert-dismissible">
                    <span>{{ \Session::get('error') }}</span>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
                @endif

                <form action="{{ route('stock_opname.update', $stock_opname->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="font-weight-bold text-muted small text-uppercase">Gudang</label>
                        <input class="form-control bg-light" type="text"
                            value="{{ $stock_opname->warehouse->code_wh ?? '' }} {{ $stock_opname->warehouse->nama_wh ?? '' }}"
                            readonly>
                        <input type="hidden" name="id_warehouse" value="{{ $stock_opname->id_warehouse }}">
                    </div>
                    <div class="mb-3">
                        <label class="font-weight-bold text-muted small text-uppercase">Barang</label>
                        <input class="form-control bg-light" type="text"
                            value="{{ $stock_opname->product->sku ?? '' }} — {{ $stock_opname->product->nama_barang ?? '' }}"
                            readonly>
                        <input type="hidden" name="id_product" value="{{ $stock_opname->id_product }}">
                    </div>

                    <hr class="mb-3">

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="font-weight-bold small">Qty Masuk</label>
                            <input class="form-control" name="qty_in" type="number" min="0"
                                value="{{ $stock_opname->qty_in }}">
                            @error('qty_in')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="font-weight-bold small">Qty Keluar</label>
                            <input class="form-control" name="qty_out" type="number" min="0"
                                value="{{ $stock_opname->qty_out }}">
                            @error('qty_out')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="font-weight-bold text-primary">
                            Qty Fisik Akhir <span class="text-danger">*</span>
                        </label>
                        <input class="form-control form-control-lg border-primary" id="inputQtyLast"
                            name="qty_last" type="number" min="0" value="{{ $stock_opname->qty_last }}">
                        <small id="qrHint" class="font-weight-bold mt-1 d-block"></small>
                        @error('qty_last')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="font-weight-bold small">Tanggal Opname <span class="text-danger">*</span></label>
                        <input class="form-control" name="tgl_opname" type="date"
                            value="{{ $stock_opname->tgl_opname }}" required>
                        @error('tgl_opname')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex" style="gap:.5rem;">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save mr-1"></i> Simpan Perubahan
                        </button>
                    </div>
                    <div class="mt-2">
                        <a href="{{ route('stock_opname.index') }}" class="btn btn-outline-secondary btn-sm btn-block">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- KOLOM KANAN: Riwayat QR --}}
    <div class="col-lg-7 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header py-2 bg-light d-flex align-items-center justify-content-between flex-wrap" style="gap:.4rem;">
                <span class="font-weight-bold" style="font-size:.9rem;">
                    <i class="fas fa-history text-primary mr-1"></i>
                    Riwayat Alur Barang — <code>{{ $stock_opname->product->sku ?? '-' }}</code>
                </span>
                <div id="qrSummaryPills" class="d-flex align-items-center" style="gap:.3rem;">
                    <span class="badge badge-secondary">Memuat...</span>
                </div>
            </div>
            <div class="card-body p-0 d-flex flex-column">

                {{-- Loading --}}
                <div id="qrLoading" class="text-center py-5">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="ml-2 text-muted small">Memuat riwayat...</span>
                </div>

                {{-- Content --}}
                <div id="qrContent" style="display:none;" class="d-flex flex-column flex-grow-1">
                    <div class="px-3 pt-3 pb-2">
                        <input type="text" id="qrSearch" class="form-control form-control-sm"
                            placeholder="Cari seq, QR code, no PO, no DO, customer...">
                    </div>
                    {{-- Scrollable list --}}
                    <div id="qrTimelineList" class="px-3 flex-grow-1"
                        style="overflow-y:auto;max-height:420px;"></div>
                    {{-- Pagination footer --}}
                    <div class="px-3 py-2 border-top bg-light d-flex align-items-center justify-content-between flex-wrap" style="gap:.4rem;">
                        <div class="text-muted" style="font-size:.78rem;">
                            <span id="qrPageFrom">0</span>–<span id="qrPageTo">0</span>
                            dari <span id="qrTotal">0</span> QR
                        </div>
                        <div class="d-flex align-items-center" style="gap:.4rem;">
                            <select id="qrPageSize" class="form-control form-control-sm" style="width:auto;">
                                <option value="10" selected>10 / hal</option>
                                <option value="25">25 / hal</option>
                                <option value="50">50 / hal</option>
                            </select>
                            <nav><ul class="pagination pagination-sm mb-0" id="qrPagination"></ul></nav>
                        </div>
                    </div>
                </div>

                {{-- Error --}}
                <div id="qrError" style="display:none;" class="text-center py-5 text-danger small">
                    <i class="fas fa-exclamation-circle mr-1"></i> Gagal memuat data. Coba refresh halaman.
                </div>
            </div>
        </div>
    </div>


</div>{{-- end row --}}

<script>
(function () {
    // ===== QTY HINT =====
    var qrCount = {{ $qrCount }};
    var input   = document.getElementById('inputQtyLast');
    var hint    = document.getElementById('qrHint');

    function updateHint() {
        var val  = parseInt(input.value) || 0;
        var diff = val - qrCount;
        if (diff === 0) {
            hint.textContent = 'Cocok dengan QR aktif (' + qrCount + ')';
            hint.style.color = '#1cc88a';
        } else if (diff > 0) {
            hint.textContent = 'Lebih ' + diff + ' dari QR aktif (' + qrCount + ')';
            hint.style.color = '#f6c23e';
        } else {
            hint.textContent = 'Kurang ' + Math.abs(diff) + ' dari QR aktif (' + qrCount + ')';
            hint.style.color = '#e74a3b';
        }
    }
    input.addEventListener('input', updateHint);
    updateHint();

    // ===== QR HISTORY =====
    var allRows      = null; // null = belum fetch, [] = sudah fetch tapi kosong
    var filteredRows = [];
    var currentPage  = 1;
    var pageSize     = 10;

    function fmt(dt) {
        if (!dt) return null;
        var d = new Date(dt);
        var pad = function(n){ return ('0'+n).slice(-2); };
        return d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate());
    }

    function statusInfo(status) {
        var map = {
            'NEW':      { color:'#858796', icon:'fa-clock',       label:'Belum Diterima' },
            'RECEIVED': { color:'#1cc88a', icon:'fa-check-circle', label:'Aktif di Gudang' },
            'OUT':      { color:'#e74a3b', icon:'fa-sign-out-alt', label:'Sudah Keluar' }
        };
        return map[status] || { color:'#858796', icon:'fa-question-circle', label: status || '-' };
    }

    function buildTimeline(r) {
        var si      = statusInfo(r.status);
        var isRetur = r.no_do && r.no_do.toString().indexOf('RT-DO') === 0;

        var masukLine = '';
        if (r.received_at) {
            masukLine = '<span class="text-success"><i class="fas fa-arrow-circle-down mr-1"></i><strong>Masuk</strong></span>'
                + ' ' + fmt(r.received_at);
            if (r.inbound_source === 'SALDO_AWAL') {
                masukLine += ' <span class="badge badge-secondary ml-1">Saldo Awal</span>';
            } else if (r.no_po) {
                masukLine += ' &middot; <span class="text-muted">' + r.no_po + '</span>';
                if (r.po_supplier_name) masukLine += ' <small class="text-muted">(' + r.po_supplier_name + ')</small>';
            }
        } else {
            masukLine = '<span class="text-muted"><i class="fas fa-clock mr-1"></i>Belum diterima</span>';
        }

        var keluarLine = '';
        if (r.out_at) {
            if (isRetur) {
                keluarLine = '<span class="text-danger"><i class="fas fa-undo mr-1"></i><strong>Retur ke Supplier</strong></span>'
                    + ' ' + fmt(r.out_at);
                if (r.no_do) keluarLine += ' &middot; <span class="text-muted">' + r.no_do + '</span>';
                if (r.do_supplier_name) keluarLine += ' <small class="text-muted">(' + r.do_supplier_name + ')</small>';
            } else {
                keluarLine = '<span class="text-danger"><i class="fas fa-arrow-circle-up mr-1"></i><strong>Keluar</strong></span>'
                    + ' ' + fmt(r.out_at);
                if (r.no_do) keluarLine += ' &middot; <span class="text-muted">' + r.no_do + '</span>';
                if (r.nama_cust) keluarLine += ' <small class="text-muted">(' + r.nama_cust + ')</small>';
            }
        } else if (r.retur_cust_at) {
            keluarLine = '<span class="text-warning"><i class="fas fa-undo mr-1"></i><strong>Retur dari Customer</strong></span>'
                + ' ' + fmt(r.retur_cust_at);
        }

        return '<div class="d-flex align-items-start py-2 border-bottom" style="gap:.75rem;">'
            // Status badge column
            + '<div class="text-center" style="min-width:80px;">'
            + '<i class="fas ' + si.icon + ' fa-lg" style="color:' + si.color + ';"></i>'
            + '<div style="font-size:.65rem;color:' + si.color + ';font-weight:600;line-height:1.2;margin-top:2px;">'
            + si.label + '</div>'
            + '</div>'
            // Info column
            + '<div class="flex-grow-1">'
            + '<div style="font-size:.78rem;">'
            + '<strong class="text-muted mr-2">#' + (r.sequence_no || '?') + '</strong>'
            + '<code style="font-size:.68rem;color:#555;">' + (r.qr_code || '-') + '</code>'
            + '</div>'
            + '<div style="font-size:.8rem;margin-top:3px;">' + masukLine + '</div>'
            + (keluarLine ? '<div style="font-size:.8rem;margin-top:2px;">' + keluarLine + '</div>' : '')
            + '</div>'
            + '</div>';
    }

    function renderPage() {
        var list  = document.getElementById('qrTimelineList');
        var total = filteredRows.length;
        var pages = Math.ceil(total / pageSize) || 1;
        if (currentPage > pages) currentPage = pages;

        var from = total === 0 ? 0 : (currentPage - 1) * pageSize + 1;
        var to   = Math.min(currentPage * pageSize, total);

        document.getElementById('qrTotal').textContent   = total;
        document.getElementById('qrPageFrom').textContent = from;
        document.getElementById('qrPageTo').textContent   = to;

        if (!total) {
            list.innerHTML = '<div class="text-center text-muted py-4 small">Tidak ada data QR ditemukan</div>';
            document.getElementById('qrPagination').innerHTML = '';
            return;
        }

        list.innerHTML = filteredRows.slice(from - 1, to).map(buildTimeline).join('');

        var maxBtn = 5;
        var half   = Math.floor(maxBtn / 2);
        var start  = Math.max(1, currentPage - half);
        var end    = Math.min(pages, start + maxBtn - 1);
        if (end - start + 1 < maxBtn) start = Math.max(1, end - maxBtn + 1);

        var html = '<li class="page-item' + (currentPage === 1 ? ' disabled' : '') + '">'
                 + '<a class="page-link" href="#" data-page="' + (currentPage-1) + '">&laquo;</a></li>';
        for (var p = start; p <= end; p++) {
            html += '<li class="page-item' + (p === currentPage ? ' active' : '') + '">'
                  + '<a class="page-link" href="#" data-page="' + p + '">' + p + '</a></li>';
        }
        html += '<li class="page-item' + (currentPage === pages ? ' disabled' : '') + '">'
              + '<a class="page-link" href="#" data-page="' + (currentPage+1) + '">&raquo;</a></li>';
        document.getElementById('qrPagination').innerHTML = html;
    }

    function applyFilter() {
        var kw = document.getElementById('qrSearch').value.trim().toLowerCase();
        filteredRows = kw
            ? allRows.filter(function(r) {
                return (r.qr_code || '').toLowerCase().includes(kw)
                    || (r.no_po || '').toLowerCase().includes(kw)
                    || (r.no_do || '').toLowerCase().includes(kw)
                    || (r.nama_cust || '').toLowerCase().includes(kw)
                    || (r.po_supplier_name || '').toLowerCase().includes(kw)
                    || (r.sequence_no || '').toString().includes(kw);
              })
            : allRows.slice();
        currentPage = 1;
        renderPage();
    }

    function updateSummaryPills(rows) {
        var counts = { NEW: 0, RECEIVED: 0, OUT: 0 };
        rows.forEach(function(r) { if (counts[r.status] !== undefined) counts[r.status]++; });
        var aktif = counts['NEW'] + counts['RECEIVED'];
        document.getElementById('qrSummaryPills').innerHTML =
            '<span class="badge badge-success px-2"><i class="fas fa-check mr-1"></i>Aktif: ' + aktif + '</span>'
            + '<span class="badge badge-danger px-2"><i class="fas fa-sign-out-alt mr-1"></i>Keluar: ' + counts['OUT'] + '</span>';
    }

    function loadHistory() {
        if (allRows !== null) {
            // sudah pernah fetch, langsung tampilkan
            document.getElementById('qrLoading').style.display  = 'none';
            document.getElementById('qrContent').style.display  = '';
            filteredRows = allRows.slice();
            currentPage  = 1;
            renderPage();
            return;
        }

        document.getElementById('qrLoading').style.display = '';
        document.getElementById('qrContent').style.display = 'none';
        document.getElementById('qrError').style.display   = 'none';

        fetch('{{ route("stock_opname.qr_history") }}?sku={{ urlencode($stock_opname->product->sku ?? "") }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            allRows      = Array.isArray(data) ? data : [];
            filteredRows = allRows.slice();
            currentPage  = 1;
            updateSummaryPills(allRows);
            document.getElementById('qrLoading').style.display = 'none';
            document.getElementById('qrContent').style.display = '';
            renderPage();
        })
        .catch(function() {
            document.getElementById('qrLoading').style.display = 'none';
            document.getElementById('qrError').style.display   = '';
        });
    }

    // Pagination clicks
    document.getElementById('qrPagination').addEventListener('click', function(e) {
        e.preventDefault();
        var a = e.target.closest('a[data-page]');
        if (!a) return;
        var pg    = parseInt(a.getAttribute('data-page'));
        var pages = Math.ceil(filteredRows.length / pageSize) || 1;
        if (pg < 1 || pg > pages) return;
        currentPage = pg;
        renderPage();
    });

    document.getElementById('qrPageSize').addEventListener('change', function() {
        pageSize    = parseInt(this.value);
        currentPage = 1;
        renderPage();
    });

    document.getElementById('qrSearch').addEventListener('input', function() {
        applyFilter();
    });

    // Auto-load on page open
    loadHistory();
})();
</script>
@endsection
