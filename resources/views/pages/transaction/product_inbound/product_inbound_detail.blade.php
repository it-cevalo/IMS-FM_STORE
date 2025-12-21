@extends('layouts.admin')

@section('content')
<div class="card shadow">
    <div class="card-body">

        <h5 class="mb-3">Scan Barang Masuk - {{ $tgl }}</h5>

        <!-- ACTION BAR -->
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

        <!-- ACCORDION -->
        <div id="accordion">

            @forelse($rows as $poId => $items)
            <div class="card mb-2">

                <div class="card-header d-flex align-items-center">
                    <input type="checkbox" class="check-po mr-2">

                    <a data-toggle="collapse"
                       href="#po{{ $poId }}"
                       class="text-dark">
                        <b>{{ $items->first()->no_po }}</b>
                        <span class="badge badge-info ml-2">
                            {{ $items->count() }} Item
                        </span>
                    </a>
                </div>

                <div id="po{{ $poId }}" class="collapse">
                    <div class="card-body p-2">

                        @foreach($items as $item)
                        <div class="d-flex align-items-center border-bottom py-2">
                            <input type="checkbox"
                                   class="check-item mr-3"
                                   value="{{ $item->id }}">

                            <div>
                                <b>{{ $item->SKU }}</b> - {{ $item->nama_barang }} - {{$item->qr_code}}<br>
                                Qty: {{ $item->qty }}
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
/** CHECK PO → CHECK ITEM */
/** CHECK PO → CHECK ITEM */
$(document).on('change', '.check-po', function () {
    let isChecked = $(this).is(':checked');

    $(this)
        .closest('.card')
        .find('.check-item')
        .prop('checked', isChecked);
});

/** CHECK ITEM → SYNC CHECK PO */
$(document).on('change', '.check-item', function () {

    let $card = $(this).closest('.card');

    let totalItem   = $card.find('.check-item').length;
    let checkedItem = $card.find('.check-item:checked').length;

    // jika SEMUA item tercentang → check-po ON
    if (totalItem === checkedItem) {
        $card.find('.check-po').prop('checked', true);
    } 
    // jika ADA yang di-uncheck → check-po OFF
    else {
        $card.find('.check-po').prop('checked', false);
    }
});

/** CONFIRM */
$('#btnConfirm').click(function () {

    let items = $('.check-item:checked')
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
        title: 'Confirm inbound?',
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