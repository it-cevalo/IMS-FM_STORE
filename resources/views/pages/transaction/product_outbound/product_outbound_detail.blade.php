@extends('layouts.admin')

@section('content')
<div class="card shadow">
    <div class="card-body">

        <h5 class="mb-3">Scan Out - {{ $tgl }}</h5>

        @php
            // ===============================
            // GLOBAL CHECK: APAKAH SEMUA OUTBOUND SUDAH SYNC
            // ===============================
            $totalAllItem = 0;
            $doneAllItem  = 0;

            foreach ($rows as $items) {
                $totalAllItem += $items->count();
                $doneAllItem  += $items->whereNotNull('sync_at')->count();
            }

            $allDone = $totalAllItem > 0 && $totalAllItem === $doneAllItem;
        @endphp

        <!-- ACTION BAR -->
        <div class="row mb-3">
            <div class="col-md-12 text-right">

                @if(!$allDone)
                    <button class="btn btn-success" id="btnConfirm">
                        Konfirmasi
                    </button>
                @endif

                <a href="{{ route('product_outbound.index') }}" class="btn btn-dark">
                    Kembali
                </a>
            </div>
        </div>

        <!-- ACCORDION -->
        <div id="accordion">

            @forelse($rows as $doId => $items)

            @php
                $isDoneDO = $items->whereNotNull('sync_at')->count() === $items->count();
            @endphp

            <div class="card mb-2 {{ $isDoneDO ? 'border-success' : '' }}">

                <div class="card-header d-flex align-items-center
                    {{ $isDoneDO ? 'bg-success text-white' : '' }}">

                    <input type="checkbox"
                           class="check-po mr-2"
                           {{ $isDoneDO ? 'disabled' : '' }}>

                        <a data-toggle="collapse"
                           href="#do{{ $doId }}"
                           class="flex-grow-1 {{ $isDoneDO ? 'text-white' : 'text-dark' }}">
                        
                            <div class="d-flex align-items-center justify-content-between">
                        
                                {{-- KIRI: NO DO + CUSTOMER --}}
                                <div>
                                    <b>{{ $items->first()->no_do }}</b>
                        
                                    @if($items->first()->do_source === 'CUST')
                                        <div class="text medium">
                                            {{ $items->first()->nama_cust }}
                                        </div>
                                    @endif
                                </div>
                        
                                {{-- KANAN: BADGE ITEM --}}
                                <div class="text-nowrap">
                                    <span class="badge {{ $isDoneDO ? 'badge-light' : 'badge-info' }}">
                                        {{ $items->count() }} Item
                                    </span>
                        
                                    @if($isDoneDO)
                                        <span class="badge badge-dark ml-2">
                                            Sudah Sync
                                        </span>
                                    @endif
                                </div>
                        
                            </div>
                        </a>
                </div>

                <div id="do{{ $doId }}" class="collapse">
                    <div class="card-body p-2">

                        @foreach($items as $item)
                        <div class="d-flex align-items-center border-bottom py-2">
                            <input type="checkbox"
                                   class="check-item mr-3"
                                   value="{{ $item->id }}"
                                   {{ $item->sync_at ? 'disabled' : '' }}>

                            <div>
                                <b>{{ $item->SKU }}</b>
                                - {{ $item->nama_barang }}
                                - {{ $item->qr_code }}<br>
                                Keluar: {{ $item->out_at }}

                                @if($item->sync_at)
                                    <span class="badge badge-secondary ml-2">
                                        Disinkron {{ $item->sync_by }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        @endforeach

                    </div>
                </div>

            </div>
            @empty
            <div class="alert alert-warning">
                Tidak ada data barang keluar pada tanggal ini
            </div>
            @endforelse

        </div>

    </div>
</div>

{{-- ================= JS ================= --}}
<script>
/** CHECK DO → CHECK ITEM */
$(document).on('change', '.check-po', function () {
    let isChecked = $(this).is(':checked');

    $(this)
        .closest('.card')
        .find('.check-item:not(:disabled)')
        .prop('checked', isChecked);
});

/** CHECK ITEM → SYNC CHECK DO */
$(document).on('change', '.check-item', function () {

    let $card = $(this).closest('.card');

    let totalItem   = $card.find('.check-item:not(:disabled)').length;
    let checkedItem = $card.find('.check-item:not(:disabled):checked').length;

    if (totalItem > 0 && totalItem === checkedItem) {
        $card.find('.check-po').prop('checked', true);
    } else {
        $card.find('.check-po').prop('checked', false);
    }
});

/** CONFIRM OUTBOUND */
$('#btnConfirm').click(function () {

    let items = $('.check-item:not(:disabled):checked')
        .map(function () { return $(this).val(); })
        .get();

    if (items.length === 0) {
        Swal.fire('Warning','Pilih minimal 1 item','warning');
        return;
    }

    Swal.fire({
        title: 'Apakah anda yakin?',
        text: 'Barang akan dikurangi dari stok',
        icon: 'question',
        showCancelButton: true
    }).then(res => {

        if (res.isConfirmed) {

            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.post("{{ route('product_outbound.confirm') }}", {
                _token: "{{ csrf_token() }}",
                items: items
            })
            .done(res => {
                Swal.fire('Success', res.message, 'success')
                    .then(() => {
                        window.location.href =
                            "{{ route('product_outbound.index') }}";
                    });
            })
            .fail(err => {
                Swal.fire(
                    'Error',
                    err.responseJSON?.message ?? 'Terjadi kesalahan',
                    'error'
                );
            });
        }

    });
});
</script>
@endsection
