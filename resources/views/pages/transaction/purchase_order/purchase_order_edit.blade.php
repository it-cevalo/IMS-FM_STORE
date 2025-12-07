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

            <div class="mb-3">
                <label>Supplier</label>
                <div class="input-group">
                    <select class="form-control" name="id_supplier" readonly="readonly">
                        @forelse($suppliers as $sup)
                        <option value="{{$sup->id}}" @if ($purchase_order->id_supplier == $sup->id) selected @endif>
                            {{$sup->code_spl}} - {{$sup->nama_spl}}
                        </option>
                        @empty
                        @endforelse
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label>PO Date</label>
                <input class="form-control" name="tgl_po" type="date"
                    value="{{ \Carbon\Carbon::parse($purchase_order->tgl_po)->format('Y-m-d')}}"
                    readonly="readonly">
            </div>

            <div class="mb-3">
                <label>PO Number</label>
                <input class="form-control" name="no_po" type="text" value="{{$purchase_order->no_po}}" readonly="readonly">
            </div>

            <div class="mb-3">
                <label>Note</label>
                <textarea class="form-control" name="reason_po">{{$purchase_order->reason_po}}</textarea>
            </div>

            <div class="table-responsive">
                <label>Product</label>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center align-middle">Kode Barang</th>
                            <th class="text-center align-middle">Nama Barang</th>
                            <th class="text-center align-middle">Qty</th>

                            {{-- *** Dinonaktifkan: Harga Satuan & Total Harga *** --}}
                            {{-- <th class="text-center align-middle">Harga Satuan</th>
                            <th class="text-center align-middle">Total Harga</th> --}}

                            <th class="text-center align-middle">
                                <a class="btn btn-primary btn-sm" id="addrow"><i class="fa fa-plus"></i></a>
                            </th>
                        </tr>
                    </thead>

                    <tbody id="append_akun">
                        @foreach($purchase_order_dtl as $val)
                        <tr class="row-akun">
                            <td>
                                <input type="text" class="form-control" name="part_number[]" value="{{ $val->part_number }}">
                            </td>
                            <td>
                                <input type="text" class="form-control" name="product_name[]" value="{{ $val->product_name }}">
                            </td>
                            <td>
                                <input type="number" class="form-control qty text-right" name="qty[]" value="{{ $val->qty }}">
                            </td>

                            {{-- *** Dinonaktifkan: Input harga & total *** --}}
                            {{-- <td><input type="number" class="form-control price" name="price[]" value="{{ $val->price }}"></td>
                            <td><input type="number" class="form-control data-nilai" name="total_price[]" value="{{ $val->total_price }}" readonly></td> --}}

                            <td style="border:none;">
                                <a class="btn-remove btn btn-danger btn-flat btn-sm">
                                    <i class="fa fa-minus"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        {{-- *** Dinonaktifkan: PPN & Grand Total *** --}}
                        {{-- 
                        <tr>
                            <td colspan="4" class="text-right align-middle">PPN 11%</td>
                            <td><input id="ppn" type="text" name="ppn" readonly class="form-control text-right"></td>
                        </tr>

                        <tr>
                            <td colspan="4" class="text-right align-middle">Grand Total</td>
                            <input type="hidden" name="grand_total" id="hidden_jumlah">
                            <td><input id="grand_total" type="text" name="grand_total" readonly class="form-control text-right"></td>
                        </tr>
                        --}}
                    </tfoot>

                </table>
            </div>

            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="{{route('purchase_order.index')}}" class="btn btn-dark">Back</a>
        </form>
    </div>
</div>

<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>

<script>
$(document).ready(function() {

    // ADD ROW tanpa kolom harga
    $('#addrow').click(function(e) {
        e.preventDefault();
        $('#append_akun').append(`
            <tr class="row-akun">
                <td><input type="text" class="form-control" name="part_number[]"></td>
                <td><input type="text" class="form-control" name="product_name[]"></td>
                <td><input type="number" class="form-control qty" name="qty[]"></td>

                <!-- Dinonaktifkan -->
                <!-- <td><input type="number" class="form-control price" name="price[]"></td>
                <td><input type="number" class="form-control data-nilai" name="total_price[]" readonly></td> -->

                <td style="border:none;">
                    <a class="btn-remove btn btn-danger btn-flat btn-sm"><i class="fa fa-minus"></i></a>
                </td>
            </tr>
        `);
    });

    // REMOVE ROW
    $('#append_akun').on('click', '.btn-remove', function(e) {
        e.preventDefault();
        $(this).closest('tr').remove();
    });

});
</script>

@endsection
