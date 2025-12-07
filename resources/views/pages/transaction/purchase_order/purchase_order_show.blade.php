@extends('layouts.admin')

@section('content')  
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <a href="{{route('purchase_order.index')}}">Purchase Order</a>
        </h6>
    </div>

    <div class="card-body">

        <div class="mb-3">
            <label>Supplier</label>
            <div class="input-group">
                <select class="form-control" name="id_supplier" disabled>
                    @forelse($suppliers as $sup)
                    <option value="{{$sup->id}}" 
                        @if ($purchase_order->id_supplier == $sup->id) selected @endif>
                        {{$sup->code_spl}} - {{$sup->nama_spl}}
                    </option>
                    @empty
                    @endforelse
                </select>
            </div> 
        </div>

        <div class="mb-3">
            <label>PO Date</label>
            <input class="form-control" 
                name="tgl_po" 
                value="{{ \Carbon\Carbon::parse($purchase_order->tgl_po)->format('Y-m-d')}}"
                type="date" 
                disabled>
        </div>

        <div class="mb-3">
            <label>PO Number</label>
            <input class="form-control" 
                name="no_po" 
                value="{{$purchase_order->no_po}}" 
                type="text" 
                disabled>
        </div>

        <div class="mb-3">
            <label>Status</label>
            <select class="form-control form-control-sm" name="status_po" disabled>
                @foreach($status_po as $k => $v)
                    <option value="{{ $k }}" {{ $purchase_order->status_po == $k ? 'selected' : '' }}>
                        {{ $v }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Note</label>
            <input class="form-control" 
                name="reason_po" 
                value="{{$purchase_order->reason_po}}" 
                type="text" 
                disabled>
        </div>
        
        <button type="button" class="btn btn-primary" id="btnPrintQR" data-toggle="modal" data-target="#modalNomorUrut">
            <i class="fas fa-print"></i> Print QR
        </button>

        {{-- ===================== MODAL PILIH NOMOR URUT ===================== --}}
        <div class="modal fade" id="modalNomorUrut" tabindex="-1" role="dialog">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                  <h5 class="modal-title">Pilih Nomor Urut</h5>
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
          
                <div class="modal-body">

                    <div class="form-group">
                        <label>Input nomor urut (contoh: 1-10 atau 3,7)</label>
                        <input type="text" id="selectedSequence" class="form-control">
                    </div>

                </div>
          
                <div class="modal-footer">
                  <button class="btn btn-primary" id="btnSubmitSequence">Print</button>
                </div>
              </div>
            </div>
        </div>

        <div class="table-responsive mt-3">
            <label>Product</label>
            <table class="table table-bordered" width="100%">
                <thead>
                    <tr>
                        <th class="text-center">
                            <input type="checkbox" id="checkAll">
                        </th>
                        <th class="text-center align-middle">Kode Barang</th>
                        <th class="text-center align-middle">Nama Barang</th>
                        <th class="text-center align-middle">Qty</th>
                    </tr>
                </thead>

                <tbody id="append_akun">
                    @foreach($purchase_order_dtl as $val)
                    <tr class="row-akun">
                        <td class="text-center">
                            <input type="checkbox" class="chkProduct" value="{{ $val->id }}" 
                                data-sku="{{ $val->part_number }}">
                        </td>
                        <td>
                            <input type="text" class="form-control" disabled 
                                value="{{ $val->part_number }}"/>
                        </td>
                        <td>
                            <input type="text" class="form-control" disabled 
                                value="{{ $val->product_name }}"/>
                        </td>
                        <td>
                            <input type="number" class="form-control text-right" disabled 
                                value="{{ $val->qty }}">
                        </td>

                        <!-- DINONAKTIFKAN -->
                        <!--
                        <td>...</td>
                        <td>...</td>
                        -->
                    </tr>
                    @endforeach
                </tbody>

                <tfoot>
                    <!-- DINONAKTIFKAN -->
                    <!--
                    <tr>
                        <td colspan="4" class="text-right">Grand Total</td>
                        <td><input ...></td>
                    </tr>
                    -->
                </tfoot>

            </table>
        </div>

        <a href="{{route('purchase_order.index')}}" class="btn btn-dark mt-2">Back</a>

    </div>
</div>

<script>
    $(document).ready(function(){
    
        // ===================== CHECK ALL =====================
        $("#checkAll").on("change", function(){
            const checked = $(this).is(":checked");
            $(".chkProduct").prop("checked", checked);
        });
    
        // ===================== PRINT QR =====================
        $("#btnPrintQR").on("click", function(){
            const selected = $(".chkProduct:checked");
    
            if(selected.length === 0){
                alert("Pilih minimal 1 produk");
                return;
            }
    
            // ==== Jika hanya 1 produk → butuh input nomor urut ====
            if(selected.length === 1){
                const idDetail = selected.val();
    
                $.get(`/qr/sequence/${idDetail}`, function(res){
    
                    $("#btnSubmitSequence").off().on("click", function(){
                        const nomor = $("#selectedSequence").val();
                        window.location.href = `/po/{{ $purchase_order->id }}/qr/pdf?detail=${idDetail}&seq=${nomor}`;
                    });
    
                });
    
                return;
            }
    
            // ==== Jika multiple → langsung print tanpa modal ====
            const ids = selected.map(function(){ return $(this).val(); }).get().join(",");
    
            window.location.href = `/po/{{ $purchase_order->id }}/qr/pdf?multi=${ids}`;
        });
    
    });
    </script>
@endsection
