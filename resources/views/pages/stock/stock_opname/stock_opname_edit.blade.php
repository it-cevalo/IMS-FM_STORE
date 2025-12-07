@extends('layouts.admin')

@section('content')  
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Stock Opname Update</h6>
    </div>
    <div class="card-body">
        @if(\Session::has('error'))
            <div class="alert alert-danger">
                <span>{{ \Session::get('error') }}</span>
                <button type="button" class="close" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @elseif(\Session::has('success'))
            <div class="alert alert-success">
                <span>{{ \Session::get('success') }}</span>
                <button type="button" class="close" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
    <form action="{{route('stock_opname.update',$stock_opname->id)}}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="exampleFormControlInput1">Warehouse</label>
                <select class="form-control select2" id="search-type" name="id_warehouse" value="{{old('id_warehouse')}}" readonly>
                    <option value="">....</option>
                    @foreach($warehouse as $p)
                    <option value="{{$p->id}}" @if ($stock_opname->id_warehouse == $p->id) selected @endif>{{$p->code_wh}} {{$p->nama_wh}}</option>
                    @endforeach
                </select>
            </div>
            <div class="validation"></div>
                @error('id_warehouse')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Product</label>
                <select class="form-control select2" id="search-type" name="id_product" value="{{old('id_product')}}" readonly>
                    <option value="">....</option>
                    @foreach($product as $p)
                    <option value="{{$p->id}}" @if ($stock_opname->id_product == $p->id) selected @endif>{{$p->SKU}} {{$p->nama_barang}}</option>
                    @endforeach
                </select>
            </div>
            <div class="validation"></div>
                @error('id_product')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Qty In</label>
                <input class="form-control" id="exampleFormControlInput1" name="qty_in" type="number" min="0" value="{{$stock_opname->qty_in}}" placeholder="Input Qty In">
            </div>
            <div class="validation"></div>
                @error('qty_in')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Qty Out</label>
                <input class="form-control" id="exampleFormControlInput1" name="qty_out" type="number" min="0" value="{{$stock_opname->qty_out}}" placeholder="Input Qty Out">
            </div>
            <div class="validation"></div>
                @error('qty_out')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Last Qty</label>
                <input class="form-control" id="exampleFormControlInput1" name="qty_last" value="{{$stock_opname->qty_last}}" type="number" min="0" placeholder="Input Last Qty">
            </div>
            <div class="validation"></div>
                @error('qty_last')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Date</label>
                <input class="form-control" id="exampleFormControlInput1" name="tgl_opname" type="date" value="{{$stock_opname->tgl_opname}}" required>
            </div>
            <div class="validation"></div>
                @error('tgl_opname')
                  <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="{{route('stock_opname.index')}}" class="btn btn-dark">Back</a>
        </form>
    </div>
</div>

{{-- @push('scripts') --}}
<!-- <script type="text/javascript">
    $(document).ready(function() {
        $('.select2').select2();
    });
</script> -->
{{-- @endpush --}}


<!-- Start Embbed JS  -->
<!-- <script src="//code.jquery.com/jquery-1.11.1.min.js"></script> -->
<!-- End Embbed JS  -->

<!-- Start JS  -->
    <!-- <script type="text/javascript">
        $(document).ready(function () {
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

            $('#addrow').click(function(e){
                e.preventDefault(); 
                $('#append_akun').append(
                    `<tr class="row-akun">
                        <td><input type="text" autocomplete="off" class="form-control unit text-right " name="unit[]"></td>
                        <td><input type="number" autocomplete="off" class="form-control qty text-right " name="qty[]"></td>
                        <td><input type="number" autocomplete="off" class="form-control price text-right " name="price[]"></td>
                        <td><input id="myInput" type="number" autocomplete="off" name="total_price[]" value="" class="form-control text-right data-nilai" readonly=""></td>
                        <td style="border:none;"><a  class="btn-remove btn btn-danger btn-flat btn-sm"><i class="fa fa-minus" title="Delete"></a></td>
                    </tr>`
                );
            });
            $('#append_akun').on('change','.price', function() {
                row = $(this).parent().parent();
                count(row);
            });

            $('#append_akun').on('change','.qty', function() {
                row = $(this).parent().parent();
                count(row);
            });      

            $('#append_akun').on('click','.btn-remove', function(e) {
                e.preventDefault();
                $(this).parent().parent().remove();
                sum();
            });
        });

        function count(row)
        {
            price = row.find('.price').val();
            qty = row.find('.qty').val();

            amount = price * qty;
            // grand_total = amount*11/100;

            row.find('.data-nilai').val(amount);

            sum();
        }

        function sum()
        {
            let sum = 0;

            $('.data-nilai').each(function() {
                sum = sum+Number($(this).val());
            });

            ppn = sum*11/100;
            gt = ppn+sum;
            $('#grand_total').val(gt);
            $('#ppn').val(ppn);
            $("#hidden_jumlah").val(gt);
        }

        // function update_amounts(){
        //     var sum = 0.0;
        //     $('table.table-bordered').each(function() {
        //         var qty = $(this).find('#qty').val();
        //         var price = $(this).find('#price').val();
        //         var amount = (qty*price)
        //         //alert(amount);
        //         sum+= amount;
        //         $(this).find('#total_price').val(amount); 
        //     });
        //     // $('.total').val(sum);
        //     //just update the total to sum  
        // }
    </script> -->
<!-- End JS  -->
@endsection