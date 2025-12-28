@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            Stock Aging
        </h6>
        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#filterModal">
            <i class="fa fa-filter"></i> Filter
        </button>
    </div>

    <div class="card-body">
        <div class="table-responsive">
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
</div>

{{-- ================= MODAL FILTER ================= --}}
<div class="modal fade" id="filterModal">
    <div class="modal-dialog">
        <form id="filterForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Filter Stock Aging</h5>
                    <button class="close" data-dismiss="modal">&times;</button>
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
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">
                        <i class="fa fa-search"></i> Terapkan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ================= SCRIPT ================= --}}
<script>
let table;

function loadData() {
    table = $('#stockAgingTable').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
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
            { data: 'stock_on_hand', className:'text-right', searchable:false },
            { data: 'first_in_date', className:'text-center', searchable:false },
            { data: 'aging_days', className:'text-center', searchable:false },
            { data: 'badge', orderable:false, searchable:false }
        ]
    });
}

/* AUTO LOAD */
$(document).ready(function () {
    loadData();
});

/* FILTER */
$('#filterForm').on('submit', function (e) {
    e.preventDefault();
    $('#filterModal').modal('hide');
    table.ajax.reload();
});
</script>
@endsection
