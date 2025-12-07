@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Purchase Order</h6>
    </div>
    <div class="card-body">

        <form id="formPO">
            @csrf
            {{-- Supplier --}}
            <div class="mb-3">
                <label>Supplier</label>
                <select class="form-control select2" name="id_supplier" required>
                    <option value="">-- Pilih Supplier --</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}">{{ $sup->code_spl }} - {{ $sup->nama_spl }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Tanggal PO --}}
            <div class="mb-3">
                <label>PO Date</label>
                <input class="form-control" name="tgl_po" type="date" required>
            </div>

            {{-- Nomor PO --}}
            <div class="mb-3">
                <label>PO Number</label>
                <input class="form-control" name="no_po" type="text" placeholder="Input PO Number" required>
            </div>

            {{-- Note --}}
            <div class="mb-3">
                <label>Note</label>
                <textarea class="form-control" name="reason_po" placeholder="Input Note" required></textarea>
            </div>

            {{-- Produk --}}
            <div class="table-responsive">
                <label>Product</label>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Qty</th>

                            {{-- <th>Harga Satuan</th> --}}
                            {{-- <th>Total</th> --}}

                            <th><a class="btn btn-primary btn-sm" id="addrow"><i class="fa fa-plus"></i></a></th>
                        </tr>
                    </thead>
                    <tbody id="append_akun"></tbody>

                    <tfoot>
                        {{-- DISKON DAN BIAYA DLL DINONAKTIFKAN --}}
                        {{--
                        <tr>
                            <td colspan="4" class="text-right">Diskon</td>
                            <td><input id="diskon" type="number" name="diskon" class="form-control text-right" value="0"></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-right">Biaya DLL</td>
                            <td><input id="bdll" type="number" name="bdll" class="form-control text-right" value="0"></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-right">PPN 11%</td>
                            <td><input id="ppn" type="text" readonly class="form-control text-right"></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-right"><strong>Grand Total</strong></td>
                            <td>
                                <input type="hidden" name="grand_total" id="hidden_jumlah">
                                <input id="grand_total" type="text" readonly class="form-control text-right font-weight-bold">
                            </td>
                        </tr>
                        --}}
                    </tfoot>
                </table>
            </div>

            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="{{ route('purchase_order.index') }}" class="btn btn-dark">Cancel</a>
        </form>
    </div>
</div>

{{-- JS --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $('.select2').select2({ width: '100%' });

    const PRODUCTS = @json($products->map(function($p) {
        return [
            'kode_barang' => $p->kode_barang,
            'nama' => $p->nama_barang,
            // 'harga' => $p->harga_beli // DINONAKTIFKAN
        ];
    }));

    function getProductOptions(except = []) {
        let options = `<option value="">Pilih Barang</option>`;
        PRODUCTS.forEach(function(p) {
            if (!except.includes(p.kode_barang)) {
                options += `<option value="${p.kode_barang}" data-nama="${p.nama}">${p.kode_barang}</option>`;
            }
        });
        return options;
    }

    // Tambah baris produk
    $('#addrow').click(function(e) {
        e.preventDefault();

        let selected = [];
        $('.kode_barang').each(function() {
            let val = $(this).val();
            if (val) selected.push(val);
        });

        let options = getProductOptions(selected);

        let row = `
        <tr>
            <td>
                <select class="form-control select2 kode_barang" name="kode_barang[]" required style="width:100%">
                    ${options}
                </select>
            </td>
            <td><input type="text" class="form-control nama_barang" name="nama_barang[]" readonly></td>
            <td><input type="number" class="form-control qty text-right" name="qty[]" min="1" style="width:80px;"></td>

            {{-- <td><input type="number" class="form-control harga text-right" name="harga[]" min="0" style="width:150px;"></td> --}}
            {{-- <td><input type="number" class="form-control total text-right data-nilai" name="total[]" readonly></td> --}}

            <td style="text-align:center;">
                <a class="btn btn-danger btn-sm btn-remove"><i class="fa fa-minus"></i></a>
            </td>
        </tr>`;

        $('#append_akun').append(row);
        $('#append_akun').find('select.select2').last().select2({ width: '100%' });
    });

    // Hapus baris
    $('#append_akun').on('click', '.btn-remove', function(e) {
        e.preventDefault();
        $(this).closest('tr').remove();
        refreshKodeBarangOptions();
        // sum(); // DINONAKTIFKAN
    });

    // Saat ganti kode barang
    $('#append_akun').on('change', '.kode_barang', function() {
        let row = $(this).closest('tr');
        let selected = $(this).find(':selected');
        let nama = selected.data('nama') || '';

        row.find('.nama_barang').val(nama);

        // row.find('.harga').val('');
        // row.find('.qty, .total').val('');

        refreshKodeBarangOptions();
    });

    // Refresh dropdown
    function refreshKodeBarangOptions() {
        let selected = [];
        $('.kode_barang').each(function() {
            let val = $(this).val();
            if (val) selected.push(val);
        });

        $('.kode_barang').each(function() {
            let $sel = $(this);
            let current = $sel.val();

            let allowed = selected.filter(s => s !== current);
            let options = getProductOptions(allowed);

            $sel.html(options);

            if (current) {
                $sel.val(current);
            } else {
                $sel.val('');
            }
            $sel.trigger('change.select2');
        });
    }

    // Hitung total — DINONAKTIFKAN
    /*
    $('#append_akun').on('input', '.qty, .harga', function() {
        let row = $(this).closest('tr');
        let qty = parseFloat(row.find('.qty').val()) || 0;
        let harga = parseFloat(row.find('.harga').val()) || 0;
        row.find('.total').val((qty * harga).toFixed(2));
        sum();
    });

    $('#diskon, #bdll').on('input', sum);

    function sum() {
        let subtotal = 0;
        $('.data-nilai').each(function() { subtotal += parseFloat($(this).val()) || 0; });
        let diskon = parseFloat($('#diskon').val()) || 0;
        let bdll = parseFloat($('#bdll').val()) || 0;
        let afterDiskon = subtotal - diskon + bdll;
        let ppn = afterDiskon * 0.11;
        let grandTotal = afterDiskon + ppn;
        $('#ppn').val(ppn.toFixed(2));
        $('#grand_total').val(grandTotal.toFixed(2));
        $('#hidden_jumlah').val(grandTotal.toFixed(2));
    }
    */

    // Submit via AJAX
    $('#formPO').on('submit', function(e) {
        e.preventDefault();
        let formData = $(this).serialize();

        Swal.fire({
            title: 'Processing...',
            text: 'Please wait while saving your PO',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: "{{ route('purchase_order.store') }}",
            type: "POST",
            data: formData,
            success: function(response) {
                Swal.close();
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => window.location.href = "{{ route('purchase_order.index') }}");
                } else {
                    Swal.fire('Warning', response.message || 'Terjadi masalah', 'warning');
                }
            },
            error: function(xhr) {
                Swal.close();
                let msg = xhr.responseJSON?.message || 'An unexpected error occurred';
                Swal.fire('Error', msg, 'error');
            }
        });
    });
});
</script>

@endsection
