@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Purchase Request</h6>
    </div>
    <div class="card-body">
        @if(\Session::has('error'))
        <div class="alert alert-danger">
            <span>{{ \Session::get('error') }}</span>
            <button type="button" class="close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @else
        @endif
        <form action="{{route('purchase_request.update',$purchase_request->id)}}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="exampleFormControlInput1">Number</label>
                <div class="input-group">
                    <input class="form-control" id="exampleFormControlInput1" name="code_pr" value="{{$purchase_request->code_pr}}" type="text" disabled>
                </div>
            </div>
            <div class="validation"></div>
            @error('code_pr')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Tanggal</label>
                <div class="input-group">
                    <input class="form-control" id="exampleFormControlInput1" name="request_date" value="{{ \Carbon\Carbon::parse($purchase_request->request_date)->format('Y-m-d')}}" type="date" disabled>
                </div>
            </div>
            <div class="validation"></div>
            @error('request_date')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Gudang</label>
                <select class="form-control select2" id="search-type" name="id_warehouse_from"
                    value="{{old('id_warehouse_from')}}" disabled>
                    @forelse($warehouses as $sup)
                    <option value="{{$sup->id}}" @if ($purchase_request->id_warehouse == $sup->id) selected @endif>{{$sup->code_store}}/{{$sup->code_wh}} - {{$sup->nama_wh}}</option>
                    @empty
                    @endforelse
                </select>
            </div>
            <div class="mb-3">
                <label for="exampleFormControlInput1">Description</label>
                <input class="form-control" id="exampleFormControlInput1" name="desc_req" value="{{$purchase_request->desc_req}}" type="text" required>
            </div>
            <div class="validation"></div>
            @error('desc_req')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="table-responsive">
                <label for="exampleFormControlInput1">Barang</label>
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th rowspan="2" class="text-center align-middle">Kode Barang</th>
                            <th rowspan="2" class="text-center align-middle">Nama Barang</th>
                            <th rowspan="2" class="text-center align-middle">Description</th>
                            <th rowspan="2" class="text-center align-middle">Qty</th>
                            <th rowspan="2" class="text-center align-middle" style="text-align: right; border:none;">
                                <a class="btn btn-primary btn-flat btn-sm" id="addrow"><i class="fa fa-plus"></i></a>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="append_akun">
                        @foreach($purchase_request_dtl as $k => $val)
                        <tr class="row-akun">
                            <td>
                                <input type="text" class="form-control" name="SKU[]" value="{{ $val->SKU }}"/>
                            </td>
                            <td>
                                <input type="text" class="form-control" name="nama_barang[]" value="{{ $val->product->nama_barang }}"/>
                            </td>
                            <td>
                                <input type="text" autocomplete="off" class="form-control" name="desc_prd[]" value="{{ $val->desc_prd }}">
                            </td>
                            <td>
                                <input type="number" autocomplete="off" class="form-control qty_prd text-right " name="qty_prd[]" value="{{ $val->qty_prd }}">
                            </td>
                        </tr>
                    </tbody>
                    @endforeach
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right align-middle">Total QTY</td>
                            <td><input id="total_qty_req" type="number" name="total_qty_req" readonly="" value="{{$purchase_request->total_qty_req}}" class="form-control text-right"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{route('purchase_request.index')}}" class="btn btn-dark">Batal</a>
        </form>
    </div>
</div>
<!-- Start Embbed JS  -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<!-- End Embbed JS  -->

<!-- Start JS  -->
<script type="text/javascript">
// Remove row click event (assuming you have this somewhere in your code)
$(document).on('click', '.btn-remove', function(e) {
    e.preventDefault();
    $(this).closest('tr').remove();
    updateTotalQty(); // Update total quantity when a row is removed
});

$(document).ready(function() {
    var counter = 0;
    /*
        update_amounts();
        $(document).on('change','#price',function(){
            update_amounts();
        });

        $(document).on('change','#qty',function(){
            update_amounts();
        }); 

        $("#addrow").on("click", function () {
            var newRow = $("<tr>");
            var cols = "";

            cols += '<td><input type="text" class="form-control" name="part_number[]' + counter + '"/></td>';
            cols += '<td><input type="text" class="form-control" name="product_name[]' + counter + '"/></td>';
            cols += '<td><input type="number" min="0" id="qty" class="form-control" name="qty[]' + counter + '"/></td>';
            cols += '<td><input type="number" min="0" id="price" class="form-control" name="price[]' + counter + '"/></td>';
            cols += '<td><input type="number" min="0" id="total_price" class="form-control" disabled name="total_price[]' + counter + '"/></td>';

            // cols += '<td><input type="button" class="ibtnDel btn btn-md btn-danger "  value="Delete"></td>';
            cols += '<td style="border:none;"><a class="ibtnDel btn btn-primary btn-flat btn-sm"><i class="fa fa-minus"></i></a></td>';
            newRow.append(cols);
            $("table.table-bordered").append(newRow);
            counter++;
        });
    */
    $('#addrow').click(function(e) {
        e.preventDefault();
        var newRow = $('<tr class="row-akun">' +
            '<td><select class="form-control select2" name="SKU[]"></select></td>' +
            '<td><select class="form-control select2" name="nama_barang[]"></select></td>' +
            '<td><input type="text" autocomplete="off" name="desc_prd[]" class="form-control " ></td>' +
            '<td><input type="number" autocomplete="off" class="form-control qty_prd text-right " name="qty_prd[]"></td>' +
            '<td style="border:none;"><a  class="btn-remove btn btn-danger btn-flat btn-sm"><i class="fa fa-minus" title="Delete"></a></td>' +
            '</tr>');

        // Menambahkan baris baru ke dalam tabel
        $('#append_akun').append(newRow);

        // Inisialisasi Select2 pada baris yang baru ditambahkan
        newRow.find('.select2').select2({
            // Atur konfigurasi Select2 sesuai kebutuhan Anda
            width: '100%'
        });

        // Mengambil data SKU dari purchase_request.product dan memasuk kannya ke Select2 SKU
        $.ajax({
            url: "{{ route('purchase_request.product') }}",
            type: 'GET',
            success: function(data) {
                var selectSKU = newRow.find('.select2[name="SKU[]"]');
                // Isi opsi SKU dari data yang diterima
                data.forEach(function(item) {
                    selectSKU.append('<option value="' + item.SKU + '">' + item
                        .SKU + '</option>');
                });

                var selectNamaBarang = newRow.find('.select2[name="nama_barang[]"]');
                // Isi opsi Nama Barang dari data yang diterima
                data.forEach(function(item) {
                    selectNamaBarang.append('<option value="' + item.nama_barang +
                        '">' + item.nama_barang + '</option>');
                });

                selectNamaBarang.prop('disabled', true);

                // Memperbarui Select2 setelah mengisi opsi SKU dan Nama Barang
                selectSKU.select2('destroy').select2();
                selectNamaBarang.select2('destroy').select2();

                // Event handler untuk perubahan nilai pada elemen SKU
                selectSKU.on('change', function() {
                    // Mendapatkan nilai SKU yang dipilih
                    var selectedSKU = $(this).val();

                    // Menemukan elemen Nama Barang terkait
                    var relatedNamaBarang = newRow.find(
                        '.select2[name="nama_barang[]"]');

                    // Mengganti nilai Nama Barang sesuai dengan SKU yang dipilih
                    data.forEach(function(item) {
                        if (item.SKU === selectedSKU) {
                            relatedNamaBarang.val(item.nama_barang).trigger(
                                'change.select2');
                            return false; // Hentikan iterasi setelah menemukan SKU yang cocok
                        }
                    });
                });

                newRow.find('.qty_prd').on('input', function() {
                    updateTotalQty();
                });
            }
        });
        updateTotalQty();
    });

    $('#append_akun').on('change', '.price', function() {
        row = $(this).parent().parent();
        count(row);
    });

    $('#append_akun').on('change', '.qty_prd', function() {
        row = $(this).parent().parent();
        count(row);
    });

    $('#append_akun').on('click', '.btn-remove', function(e) {
        e.preventDefault();
        $(this).parent().parent().remove();
        sum();
    });

    $('#calculate').click(function(e) {
        e.preventDefault();
        sum();
    });

    updateTotalQty();
});

document.addEventListener('DOMContentLoaded', function() {
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.display = 'none';
        }, 5000); // 5 detik
    });
    var closeButtons = document.querySelectorAll('.alert button.close');
    closeButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var parentAlert = this.closest('.alert');
            parentAlert.style.display = 'none';
        });
    });
});

function updateTotalQty() {
    var totalQty = 0;

    // Check if qty_prd is not adding new rows
    if ($('.qty_prd').length > 1) {
        // Loop through all the rows and sum up the quantities
        $('.qty_prd').each(function() {
            var qty = parseFloat($(this).val()) || 0;
            totalQty += qty;
        });
    } else {
        // If qty_prd is adding new rows, set totalQty to the value of qty_prd
        totalQty = parseFloat($('.qty_prd').val()) || 0;
    }

    // Update the total quantity input field
    $('#total_qty_req').val(totalQty);
}

function count(row) {
    price = row.find('.price').val();
    qty = row.find('.qty_prd').val();

    amount = price * qty;
    // grand_total = amount*11/100;

    row.find('.data-nilai').val(amount);

    sum();
}

function sum() {
    let sum = 0;

    $('.data-nilai').each(function() {
        sum = sum + Number($(this).val());
    });

    let diskon = parseFloat($('#diskon').val() || 0);
    let bdll = parseFloat($('#bdll').val() || 0);

    let price = (sum - diskon) + bdll;
    let ppn = price * 11 / 100;
    let gt = price + ppn;

    $('#grand_total').val(gt);
    $('#ppn').val(ppn);
    $("#hidden_jumlah").val(gt);
}
</script>
<!-- End JS  -->
@endsection