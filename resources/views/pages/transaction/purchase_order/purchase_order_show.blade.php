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
        
        @if(!in_array($purchase_order->status_po, ['0', '1', '4']))
            <button type="button" class="btn btn-primary" id="btnPrintQR">
                <i class="fas fa-print"></i> Print QR
            </button>
        @endif
        {{-- ===================== INPUT NOMOR URUT (INLINE) ===================== --}}
        <div id="sequenceWrapper" class="mb-3 d-none">
            <label class="font-weight-bold">
                Nomor Urut
                <small class="text-muted">(contoh: 1-10 atau 3,7)</small>
            </label>
            <input type="text"
                id="selectedSequence"
                class="form-control col-md-4"
                placeholder="1-10 atau 3,7">
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
        <!-- ===================== LOADING SPINNER ===================== -->
        <div id="printLoading"
            style="
                display:none;
                position:fixed;
                z-index:9999;
                inset:0;
                background:rgba(0,0,0,.45);
                align-items:center;
                justify-content:center;
            ">
            <div class="bg-white p-4 rounded shadow text-center">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <div class="font-weight-bold">Menyiapkan Print QR...</div>
                <small class="text-muted">Mohon tunggu sebentar</small>
            </div>
        </div>

        <!-- ===================== HIDDEN IFRAME ===================== -->
        <iframe id="printFrame" style="display:none;" width="0" height="0"></iframe>

    </div>
</div>

<script>
    $(document).ready(function(){
        const seqWrapper = $("#sequenceWrapper");
        const seqInput   = $("#selectedSequence");

        // ===================== CHECK ALL =====================
        $("#checkAll").on("change", function(){
            const checked = $(this).is(":checked");

            $(".chkProduct").prop("checked", checked);

            if(checked){
                hideSequence();
            }
        });

        // ===================== MANUAL CHECK =====================
        $(".chkProduct").on("change", function(){

            const total       = $(".chkProduct").length;
            const checkedList = $(".chkProduct:checked").length;

            // auto uncheck checkAll
            if(checkedList < total){
                $("#checkAll").prop("checked", false);
            }

            // show / hide nomor urut
            if(checkedList === 1){
                showSequence();
            } else {
                hideSequence();
            }

            // kalau semua dicentang manual
            if(checkedList === total){
                $("#checkAll").prop("checked", true);
                hideSequence();
            }
        });

        // ===================== PRINT QR =====================
        $("#btnPrintQR").on("click", function(){
            const selected   = $(".chkProduct:checked");
            const totalItem  = $(".chkProduct").length;
            const isCheckAll = $("#checkAll").is(":checked") && selected.length === totalItem;

            if(selected.length === 0){
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops',
                    text: 'Pilih minimal 1 produk'
                });
                return;
            }

            // ===================== CHECK ALL =====================
            if(isCheckAll){
                Swal.fire({
                    title: 'Cetak QR Semua Produk?',
                    text: 'QR akan digenerate untuk seluruh item dalam PO ini',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Cetak',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if(result.isConfirmed){
                        printViaIframe(`/po/{{ $purchase_order->id }}/qr/pdf`);
                    }
                });
                return;
            }

            // ===================== SINGLE =====================
            if(selected.length === 1){

                const idDetail = selected.val();
                const seq = $("#selectedSequence").val();

                if(!seq){
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nomor urut wajib diisi',
                        text: 'Silakan isi nomor urut terlebih dahulu'
                    });
                    $("#selectedSequence").focus();
                    return;
                }

                printViaIframe(
                    `/po/{{ $purchase_order->id }}/qr/pdf?detail=${idDetail}&seq=${seq}`
                );
                return;
            }

            // ===================== MULTIPLE =====================
            const ids = selected.map(function(){
                return $(this).val();
            }).get().join(",");

            Swal.fire({
                title: 'Cetak QR Produk Terpilih?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Cetak',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if(result.isConfirmed){
                    printViaIframe(
                        `/po/{{ $purchase_order->id }}/qr/pdf?multi=${ids}`
                    );
                }
            });
        });

        // ===================== HELPER =====================
        function showSequence(){
            seqWrapper.removeClass('d-none');
            seqInput.focus();
        }

        function hideSequence(){
            seqWrapper.addClass('d-none');
            seqInput.val('');
        }

        function printViaIframe(url){

            const iframe = document.getElementById('printFrame');
            const loading = document.getElementById('printLoading');

            // reset iframe
            iframe.src = '';
            iframe.onload = null;

            // show loading
            loading.style.display = 'flex';

            iframe.src = url;

            iframe.onload = function(){

                const win = iframe.contentWindow;

                // kecilkan delay biar PDF ke-render dulu
                setTimeout(function(){

                    win.focus();
                    win.print();

                    // ===================== AUTO CLOSE =====================
                    let closed = false;

                    const cleanup = () => {
                        if (closed) return;
                        closed = true;

                        loading.style.display = 'none';
                        iframe.src = '';
                        window.removeEventListener('focus', onFocusBack);
                    };

                    // Chrome / Edge / Firefox (user close print dialog)
                    const onFocusBack = () => {
                        setTimeout(cleanup, 300);
                    };

                    window.addEventListener('focus', onFocusBack);

                    // fallback safety (kalau browser gak trigger focus)
                    setTimeout(cleanup, 15000);

                }, 600);
            };
        }

    });
    </script>
@endsection
