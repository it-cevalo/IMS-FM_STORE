@extends('layouts.admin')

@section('content')  
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <a href="{{route('purchase_order.index')}}">Stock In</a>
        </h6>
    </div>

    <div class="card-body">

        <div class="mb-3">
            <label>Pemasok</label>
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
            <label>Tanggal</label>
            <input class="form-control" 
                name="tgl_po" 
                value="{{ \Carbon\Carbon::parse($purchase_order->tgl_po)->format('Y-m-d')}}"
                type="date" 
                disabled>
        </div>

        <div class="mb-3">
            <label>Nomor</label>
            <input class="form-control" 
                name="no_po" 
                value="{{$purchase_order->no_po}}" 
                type="text" 
                disabled>
        </div>

        {{-- <div class="mb-3">
            <label>Status</label>
            <select class="form-control form-control-sm" name="status_po" disabled>
                @foreach($status_po as $k => $v)
                    <option value="{{ $k }}" {{ $purchase_order->status_po == $k ? 'selected' : '' }}>
                        {{ $v }}
                    </option>
                @endforeach
            </select>
        </div> --}}

        <div class="mb-3">
            <label>Catatan</label>
            <input class="form-control" 
                name="reason_po" 
                value="{{$purchase_order->reason_po}}" 
                type="text" 
                disabled>
        </div>
        
        @if(!in_array($purchase_order->status_po, ['0']))
            <button type="button" class="btn btn-primary" id="btnPrintQR">
                <i class="fas fa-print"></i> Cetak QR
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
            <label>Barang</label>
            <table class="table table-bordered" width="100%">
                <thead>
                    <tr>
                        <th class="text-center">
                            <input type="checkbox" id="checkAll">
                        </th>
                        <th class="text-center align-middle">SKU</th>
                        {{-- <th class="text-center align-middle">Kode Barang</th> --}}
                        <th class="text-center align-middle">Nama Barang</th>
                        <th class="text-center align-middle">Qty Order</th>
                        <th class="text-center align-middle">Qty Diterima</th>
                        <th class="text-center align-middle">Qty Belum diterima</th>
                    </tr>
                </thead>
                <tbody id="append_akun">
                    @foreach($purchase_order_dtl as $val)
                    <tr class="row-akun">
                    
                        <td class="text-center">
                            <input type="checkbox"
                                class="chkProduct"
                                value="{{ $val->id }}"
                                data-sku="{{ $val->part_number }}">
                        </td>
                    
                        {{-- SKU --}}
                        <td>
                            <select class="form-control sku-select select2" disabled>
                                <option value="">-- Pilih SKU --</option>
                                <option value="{{ $val->part_number }}">
                                    {{ $val->part_number }}
                                </option>
                            </select>
                        </td>
                    
                        {{-- Kode Barang --}}
                        {{-- <td>
                            <input type="text"
                                class="form-control kode-barang"
                                value="{{ $val->part_number }}"
                                readonly>
                        </td> --}}
                    
                        {{-- Nama Barang --}}
                        <td>
                            <input type="text"
                                class="form-control nama-barang"
                                value="{{ $val->product_name }}"
                                readonly>
                        </td>
                    
                        {{-- Qty --}}
                        <td>
                            <input type="number"
                                class="form-control text-right"
                                value="{{ $val->qty }}"
                                readonly>
                        </td>
                        
                        {{-- Qty --}}
                        <td>
                            <input type="number"
                                class="form-control text-right"
                                value="{{ $val->qty_received }}"
                                readonly>
                        </td>

                        
                        {{-- Qty --}}
                        <td>
                            <input type="number"
                                class="form-control text-right"
                                value="{{ $val->qty - $val->qty_received }}"
                                readonly>
                        </td>
                    
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

        <a href="{{route('purchase_order.index')}}" class="btn btn-dark mt-2">Kembali</a>
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
                <div class="font-weight-bold">Menyiapkan Cetak QR...</div>
                <small class="text-muted">Mohon tunggu sebentar</small>
            </div>
        </div>

        <!-- ===================== HIDDEN IFRAME ===================== -->
        <iframe id="printFrame" style="display:none;" width="0" height="0"></iframe>

    </div>
</div>

<script>
    function parseBlobError(xhr, callback) {
        // 1️⃣ kalau responseType = blob dan response ada
        if (xhr.response instanceof Blob) {

            const reader = new FileReader();
            reader.onload = function () {
                try {
                    const json = JSON.parse(reader.result);
                    callback(json);
                } catch (e) {
                    callback({ message: reader.result });
                }
            };
            reader.readAsText(xhr.response);
            return;
        }

        // 2️⃣ fallback: responseText
        if (xhr.responseText) {
            try {
                const json = JSON.parse(xhr.responseText);
                callback(json);
            } catch (e) {
                callback({ message: xhr.responseText });
            }
            return;
        }

        // 3️⃣ benar-benar kosong
        callback({ message: null });
    }

    function compressSequences(seqs) {
        const nums = [...new Set(seqs.map(s => parseInt(s, 10)))]
            .sort((a, b) => a - b);

        const ranges = [];
        let start = null, prev = null;

        nums.forEach(n => {
            if (start === null) {
                start = prev = n;
                return;
            }
            if (n === prev + 1) {
                prev = n;
            } else {
                ranges.push(start === prev ? `${pad(start)}` : `${pad(start)}–${pad(prev)}`);
                start = prev = n;
            }
        });

        if (start !== null) {
            ranges.push(start === prev ? `${pad(start)}` : `${pad(start)}–${pad(prev)}`);
        }

        return ranges.join(', ');
    }

    function pad(n) {
        return n.toString().padStart(4, '0');
    }
    $(document).ready(function(){
    
        /*
        |--------------------------------------------------------------------------
        | VARIABEL TAMBAHAN (REPRINT CONTEXT)
        |--------------------------------------------------------------------------
        | Dipakai hanya untuk request reprint
        | Tidak mengganggu logic lama
        */
        let selectedDetailId = null;
        let selectedSeq      = null;
    
        const seqWrapper = $("#sequenceWrapper");
        const seqInput   = $("#selectedSequence");
        
        // ===================== INIT SKU SELECT =====================
        $('.sku-select').each(function(){
            const sku = $(this).find('option:eq(1)').val();

            $(this)
                .val(sku)
                .trigger('change')
                .select2({ width:'100%' });
        });

        // ===================== CHECK ALL =====================
        $("#checkAll").on("change", function(){
            const checked = $(this).is(":checked");
            $(".chkProduct").prop("checked", checked);
            if(checked) hideSequence();
        });
    
        // ===================== MANUAL CHECK =====================
        $(".chkProduct").on("change", function(){
            const total       = $(".chkProduct").length;
            const checkedList = $(".chkProduct:checked").length;
    
            if(checkedList < total){
                $("#checkAll").prop("checked", false);
            }
    
            if(checkedList === 1){
                showSequence();
            } else {
                hideSequence();
            }
    
            if(checkedList === total){
                $("#checkAll").prop("checked", true);
                hideSequence();
            }
        });
    
        // ===================== Cetak QR (EXTENDED WITH REPRINT FLOW) =====================
        $("#btnPrintQR").on("click", function () {
            const selected   = $(".chkProduct:checked");
            const totalItem  = $(".chkProduct").length;
            const isCheckAll = $("#checkAll").is(":checked") && selected.length === totalItem;

            if (selected.length === 0) {
                Swal.fire('Oops', 'Pilih minimal 1 produk', 'warning');
                return;
            }

            // ===============================
            // 1️⃣ VALIDATE DULU (SWAL INFO)
            // ===============================
            Swal.fire({
                title: 'Validasi QR',
                text: 'Sedang memvalidasi data QR, mohon tunggu...',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });
            const selectedIds = selected.map(function () {
                return $(this).val();
            }).get().join(",");

            $.get(`/po/{{ $purchase_order->id }}/qr/validate`, {
                details: selectedIds
            }).done(function (res) {

                    Swal.close();

                    // ===============================
                    // ❌ ADA QR SUDAH TERCETAK
                    // ===============================
                    if (!res.allowed) {

                        const conflicts = res.conflicts || [];

                        // GROUP BY SKU
                        const grouped = {};
                        conflicts.forEach(c => {
                            const key = `${c.product_name}||${c.sku}`;
                            if (!grouped[key]) grouped[key] = [];
                            grouped[key].push(c.sequence);
                        });

                        // BUILD RINGKASAN
                        let list = Object.entries(grouped).map(([key, seqs]) => {
                            const [name, sku] = key.split('||');
                            const rangeText = compressSequences(seqs);
                            return `• <b>${name}</b> (${sku}) → <b>${rangeText}</b>`;
                        }).join('<br>');

                        Swal.fire({
                            title: 'Cetak QR Diblokir',
                            html: `
                                <div style="text-align:left;font-size:14px;line-height:1.6">
                                    <p>
                                        <b>QR berikut sudah pernah tercetak.</b><br>
                                        Silakan ajukan <b>cetak ulang</b>.
                                    </p>
                                    <hr>
                                    ${list}
                                </div>
                            `,
                            icon: 'warning',
                            input: 'textarea',
                            inputPlaceholder: 'Alasan cetak ulang',
                            showCancelButton: true,
                            confirmButtonText: 'Ajukan Cetak Ulang',
                            cancelButtonText: 'Batal',
                            preConfirm: (reason) => {
                                if (!reason) {
                                    Swal.showValidationMessage('Alasan wajib diisi');
                                    return false;
                                }
                                return reason;
                            }
                        }).then(r => {

                            if (!r.isConfirmed) return;

                            $.post('/qr/reprint/request', {
                                id_po  : {{ $purchase_order->id }},
                                reason : r.value,
                                _token : '{{ csrf_token() }}',
                                items  : conflicts
                            })
                            .done(resp => {
                                Swal.fire(
                                    'Berhasil',
                                    resp.message || 'Pengajuan cetak ulang berhasil dikirim.',
                                    'success'
                                );
                            })
                            .fail(xhr => {

                                let msg = 'Gagal mengajukan cetak ulang.';
                                if (xhr.responseJSON?.code === 'REPRINT_PENDING') {
                                    msg = xhr.responseJSON.message;
                                } else if (xhr.responseJSON?.message) {
                                    msg = xhr.responseJSON.message;
                                }

                                Swal.fire('Gagal', msg, 'error');
                            });
                        });

                        return;
                    }

                    // ===============================
                    // 2️⃣ KONFIRMASI CETAK (SWAL)
                    // ===============================
                    Swal.fire({
                        title: 'Cetak QR',
                        text: 'Validasi berhasil. Lanjutkan proses cetak QR?',
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Cetak',
                        cancelButtonText: 'Batal'
                    }).then(result => {

                        if (!result.isConfirmed) return;

                        let url = null;

                        // ===============================
                        // PRINT ALL
                        // ===============================
                        if (isCheckAll) {
                            url = `/po/{{ $purchase_order->id }}/qr/pdf`;
                        }
                        // ===============================
                        // SINGLE
                        // ===============================
                        else if (selected.length === 1) {

                            const detailId = selected.val();
                            const seq      = $("#selectedSequence").val();

                            if (!seq) {
                                Swal.fire('Wajib', 'Nomor urut harus diisi', 'warning');
                                return;
                            }

                            url = `/po/{{ $purchase_order->id }}/qr/pdf?detail=${detailId}&seq=${seq}`;
                        }
                        // ===============================
                        // MULTI
                        // ===============================
                        else {
                            const ids = selected.map(function () {
                                return $(this).val();
                            }).get().join(",");

                            url = `/po/{{ $purchase_order->id }}/qr/pdf?multi=${ids}`;
                        }

                        // ===============================
                        // OPEN PDF
                        // ===============================
                        Swal.fire({
                            title: 'Menyiapkan Cetak',
                            text: 'Membuka dokumen PDF...',
                            icon: 'info',
                            timer: 1200,
                            showConfirmButton: false
                        });

                        window.open(url, '_blank');
                    });
                })
                .fail(() => {
                    Swal.close();
                    Swal.fire('Error', 'Gagal memvalidasi QR', 'error');
                });
        });
        // $("#btnPrintQR").on("click", function(){

        //     $.get(`/po/{{ $purchase_order->id }}/qr/validate`)
        //         .done(function(res){

        //             if (!res.allowed) {

        //                 const conflicts = res.conflicts || [];

        //                 // 🔥 GROUP BY SKU
        //                 const grouped = {};
        //                 conflicts.forEach(c => {
        //                     const key = `${c.product_name}||${c.sku}`;
        //                     if (!grouped[key]) {
        //                         grouped[key] = [];
        //                     }
        //                     grouped[key].push(c.sequence);
        //                 });

        //                 // 🔥 BUILD RINGKASAN
        //                 let list = Object.entries(grouped).map(([key, seqs]) => {
        //                     const [name, sku] = key.split('||');
        //                     const rangeText = compressSequences(seqs);
        //                     return `• <b>${name}</b> (${sku}) → <b>${rangeText}</b>`;
        //                 }).join('<br>');

        //                 Swal.fire({
        //                     title: 'Cetak QR Diblokir',
        //                     html: `
        //                         <div style="text-align:left;font-size:14px;line-height:1.6">
        //                             <p>
        //                                 <b>QR dari daftar berikut sudah pernah tercetak.</b><br>
        //                                 Silakan melakukan <b>pengajuan cetak ulang</b>.
        //                             </p>
        //                             <hr style="margin:10px 0">
        //                             ${list}
        //                         </div>
        //                     `,
        //                     icon : 'warning',
        //                     input: 'textarea',
        //                     inputPlaceholder: 'Alasan cetak ulang',
        //                     showCancelButton: true
        //                 }).then(r => {
        //                     if (!r.isConfirmed) return;

        //                     $.post('/qr/reprint/request', {
        //                         id_po  : {{ $purchase_order->id }},
        //                         reason : r.value,
        //                         _token : '{{ csrf_token() }}',
        //                         items  : res.conflicts
        //                     })
        //                     .done(function(resp){
        //                         Swal.fire({
        //                             icon: 'success',
        //                             title: 'Berhasil',
        //                             text: resp.message || 'Pengajuan cetak ulang berhasil dikirim.'
        //                         });
        //                     })
        //                     .fail(function(xhr){

        //                         let msg = 'Gagal mengajukan cetak ulang.';

        //                         if (xhr.responseJSON?.code === 'REPRINT_PENDING') {
        //                             msg = xhr.responseJSON.message;
        //                         } else if (xhr.responseJSON?.message) {
        //                             msg = xhr.responseJSON.message;
        //                         }

        //                         Swal.fire({
        //                             icon: 'error',
        //                             title: 'Tidak Dapat Mengajukan',
        //                             text: msg
        //                         });
        //                     });
        //                 });

        //                 return;
        //             }

        //             // ✅ AMAN CETAK PDF
        //             window.open(`/po/{{ $purchase_order->id }}/qr/pdf`, '_blank');
        //         });
        // });
    
        // ===================== HELPER (EXISTING, TIDAK DIUBAH) =====================
        function showSequence(){
            seqWrapper.removeClass('d-none');
            seqInput.focus();
        }
    
        function hideSequence(){
            seqWrapper.addClass('d-none');
            seqInput.val('');
        }
    
    });
</script> 
@endsection
