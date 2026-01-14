@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">TambahPengiriman Barang</h6>
    </div>
    <div class="card-body">

        <form id="formDO">
            @csrf

            {{-- Tanggal DO --}}
            <div class="mb-3">
                <label>Tanggal *</label>
                <input type="date" class="form-control" name="tgl_do" required>
            </div>

            {{-- Nomor DO --}}
            <div class="mb-3"> 
                <label>Number</label> 
                <div class="input-group"> 
                    <input class="form-control" id="no_do" name="no_do" type="text" placeholder="Masukkan Nomor" required> 
                    <div class="input-group-append"> 
                        <div class="input-group-text"> 
                            <input type="checkbox" id="autoGenerate"> Auto 
                        </div> 
                    </div> 
                </div> 
            </div> 
            <div class="validation"></div> 
            @error('no_do') 
            <div class="alert alert-danger">{{ $message }}</div> 
            @enderror

            <div class="mb-3">
                <label>Metode Pengiriman *</label>
                <select class="form-control" name="shipping_via" required>
                    @foreach($shipping_via as $k=>$v)
                        <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Reason --}}
            <div class="mb-3">
                <label>Catatan *</label>
                <textarea class="form-control" name="reason_do" required></textarea>
            </div>

            {{-- Produk --}}
            <div class="table-responsive">
                <label>Produk</label>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Nama Barang</th>
                            <th>Qty</th>
                            <th>Qty Tersedia</th> <!-- kolom baru -->
                            <th style="width:60px" class="text-center align-middle">
                                <a class="btn btn-primary btn-sm" id="addrow">
                                    <i class="fa fa-plus"></i>
                                </a>
                            </th>
                        </tr>
                    </thead>                    
                    <tbody id="append_akun"></tbody>
                </table>
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('delivery_order.index') }}" class="btn btn-dark">Kembali</a>
        </form>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function(){
    
    $(document).on('change', '#autoGenerate', function() {
        if ($(this).is(':checked')) {
            let tgl = $('input[name="tgl_do"]').val();
            if (!tgl) {
                alert("Pilih Tanggal DO terlebih dahulu");
                $(this).prop('checked', false);
                return;
            }
            $.ajax({
                url: '/delivery-order/autogen',
                data: { tgl_do: tgl },
                success: function(res) {
                    if(res.no_do){
                        $('#no_do').val(res.no_do);
                        // Set field jadi readonly
                        $('#no_do').prop('readonly', true);
                    } else {
                        alert('Failed to generate Nomor!');
                        $('#autoGenerate').prop('checked', false);
                    }
                },
                error: function() {
                    alert('Error while generating Nomor!');
                    $('#autoGenerate').prop('checked', false);
                }
            });
        } else {
            // Kosongkan dan buat editable lagi
            $('#no_do').val('');
            $('#no_do').prop('readonly', false);
        }
    });

    $('.select2').select2({ width:'100%' });

    const PRODUCTS = @json($products);

    function getSkuOptions(except = []) {
        let opt = `<option value="">-- Pilih SKU --</option>`;
        PRODUCTS.forEach(p => {
            if (!except.includes(p.SKU)) {
                opt += `<option 
                    value="${p.sku}" 
                    data-kode="${p.sku}" 
                    data-nama="${p.nama_barang}">${p.SKU}</option>`;
            }
        });
        return opt;
    }

    // Auto-generate Nomor
    $('input[name="tgl_do"]').change(function(){
        let tgl = $(this).val();
        if(!tgl) return;

        $.get("{{ route('delivery_order.autoGenerate') }}", { tgl_do: tgl }, function(res){
            if(res.no_do){
                $('input[name="no_do"]').val(res.no_do);
            }
        });
    });

    // Add Row
    $('#addrow').click(function(e){
        e.preventDefault();
        let usedSku = [];
        $('.sku').each(function(){ if($(this).val()) usedSku.push($(this).val()); });

        let row = `<tr>
            <td>
                <select class="form-control select2 sku" name="sku[]" required>
                    ${getSkuOptions(usedSku)}
                </select>
            </td>
            <td><input type="text" class="form-control nama_barang" name="nama_barang[]" readonly></td>
            <td><input type="number" class="form-control qty text-right" name="qty[]" min="1" required></td>
            <td><input type="number" class="form-control qty_tersedia text-right" readonly></td>
            <td class="text-center align-middle">
                <a class="btn btn-danger btn-sm btn-remove">
                    <i class="fa fa-minus"></i>
                </a>
            </td>
        </tr>`;

        $('#append_akun').append(row);
        $('#append_akun').find('.select2').last().select2({ width:'100%' });
    });

    // On change SKU
    $('#append_akun').on('change','.sku',function(){
        let row = $(this).closest('tr');
        let opt = $(this).find(':selected');
        let kode = opt.data('kode') || '';
        row.find('.sku').val(kode);
        row.find('.nama_barang').val(opt.data('nama') || '');
        
        // Ambil qty tersedia via AJAX
        if(kode){
            $.ajax({
                url: '/delivery-order/stock', // endpoint baru
                data: { sku: kode },
                success: function(res){
                    row.find('.qty_tersedia').val(res.qty_tersedia || 0);
                },
                error: function(){
                    row.find('.qty_tersedia').val(0);
                }
            });
        } else {
            row.find('.qty_tersedia').val(0);
        }

        refreshSku();
    });

    function refreshSku(){
        let selected = [];
        $('.sku').each(function(){ if($(this).val()) selected.push($(this).val()); });
        $('.sku').each(function(){
            let current = $(this).val();
            let except = selected.filter(v => v !== current);
            $(this).html(getSkuOptions(except));
            $(this).val(current);
            $(this).trigger('change.select2');
        });
    }

    // Remove row
    $('#append_akun').on('click','.btn-remove',function(e){
        e.preventDefault();
        $(this).closest('tr').remove();
        refreshSku();
    });

    // Batasi qty agar tidak lebih dari qty_tersedia
    $('#append_akun').on('input', '.qty', function() {
        let row = $(this).closest('tr');
        let qtyTersedia = parseFloat(row.find('.qty_tersedia').val()) || 0;
        let qtyInput = parseFloat($(this).val()) || 0;

        if(qtyInput > qtyTersedia){
            $(this).val(qtyTersedia);
            Swal.fire({
                icon: 'warning',
                title: 'Qty melebihi stok',
                text: `Qty tidak boleh lebih dari ${qtyTersedia}`
            });
        } else if(qtyInput < 0){
            $(this).val(0);
        }
    });

    // Submit via AJAX
    $('#formDO').submit(function(e){
        e.preventDefault();
        Swal.fire({title:'Processing...', allowOutsideClick:false, didOpen:()=>Swal.showLoading()});

        $.ajax({
            url: "{{ route('delivery_order.store') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function(res){
                Swal.close();
                if(res.status === 'success'){
                    Swal.fire({icon:'success', title:'Success', text:res.message, timer:1500, showConfirmButton:false})
                        .then(()=> window.location.href = "{{ route('delivery_order.index') }}");
                } else {
                    Swal.fire('Warning', res.message || 'Gagal', 'warning');
                }
            },
            error: function(xhr){
                Swal.close();
                Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
            }
        });
    });

});
</script>
@endsection
