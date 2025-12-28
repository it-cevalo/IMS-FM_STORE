@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            Stock Movement
        </h6>
        {{-- <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#filterModal">
            <i class="fa fa-filter"></i> Filter
        </button> --}}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="stockMovementTable" width="100%">
                <thead class="bg-light">
                    <tr class="text-center">
                        <th>No</th>
                        <th>SKU</th>
                        <th>Nama Barang</th>
                        <th>Qty In</th>
                        <th>Qty Out</th>
                        <th>Last Out</th>
                        <th>Movement Rate</th>
                        <th>Status</th>
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
                    <h5 class="modal-title">Filter Stock Movement</h5>
                    <button class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Dari Tanggal</label>
                        <input type="date" class="form-control" name="fd">
                    </div>

                    <div class="form-group">
                        <label>Sampai Tanggal</label>
                        <input type="date" class="form-control" name="td">
                    </div>

                    <div class="form-group">
                        <label>Status Pergerakan</label>
                        <select name="movement_type" class="form-control">
                            <option value="">-- Semua --</option>
                            <option value="FAST">Fast Moving</option>
                            <option value="SLOW">Slow Moving</option>
                            <option value="DEAD">Dead Stock</option>
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
    table = $('#stockMovementTable').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: {
            url: "{{ route('stock_movement.data') }}",
            data: function (d) {
                d.fd = $('input[name=fd]').val();
                d.td = $('input[name=td]').val();
                d.movement_type = $('select[name=movement_type]').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', searchable: false, orderable: false },
            { data: 'sku' },
            { data: 'nama_barang' },
            { data: 'qty_in', searchable: false },
            { data: 'qty_out', searchable: false },
            { data: 'last_out_date', searchable: false },
            { data: 'movement_rate', searchable: false },
            { data: 'badge', searchable: false, orderable: false }
        ]
    });
}

function reloadData() {
    table.ajax.reload();
}

/* =======================
   AUTO LOAD PAGE
======================= */
$(document).ready(function () {
    loadData();
});

/* =======================
   FILTER SUBMIT
======================= */
$('#filterForm').on('submit', function (e) {
    e.preventDefault();
    $('#filterModal').modal('hide');
    reloadData();
});
</script>
@endsection
