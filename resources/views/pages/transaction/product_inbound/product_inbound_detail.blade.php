@extends('layouts.admin')

@section('content')
<div class="card shadow">
    <div class="card-body">

        <h5 class="mb-3">Scan Barang Masuk - {{ $tgl }}</h5>

        @php
            // ===============================
            // GLOBAL CHECK: APAKAH SEMUA ITEM SUDAH MASUK GUDANG
            // ===============================
            $totalAllItem = 0;
            $doneAllItem  = 0;

            foreach ($rows as $items) {
                $totalAllItem += $items->count();
                $doneAllItem  += $items->where('id_warehouse', '!=', 0)->count();
            }

            $allDone = $totalAllItem > 0 && $totalAllItem === $doneAllItem;
        @endphp

        <!-- ACTION BAR -->
        @if(!$allDone)
        <div class="row mb-3">
            <div class="col-md-4">
                <select id="id_warehouse" class="form-control">
                    <option value="">-- Pilih Gudang --</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->nama_wh }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-8 text-right">
                <button class="btn btn-success" id="btnConfirm">
                    Confirm Selected
                </button>
                <a href="{{ route('product_inbound.index') }}" class="btn btn-dark">
                    Back
                </a>
            </div>
        </div>
        @else
        <div class="alert alert-success d-flex justify-content-between align-items-center">
            <div>
                <i class="fa fa-check-circle mr-2"></i>
                <b>Semua barang inbound pada tanggal ini sudah masuk gudang.</b>
            </div>
            <a href="{{ route('product_inbound.index') }}" class="btn btn-dark btn-sm">
                Back
            </a>
        </div>
        @endif

        <!-- ACCORDION -->
        <div id="accordion">

            @forelse($rows as $poId => $items)

            @php
                // cek apakah SEMUA item di PO sudah masuk gudang
                $totalItem = $items->count();
                $doneItem  = $items->where('id_warehouse', '!=', 0)->count();
                $isDonePO  = $totalItem === $doneItem;
            @endphp

            <div class="card mb-2 {{ $isDonePO ? 'border-success' : '' }}">

                <div class="card-header d-flex align-items-center
                    {{ $isDonePO ? 'bg-success text-white' : '' }}">

                    <input type="checkbox"
                           class="check-po mr-2"
                           {{ $isDonePO ? 'disabled' : '' }}>

                    <a data-toggle="collapse"
                       href="#po{{ $poId }}"
                       class="{{ $isDonePO ? 'text-white' : 'text-dark' }}">
                        <b>{{ $items->first()->no_po }}</b>

                        <span class="badge {{ $isDonePO ? 'badge-light' : 'badge-info' }} ml-2">
                            {{ $items->count() }} Item
                        </span>

                        @if($isDonePO)
                            <span class="badge badge-dark ml-2">
                                Sudah Masuk Gudang
                            </span>
                        @endif
                    </a>
                </div>

                <div id="po{{ $poId }}" class="collapse">
                    <div class="card-body p-2">

                        @foreach($items as $item)
                        <div class="d-flex align-items-center border-bottom py-2">
                            <input type="checkbox"
                                   class="check-item mr-3"
                                   value="{{ $item->id }}"
                                   {{ $item->id_warehouse != 0 ? 'disabled' : '' }}>

                            <div>
                                <b>{{ $item->SKU }}</b>
                                - {{ $item->nama_barang }}
                                - {{ $item->qr_code }}<br>
                                Qty: {{ $item->qty }}

                                @if($item->id_warehouse != 0)
                                    <span class="badge badge-secondary ml-2">
                                        Sudah masuk gudang
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
                Tidak ada data inbound pada tanggal ini
            </div>
            @endforelse

        </div>

    </div>
</div>

{{-- ================= JS ================= --}}
<script>
/** CHECK PO → CHECK ITEM (HANYA YANG TIDAK DISABLED) */
$(document).on('change', '.check-po', function () {
    let isChecked = $(this).is(':checked');

    $(this)
        .closest('.card')
        .find('.check-item:not(:disabled)')
        .prop('checked', isChecked);
});

/** CHECK ITEM → SYNC CHECK PO */
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

/** CONFIRM */
$('#btnConfirm').click(function () {

    let items = $('.check-item:not(:disabled):checked')
        .map(function () { return $(this).val(); })
        .get();

    if (!$('#id_warehouse').val()) {
        Swal.fire('Warning','Pilih gudang','warning');
        return;
    }

    if (items.length === 0) {
        Swal.fire('Warning','Pilih minimal 1 item','warning');
        return;
    }

    Swal.fire({
        title: 'Apakah anda yakin sudah benar?',
        icon: 'question',
        showCancelButton: true
    }).then(res => {

        if (res.isConfirmed) {
            $.post("{{ route('product_inbound.confirm') }}", {
                _token: "{{ csrf_token() }}",
                id_warehouse: $('#id_warehouse').val(),
                items: items
            })
            .done(res => {
                Swal.fire('Success', res.message, 'success')
                    .then(() => {
                        window.location.href =
                            "{{ route('product_inbound.index') }}";
                    });
            })
            .fail(err => {
                Swal.fire(
                    'Error',
                    err.responseJSON?.message ?? 'Error',
                    'error'
                );
            });
        }

    });
});
</script>
@endsection
