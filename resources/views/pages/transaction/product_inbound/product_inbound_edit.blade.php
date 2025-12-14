@extends('layouts.admin')

@section('content')
<div class="card shadow">
    <div class="card-body">

        <h6 class="mb-3">Confirm Product Inbound</h6>

        <div class="row">
            <div class="col-md-6">
                <p><b>PO:</b> {{ $inbound->no_po }}</p>
                <p><b>Produk:</b> {{ $inbound->nama_barang }}</p>
                <p><b>Qty Inbound:</b> {{ $inbound->qty }}</p>
            </div>

            <div class="col-md-6">
                <label>Pilih Gudang</label>
                <select id="id_warehouse" class="form-control">
                    <option value="">-- Pilih Gudang --</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">
                            {{ $wh->nama_wh }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <hr>

        <button class="btn btn-primary" id="btnConfirm">
            Confirm
        </button>

        <a href="{{ route('product_inbound.index') }}"
           class="btn btn-dark">
           Back
        </a>

    </div>
</div>

<script>
$('#btnConfirm').click(function () {

    if (!$('#id_warehouse').val()) {
        Swal.fire('Warning','Pilih gudang terlebih dahulu','warning');
        return;
    }

    Swal.fire({
        title: 'Confirm Inbound?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes'
    }).then((res) => {

        if (res.isConfirmed) {
            $.post("{{ route('product_inbound.confirm', $inbound->id) }}", {
                _token: "{{ csrf_token() }}",
                id_warehouse: $('#id_warehouse').val()
            })
            .done(res => {
                Swal.fire('Success', res.message, 'success')
                    .then(() => window.location.href = "{{ route('product_inbound.index') }}");
            })
            .fail(err => {
                Swal.fire('Error', err.responseJSON.message, 'error');
            });
        }

    });
});
</script>
@endsection