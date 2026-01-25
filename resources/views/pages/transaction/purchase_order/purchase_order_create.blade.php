@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Pemesanan Barang</h6>
    </div>
    <div class="card-body">

        <form id="formPO">
            @csrf

            {{-- Supplier --}}
            <div class="mb-3">
                <label>Pemasok *</label>
                <select class="form-control select2" name="id_supplier" required>
                    <option value="">-- Pilih Pemasok --</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}">
                            {{ $sup->code_spl }} - {{ $sup->nama_spl }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tanggal PO --}}
            <div class="mb-3">
                <label>Tanggal *</label>
                <input type="date" class="form-control" name="tgl_po" required>
            </div>

            <div class="mb-3">
                <label>Jenis PO *</label><br>
            
                <div class="form-check form-check-inline">
                    <input class="form-check-input po-type" type="radio" 
                           name="po_type" value="baru" checked>
                    <label class="form-check-label">Baru</label>
                </div>
            
                <div class="form-check form-check-inline">
                    <input class="form-check-input po-type" type="radio" 
                           name="po_type" value="tambahan">
                    <label class="form-check-label">Tambahan</label>
                </div>
            </div>

            <div class="mb-3 d-none" id="basePoWrapper">
                <label>Pilih PO Tambahan *</label>
                <select class="form-control select2" name="base_po_id" id="base_po_select">
                    <option value="">-- Pilih PO --</option>
                </select>
            </div>            

            {{-- Nomor PO --}}
            <div class="mb-3">
                <label>PO Number *</label>
                <input type="text" class="form-control" name="no_po" required>
            </div>

            {{-- Note --}}
            <div class="mb-3">
                <label>Catatan</label>
                <textarea class="form-control" name="reason_po" required></textarea>
            </div>

            {{-- Produk --}}
            <div class="table-responsive">
                <label>Barang</label>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Nama Barang</th>
                            <th style="width:90px">Qty</th>
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
            <a href="{{ route('purchase_order.index') }}" class="btn btn-dark">Kembali</a>
        </form>

    </div>
</div>

{{-- JS --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function(){

    $('.select2').select2({ width:'100%' });

    // ============================
    // DATA PRODUCT DARI BACKEND
    // ============================
    const PRODUCTS = @json(
        $products->map(function($p){
            return [
                'sku' => $p->sku,
                'nama_barang' => $p->nama_barang
            ];
        })
    );

    // ============================
    // OPTION SKU (ANTI DUPLIKAT)
    // ============================
    function getSkuOptions(except = []) {
        let opt = `<option value="">-- Pilih SKU --</option>`;

        PRODUCTS.forEach(p => {
            if (!except.includes(p.sku)) {
                opt += `
                    <option 
                        value="${p.sku}"
                        data-kode="${p.sku}"
                        data-nama="${p.nama_barang}">
                        ${p.sku}
                    </option>`;
            }
        });

        return opt;
    }

    // ============================
    // ADD ROW
    // ============================
    $('#addrow').click(function(e){
        e.preventDefault();

        let usedKodeBarang = [];
        $('.sku_select').each(function(){
            if ($(this).val()) usedKodeBarang.push($(this).val());
        });

        let row = `
        <tr>
            <td>
                <select class="form-control select2 sku_select" 
                        name="sku[]" required>
                    ${getSkuOptions(usedKodeBarang)}
                </select>
            </td>

            <td>
                <input type="text" class="form-control nama_barang" 
                    name="nama_barang[]" readonly>
            </td>

            <td>
                <input type="number" class="form-control qty text-right" 
                    name="qty[]" min="1" required>
            </td>

            <td class="text-center align-middle">
                <a class="btn btn-danger btn-sm btn-remove">
                    <i class="fa fa-minus"></i>
                </a>
            </td>
        </tr>`;

        $('#append_akun').append(row);
        $('#append_akun').find('.select2').last().select2({ width:'100%' });
    });

    // ============================
    // ON CHANGE SKU
    // ============================
    $('#append_akun').on('change','.sku_select',function(){
        let row = $(this).closest('tr');
        let opt = $(this).find(':selected');

        row.find('.nama_barang').val(opt.data('nama') || '');

        refreshSku();
    });

    // ============================
    // REFRESH SKU OPTION
    // ============================
    function refreshSku(){
        let selected = [];

        $('.sku_select').each(function(){
            if ($(this).val()) selected.push($(this).val());
        });

        $('.sku_select').each(function(){
            let current = $(this).val();
            let except  = selected.filter(v => v !== current);

            $(this).html(getSkuOptions(except));
            $(this).val(current);
            $(this).trigger('change.select2');
        });
    }

    // ============================
    // REMOVE ROW
    // ============================
    $('#append_akun').on('click','.btn-remove',function(e){
        e.preventDefault();
        $(this).closest('tr').remove();
        refreshSku();
    });

    function loadExistingPO() {
        $.get("{{ route('purchase_order.list_existing') }}", function(res){
            let opt = `<option value="">-- Pilih PO --</option>`;
            res.forEach(po => {
                opt += `<option value="${po.id}" data-no="${po.no_po}">
                            ${po.no_po}
                        </option>`;
            });
            $('#base_po_select').html(opt).select2({ width:'100%' });
        });
    }

    // ===============================
    // TOGGLE PO TYPE
    // ===============================
    $('.po-type').change(function(){
        const type = $(this).val();

        if (type === 'tambahan') {
            $('#basePoWrapper').removeClass('d-none');
            loadExistingPO();
            $('input[name="no_po"]').prop('readonly', true);
        } else {
            $('#basePoWrapper').addClass('d-none');
            $('#base_po_select').val(null).trigger('change');
            $('input[name="no_po"]').prop('readonly', false).val('');
        }
    });

    // ===============================
    // SET PREFIX T-
    // ===============================
    $('#base_po_select').on('change', function(){
        let noPo = $(this).find(':selected').data('no');
        if (noPo) {
            $('input[name="no_po"]').val('T-' + noPo);
        }
    });

    // ============================
    // SUBMIT AJAX
    // ============================
    $('#formPO').submit(function(e){
        e.preventDefault();

        Swal.fire({
            title: 'Memproses...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: "{{ route('purchase_order.store') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function(res){
                Swal.close();
                if(res.status === 'success'){
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = "{{ route('purchase_order.index') }}";
                    });
                } else {
                    Swal.fire('Warning', res.message || 'Gagal', 'warning');
                }
            },
            error: function(xhr){
                Swal.close();
                Swal.fire(
                    'Error',
                    xhr.responseJSON?.message || 'Terjadi kesalahan',
                    'error'
                );
            }
        });
    });

});
</script>
@endsection
