@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Umur Stok</h6>

        {{-- tombol buka modal TANPA bootstrap js --}}
        <button class="btn btn-primary btn-sm" id="btnFilter">
            <i class="fa fa-filter"></i> Filter & Export
        </button>
    </div>

    <div class="card-body">
        <table class="table table-bordered" id="stockAgingTable" width="100%">
            <thead class="bg-light">
                <tr class="text-center">
                    <th>No</th>
                    <th>SKU</th>
                    <th>Nama Barang</th>
                    <th>Stok Saat Ini</th>
                    <th>Tgl Masuk Pertama</th>
                    <th>Umur (Hari)</th>
                    <th>Kategori Aging</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

{{-- MODAL (MANUAL CONTROL, NO BOOTSTRAP JS) --}}
<div class="modal fade" id="filterModal" style="display:none;">
    <div class="modal-dialog">
        <form id="filterForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Filter Umur Stok</h5>
                    <button type="button" class="close btnCloseModal">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Kategori Aging</label>
                        <select name="aging_bucket" class="form-control">
                            <option value="">-- Semua --</option>
                            <option value="0-30">0 - 30 Hari</option>
                            <option value="31-60">31 - 60 Hari</option>
                            <option value="61-90">61 - 90 Hari</option>
                            <option value=">90">> 90 Hari</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Aksi</label>
                        <select name="action" class="form-control">
                            <option value="show">Tampilkan Data</option>
                            <option value="export">Export Excel</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Proses</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let table;

/* ======================
   DATATABLE INIT
====================== */
function loadTable() {
    table = $('#stockAgingTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('stock_aging.data') }}",
            data: function (d) {
                d.aging_bucket = $('select[name=aging_bucket]').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable:false, searchable:false },
            { data: 'sku' },
            { data: 'nama_barang' },
            { data: 'stock_on_hand', className:'text-right' },
            { data: 'first_in_date', className:'text-center' },
            { data: 'aging_days', className:'text-center' },
            { data: 'badge', orderable:false, searchable:false }
        ]
    });
}

$(document).ready(loadTable);

/* ======================
   MODAL MANUAL HANDLER
====================== */
function openModal() {
    $('#filterModal').show().addClass('show');
    $('body').addClass('modal-open');
}

function closeModal() {
    $('#filterModal').hide().removeClass('show');
    $('body').removeClass('modal-open');
    $('.modal-backdrop').remove();
}

$('#btnFilter').on('click', openModal);
$('.btnCloseModal').on('click', closeModal);

/* ======================
   FORM SUBMIT
====================== */
$('#filterForm').on('submit', function(e){
    e.preventDefault();

    const action = $('select[name=action]').val();
    const params = $(this).serialize();
    const exportUrl = "{{ route('stock_aging.export') }}?" + params;

    closeModal();

    if (action === 'show') {
        table.ajax.reload();
        return;
    }

    Swal.fire({
        title: 'Export Umur Stok?',
        text: 'File Excel akan dibuat sesuai filter.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Export'
    }).then(res => {
        if (res.isConfirmed) {
            window.open(exportUrl, '_blank');
        }
    });
});
</script>
@endsection
