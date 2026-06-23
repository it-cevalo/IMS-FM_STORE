@extends('layouts.admin')

@section('content')
<div class="card shadow">
    <div class="card-body">

        <h5 class="mb-3">Scan Out - {{ $tgl }}</h5>

        @php
            $totalAllItem  = 0;
            $doneAllItem   = 0;
            $totalDuplicates = 0;

            foreach ($rows as $items) {
                $totalAllItem  += $items->count();
                $doneAllItem   += $items->whereNotNull('sync_at')->count();
                $totalDuplicates += $items->where('is_duplicate_qr', true)->count();
            }

            $allDone = $totalAllItem > 0 && $totalAllItem === $doneAllItem;
        @endphp

        @if(\Session::has('error'))
            <div class="alert alert-danger alert-dismissible">
                {{ \Session::get('error') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @elseif(\Session::has('success'))
            <div class="alert alert-success alert-dismissible">
                {{ \Session::get('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        @if($totalDuplicates > 0)
        <div class="alert alert-warning d-flex align-items-start mb-3">
            <i class="fas fa-exclamation-triangle mt-1 mr-2"></i>
            <div>
                <strong>QR Code Duplikat Terdeteksi!</strong>
                Ditemukan <strong>{{ $totalDuplicates }} item</strong> dengan QR Code yang sama dalam satu DO.
                Item duplikat ditandai dengan warna kuning.
                Tolak item duplikat sebelum melakukan konfirmasi untuk menghindari pencatatan stok ganda.
            </div>
        </div>
        @endif

        <!-- ACTION BAR -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                @if($totalDuplicates > 0)
                <span class="badge badge-warning px-2 py-1">
                    <i class="fas fa-copy mr-1"></i> {{ $totalDuplicates }} QR Duplikat
                </span>
                @endif
                @if($rejectedRows->count() > 0)
                <span class="badge badge-danger px-2 py-1 ml-1">
                    <i class="fas fa-ban mr-1"></i> {{ $rejectedRows->count() }} Ditolak
                </span>
                @endif
            </div>
            <div>
                @if(!$allDone)
                    <button class="btn btn-danger mr-1" id="btnReject">
                        <i class="fas fa-times-circle"></i> Tolak
                    </button>
                    <button class="btn btn-success" id="btnConfirm">
                        <i class="fas fa-check-circle"></i> Konfirmasi
                    </button>
                @endif
                <a href="{{ route('product_outbound.index') }}" class="btn btn-dark ml-1">
                    Kembali
                </a>
            </div>
        </div>

        <!-- LEGEND -->
        <div class="mb-3 small">
            <span class="legend-box bg-warning-light border border-warning rounded px-2 py-1 mr-2">
                <i class="fas fa-copy text-warning mr-1"></i> QR Duplikat
            </span>
            <span class="legend-box bg-success rounded px-2 py-1 mr-2 text-white">
                <i class="fas fa-check mr-1"></i> Sudah Sync
            </span>
        </div>

        <!-- ACCORDION ACTIVE ITEMS -->
        <div id="accordion">

            @forelse($rows as $doId => $items)

            @php
                $isDoneDO    = $items->whereNotNull('sync_at')->count() === $items->count();
                $dupCountDo  = $items->where('is_duplicate_qr', true)->count();
            @endphp

            <div class="card mb-2 {{ $isDoneDO ? 'border-success' : ($dupCountDo > 0 ? 'border-warning' : '') }}">

                <div class="card-header d-flex align-items-center {{ $isDoneDO ? 'bg-success text-white' : '' }}">

                    <input type="checkbox" class="check-po mr-2" {{ $isDoneDO ? 'disabled' : '' }}>

                    <a data-toggle="collapse"
                       href="#do{{ $doId }}"
                       class="flex-grow-1 {{ $isDoneDO ? 'text-white' : 'text-dark' }} text-decoration-none">

                        <div class="d-flex align-items-center justify-content-between">

                            <div>
                                <b>{{ $items->first()->no_do }}</b>
                                @if($items->first()->do_source === 'CUST')
                                    <div class="small">{{ $items->first()->nama_cust }}</div>
                                @endif
                            </div>

                            <div class="text-nowrap">
                                <span class="badge {{ $isDoneDO ? 'badge-light' : 'badge-info' }}">
                                    {{ $items->count() }} Item
                                </span>
                                @if($dupCountDo > 0)
                                    <span class="badge badge-warning ml-1">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        {{ $dupCountDo }} Duplikat
                                    </span>
                                @endif
                                @if($isDoneDO)
                                    <span class="badge badge-dark ml-1">Sudah Sync</span>
                                @endif
                            </div>

                        </div>
                    </a>
                </div>

                <div id="do{{ $doId }}" class="collapse {{ $dupCountDo > 0 ? 'show' : '' }}">
                    <div class="card-body p-0">

                        @foreach($items as $item)
                        <div class="d-flex align-items-start border-bottom px-3 py-2
                                    {{ $item->is_duplicate_qr ? 'bg-warning-light' : '' }}"
                             data-qr="{{ $item->qr_code }}">

                            <input type="checkbox"
                                   class="check-item mt-1 mr-3 flex-shrink-0"
                                   value="{{ $item->id }}"
                                   data-is-duplicate="{{ $item->is_duplicate_qr ? '1' : '0' }}"
                                   data-qr="{{ $item->qr_code }}"
                                   {{ $item->sync_at ? 'disabled' : '' }}>

                            <div class="flex-grow-1">
                                <div>
                                    <b>{{ $item->SKU }}</b>
                                    <span class="text-muted">— {{ $item->nama_barang }}</span>
                                    @if($item->is_duplicate_qr)
                                        <span class="badge badge-warning ml-1">
                                            <i class="fas fa-copy"></i> QR Duplikat
                                        </span>
                                    @endif
                                </div>
                                <div class="small text-muted">
                                    <code class="{{ $item->is_duplicate_qr ? 'text-warning font-weight-bold' : '' }}">{{ $item->qr_code }}</code>
                                    &bull; Keluar: {{ $item->out_at }}
                                    &bull; Oleh: <strong>{{ $item->created_by_name ?? '-' }}</strong>
                                    @if($item->sync_at)
                                        <span class="badge badge-secondary ml-1">Disinkron {{ $item->sync_by }}</span>
                                    @endif
                                </div>
                            </div>

                            @if($item->is_duplicate_qr && !$item->sync_at)
                            <button type="button"
                                    class="btn btn-outline-danger btn-sm btn-reject-single flex-shrink-0 ml-2"
                                    data-id="{{ $item->id }}"
                                    data-qr="{{ $item->qr_code }}"
                                    title="Tolak item duplikat ini">
                                <i class="fas fa-times"></i> Tolak
                            </button>
                            @endif

                        </div>
                        @endforeach

                    </div>
                </div>

            </div>
            @empty
            <div class="alert alert-warning">
                Tidak ada data barang keluar pada tanggal ini.
            </div>
            @endforelse

        </div>

        <!-- REJECTED ITEMS SECTION -->
        @if($rejectedRows->count() > 0)
        <div class="card mt-4 border-danger">
            <div class="card-header bg-danger text-white">
                <i class="fas fa-ban mr-1"></i>
                <strong>Item Ditolak</strong>
                <span class="badge badge-light ml-1">{{ $rejectedRows->count() }}</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>QR Code</th>
                            <th>SKU / Barang</th>
                            <th>No. DO</th>
                            <th>Ditolak Oleh</th>
                            <th>Waktu</th>
                            <th>Alasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rejectedRows as $r)
                        <tr class="table-danger">
                            <td><code>{{ $r->qr_code }}</code></td>
                            <td>{{ $r->SKU }} — {{ $r->nama_barang }}</td>
                            <td>{{ $r->no_do }}</td>
                            <td>{{ $r->rejected_by_name ?? '-' }}</td>
                            <td class="small">{{ \Carbon\Carbon::parse($r->rejected_at)->format('d/m/Y H:i') }}</td>
                            <td class="small text-danger">{{ $r->reject_reason }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>
</div>

<!-- ===== REJECT MODAL ===== -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-times-circle mr-1"></i> Tolak Item
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">

                <div class="alert alert-warning" id="alertDupAutoSelected" style="display:none;">
                    <i class="fas fa-info-circle mr-1"></i>
                    Item duplikat QR Code otomatis dipilih untuk penolakan.
                </div>

                <p class="mb-1">Item yang akan ditolak: <strong id="rejectCount">0</strong> item</p>
                <div id="rejectItemList"
                     class="mb-3 border rounded p-2 small bg-light"
                     style="max-height:160px; overflow-y:auto;"></div>

                <div class="form-group mb-0">
                    <label for="rejectReason">
                        <strong>Alasan Penolakan <span class="text-danger">*</span></strong>
                    </label>
                    <textarea id="rejectReason"
                              class="form-control"
                              rows="3"
                              maxlength="500"
                              placeholder="Contoh: QR Code duplikat — barang sudah tercatat sebelumnya"></textarea>
                    <small class="text-muted">Minimal 3 karakter, maksimal 500 karakter.</small>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btnRejectSubmit">
                    <i class="fas fa-times mr-1"></i> Tolak Item
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ===== STYLES ===== --}}
<style>
    .bg-warning-light { background-color: #fff8db !important; }
    .legend-box { display: inline-block; }
</style>

{{-- ===== JS ===== --}}
<script>
// IDs yang sedang antri untuk ditolak (diset sebelum modal dibuka)
let pendingRejectIds = [];

/**
 * Buka reject modal.
 * ids            : array of string ID item
 * autoDupSelected: true jika duplikat di-auto-centang
 */
function openRejectModal(ids, autoDupSelected) {
    if (ids.length === 0) {
        Swal.fire('Perhatian', 'Pilih minimal 1 item untuk ditolak', 'warning');
        return;
    }

    pendingRejectIds = ids;

    $('#alertDupAutoSelected').toggle(autoDupSelected);
    $('#rejectCount').text(ids.length);

    let listHtml = '';
    ids.forEach(function (id) {
        let $cb  = $('.check-item[value="' + id + '"]');
        let qr   = $cb.data('qr') || '-';
        let isDup = $cb.data('is-duplicate') == '1';
        listHtml += '<div class="' + (isDup ? 'font-weight-bold text-danger' : 'text-dark') + '">'
            + (isDup ? '<i class="fas fa-exclamation-triangle mr-1"></i>' : '')
            + qr + '</div>';
    });
    $('#rejectItemList').html(listHtml);

    $('#rejectReason').val('');
    window.__bsJQuery('#rejectModal').modal('show');
}

/** CHECK DO → CHECK semua ITEM di dalamnya */
$(document).on('change', '.check-po', function () {
    let isChecked = $(this).is(':checked');
    $(this).closest('.card').find('.check-item:not(:disabled)').prop('checked', isChecked);
});

/** CHECK ITEM → sinkronisasi CHECK DO */
$(document).on('change', '.check-item', function () {
    let $card   = $(this).closest('.card');
    let total   = $card.find('.check-item:not(:disabled)').length;
    let checked = $card.find('.check-item:not(:disabled):checked').length;
    $card.find('.check-po').prop('checked', total > 0 && total === checked);
});

/** TOMBOL TOLAK (bulk) — auto-centang duplikat sebelum buka modal */
$('#btnReject').on('click', function () {
    let autoDupSelected = false;
    $('.check-item[data-is-duplicate="1"]:not(:disabled)').each(function () {
        if (!$(this).is(':checked')) {
            $(this).prop('checked', true);
            autoDupSelected = true;
        }
    });

    // Sinkron check-po header
    $('.check-po').each(function () {
        let $card   = $(this).closest('.card');
        let total   = $card.find('.check-item:not(:disabled)').length;
        let checked = $card.find('.check-item:not(:disabled):checked').length;
        $(this).prop('checked', total > 0 && total === checked);
    });

    let ids = $('.check-item:not(:disabled):checked')
        .map(function () { return $(this).val(); }).get();

    openRejectModal(ids, autoDupSelected);
});

/** TOMBOL TOLAK per-item (hanya muncul di baris duplikat) */
$(document).on('click', '.btn-reject-single', function () {
    let id = String($(this).data('id'));
    openRejectModal([id], false);
});

/** SUBMIT penolakan */
$('#btnRejectSubmit').on('click', function () {
    let reason = $('#rejectReason').val().trim();

    if (reason.length < 3) {
        Swal.fire('Perhatian', 'Alasan penolakan wajib diisi (minimal 3 karakter)', 'warning');
        return;
    }

    if (pendingRejectIds.length === 0) {
        Swal.fire('Perhatian', 'Tidak ada item yang dipilih', 'warning');
        return;
    }

    window.__bsJQuery('#rejectModal').modal('hide');

    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    $.post("{{ route('product_outbound.reject') }}", {
        _token: "{{ csrf_token() }}",
        items: pendingRejectIds,
        reject_reason: reason
    })
    .done(function (res) {
        Swal.fire('Berhasil', res.message, 'success').then(() => window.location.reload());
    })
    .fail(function (err) {
        Swal.fire('Error', err.responseJSON?.message ?? 'Terjadi kesalahan', 'error');
    });
});

/** Reset pendingRejectIds saat modal ditutup */
$(function () {
    window.__bsJQuery('#rejectModal').on('hidden.bs.modal', function () {
        pendingRejectIds = [];
    });
});

/** TOMBOL KONFIRMASI — blokir jika ada duplikat masih terceklis */
$('#btnConfirm').on('click', function () {
    let checkedDuplicates = $('.check-item[data-is-duplicate="1"]:not(:disabled):checked');

    if (checkedDuplicates.length > 0) {
        Swal.fire({
            title: 'Ada QR Code Duplikat!',
            html: '<b>' + checkedDuplicates.length + ' item duplikat</b> masih terpilih.<br>'
                + 'Tolak item duplikat terlebih dahulu sebelum konfirmasi.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-times-circle"></i> Buka Tolak',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545'
        }).then(function (res) {
            if (res.isConfirmed) $('#btnReject').click();
        });
        return;
    }

    let items = $('.check-item:not(:disabled):checked')
        .map(function () { return $(this).val(); }).get();

    if (items.length === 0) {
        Swal.fire('Perhatian', 'Pilih minimal 1 item', 'warning');
        return;
    }

    Swal.fire({
        title: 'Apakah anda yakin?',
        text: 'Barang akan dikurangi dari stok.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Konfirmasi',
        cancelButtonText: 'Batal'
    }).then(function (res) {
        if (!res.isConfirmed) return;

        Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        $.post("{{ route('product_outbound.confirm') }}", {
            _token: "{{ csrf_token() }}",
            items: items
        })
        .done(function (res) {
            Swal.fire('Berhasil', res.message, 'success')
                .then(() => { window.location.href = "{{ route('product_outbound.index') }}"; });
        })
        .fail(function (err) {
            Swal.fire('Error', err.responseJSON?.message ?? 'Terjadi kesalahan', 'error');
        });
    });
});
</script>
@endsection
