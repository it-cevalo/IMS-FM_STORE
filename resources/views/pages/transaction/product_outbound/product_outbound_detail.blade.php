@extends('layouts.admin')

@section('content')
<div class="card shadow">
    <div class="card-body">

        <h5 class="mb-3">Scan Barang Keluar - {{ $tgl }}</h5>

        <!-- ACTION BAR -->
        <div class="row mb-3">
            <div class="col-md-12 text-right">
                <button class="btn btn-success" id="btnConfirm">
                    Confirm Selected
                </button>
                <a href="{{ route('product_outbound.index') }}" class="btn btn-dark">
                    Back
                </a>
            </div>
        </div>

        <!-- ACCORDION -->
        <div id="accordion">

            @forelse($rows as $doId => $items)
            <div class="card mb-2">

                <div class="card-header d-flex align-items-center">
                    <input type="checkbox" class="check-po mr-2">

                    <a data-toggle="collapse"
                       href="#do{{ $doId }}"
                       class="text-dark">
                        <b>{{ $items->first()->no_do }}</b>
                        <span class="badge badge-info ml-2">
                            {{ $items->count() }} Item
                        </span>
                    </a>
                </div>

                <div id="do{{ $doId }}" class="collapse">
                    <div class="card-body p-2">

                        @foreach($items as $item)
                        <div class="d-flex align-items-center border-bottom py-2">
                            <input type="checkbox"
                                   class="check-item mr-3"
                                   value="{{ $item->id }}">

                            <div>
                                <b>{{ $item->SKU }}</b>
                                - {{ $item->nama_barang }}
                                - {{ $item->qr_code }}<br>
                                Qty: {{ $item->qty }}
                            </div>
                        </div>
                        @endforeach

                    </div>
                </div>

            </div>
            @empty
            <div class="alert alert-warning">
                Tidak ada data outbound pada tanggal ini
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
        .find('.check-item')
        .prop('checked', isChecked);
});

/** CHECK ITEM → SYNC CHECK DO */
$(document).on('change', '.check-item', function () {

    let $card = $(this).closest('.card');

    let totalItem   = $card.find('.check-item').length;
    let checkedItem = $card.find('.check-item:checked').length;

    if (totalItem === checkedItem) {
        $card.find('.check-po').prop('checked', true);
    } else {
        $card.find('.check-po').prop('checked', false);
    }
});

/** CONFIRM OUTBOUND */
$('#btnConfirm').click(function () {

    let items = $('.check-item:checked')
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
            
            // ===============================
            // LOADING SWAL
            // ===============================
            Swal.fire({
                title: 'Memproses...',
                text: 'Mohon tunggu',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
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
