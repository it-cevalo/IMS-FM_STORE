@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Purchase Order</h6>
    </div>
    <div class="card-body">
        <form action="{{route('purchase_order.update',$purchase_order->id)}}" method="POST">
            @csrf
            @method('PUT')
            <!-- <div class="mb-3">
                <label for="exampleFormControlInput1">Customer</label>
                <div class="input-group">
                        <select class="form-control" name="id_cust" readonly="readonly">
                           @forelse($customers as $cust)
                           <option value="{{$cust->id}}" @if ($purchase_order->id_cust == $cust->id) selected @endif>{{$cust->code_cust}} - {{$cust->nama_cust}}</option>
                           @empty
                           @endforelse
                        </select>
                </div> 
            </div>
            <div class="validation"></div>
                @error('id_cust')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror -->
            <div class="mb-3">
                <label for="exampleFormControlInput1">Supplier</label>
                <div class="input-group">
                    <select class="form-control" name="id_supplier" readonly="readonly">
                        @forelse($suppliers as $sup)
                        <option value="{{$sup->id}}" @if ($purchase_order->id_supplier == $sup->id) selected
                            @endif>{{$sup->code_spl}} - {{$sup->nama_spl}}</option>
                        @empty
                        @endforelse
                    </select>
                </div>
            </div>
            <div class="validation"></div>
            @error('id_supplier')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">PO Date</label>
                <input class="form-control" id="exampleFormControlInput1" name="tgl_po"
                    value="{{ \Carbon\Carbon::parse($purchase_order->tgl_po)->format('Y-m-d')}}" type="date"
                    readonly="readonly">
            </div>
            <div class="validation"></div>
            @error('tgl_po')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">PO Number</label>
                <input class="form-control" id="exampleFormControlInput1" name="no_po"
                    value="{{$purchase_order->no_po}}" type="text" placeholder="Input PO Number" readonly="readonly">
            </div>
            <div class="validation"></div>
            @error('no_po')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <!-- <div class="mb-3">
                <label for="exampleFormControlInput1">SO Number</label>
                <input class="form-control" id="exampleFormControlInput1" name="no_so" value="{{$purchase_order->no_so}}" type="text" placeholder="Input SO Number" readonly="readonly">
            </div>
            <div class="validation"></div>
                @error('no_so')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror -->
            {{-- <div class="mb-3">
                <label for="exampleFormControlInput1">Status</label>
                <select class="form-control form-control-sm" name="status_po">
                    @foreach($status_po as $k => $v)
                    @if($purchase_order->status_po == $k)
                    <option value="{{ $k }}" selected="">{{ $v }}</option>
                    @else
                    <option value="{{ $k }}">{{ $v }}</option>
                    @endif
                    @endforeach
                </select>
            </div>
            <div class="validation"></div>
            @error('status_po')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror --}}
            <div class="mb-3">
                <label for="exampleFormControlInput1">Reason</label>
                <textarea class="form-control" id="exampleFormControlInput1" name="reason_po" type="text"
                    placeholder="Input Reason">{{$purchase_order->reason_po}}</textarea>
            </div>
            <div class="validation"></div>
            @error('reason_po')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="table-responsive">
                <label for="exampleFormControlInput1">Product</label>
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th rowspan="2" class="text-center align-middle">Kode Barang</th>
                            <th rowspan="2" class="text-center align-middle">Nama Barang</th>
                            <th rowspan="2" class="text-center align-middle">Qty</th>
                            <th rowspan="2" class="text-center align-middle">Harga Satuan</th>
                            <th rowspan="2" class="text-center align-middle">Total Harga</th>
                            <th rowspan="2" class="text-center align-middle" style="text-align: right; border:none;">
                                <a class="btn btn-primary btn-flat btn-sm" id="addrow"><i class="fa fa-plus"></i></a>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="append_akun">
                        @foreach($purchase_order_dtl as $k => $val)
                        <tr class="row-akun">
                            <td>
                                <input type="text" class="form-control" name="part_number[]"
                                    value="{{ $val->part_number }}" />
                            </td>
                            <td>
                                <input type="text" class="form-control" name="product_name[]"
                                    value="{{ $val->product_name }}" />
                            </td>
                            <td>
                                <input type="number" autocomplete="off" class="form-control price text-right "
                                    name="price[]" value="{{ $val->price }}">
                            </td>
                            <td>
                                <input type="number" autocomplete="off" class="form-control qty text-right "
                                    name="qty[]" value="{{ $val->qty }}">
                            </td>
                            <td>
                                <input id="myInput" type="number" autocomplete="off" name="total_price[]"
                                    value="{{ $val->total_price }}" class="form-control text-right data-nilai"
                                    readonly="">
                            </td>
                            <td style="display:none; border:none;">
                                <a class="btn-remove btn btn-danger btn-flat btn-sm"><i class="fa fa-minus"
                                        title="Delete"></a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-right align-middle">PPN 11%</td>
                            <td><input id="ppn" type="text" name="ppn" readonly="" class="form-control text-right"></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-right align-middle">Grand Total</td>
                            <input type="hidden" name="grand_total" id="hidden_jumlah">
                            <td><input id="grand_total" type="text" name="grand_total" readonly=""
                                    class="form-control text-right"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="{{route('purchase_order.index')}}" class="btn btn-dark">Cancel</a>
        </form>
    </div>
</div>
<!-- Start Embbed JS  -->
<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<!-- End Embbed JS  -->

<!-- Start JS  -->
<script type="text/javascript">
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
        $('#append_akun').append(
            `<tr class="row-akun">
                    <td><input type="text" class="form-control" name="part_number[]"/></td>
                    <td><input type="text" class="form-control" name="product_name[]"/></td>
                    <td><input type="number" autocomplete="off" class="form-control qty text-right " name="qty[]" style="width: 80px;"></td>
                    <td><input type="number" autocomplete="off" class="form-control price text-right " name="price[]" style="width: 150px;"></td>
                    <td><input id="myInput" type="number" autocomplete="off" name="total_price[]" value="" class="form-control text-right data-nilai" readonly=""></td>
                    <td style="border:none;"><a  class="btn-remove btn btn-danger btn-flat btn-sm"><i class="fa fa-minus" title="Delete"></a></td>
                    </tr>`
        );
    });
    $('#append_akun').on('change', '.price', function() {
        row = $(this).parent().parent();
        count(row);
    });

    $('#append_akun').on('change', '.qty', function() {
        row = $(this).parent().parent();
        count(row);
    });

    $('#append_akun').on('click', '.btn-remove', function(e) {
        e.preventDefault();
        $(this).parent().parent().remove();
        sum();
    });

    $('#diskon').on('input', function() {
        sum();
    });

    $('#diskon').on('change', function() {
        sum();
    });

    $('#bdll').on('input', function() {
        sum();
    });

    $('#bdll').on('change', function() {
        sum();
    });

    $('#ppn').on('input', function() {
        sum();
    });

    $('#ppn').on('change', function() {
        sum();
    });
});

function count(row) {
    price = row.find('.price').val();
    qty = row.find('.qty').val();

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

    ppn = sum * 11 / 100;
    gt = ppn + sum;
    $('#grand_total').val(gt);
    $('#ppn').val(ppn);
    $("#hidden_jumlah").val(gt);
}
</script>
<!-- End JS  -->
@endsection