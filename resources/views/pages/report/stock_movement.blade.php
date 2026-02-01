@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            Pergerakan Barang
        </h6>

        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#filterModal">
            <i class="fa fa-filter"></i> Filter & Export
        </button>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="stockMovementTable" width="100%">
                <thead class="bg-light">
                    <tr class="text-center">
                        <th>No</th>
                        <th>SKU</th>
                        <th>Nama Barang</th>
                        <th>QTY Masuk</th>
                        <th>QTY Keluar</th>
                        <th>Keluar Terakhir</th>
                        <th>Rata Rata Pergerakan</th>
                        <th>Status</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

{{-- ================= MODAL FILTER ================= --}}
<div class="modal fade" id="filterModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="filterForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Filter Pergerakan Barang</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
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
                            <option value="MEDIUM">Medium Moving</option>
                            <option value="SLOW">Slow Moving</option>
                            <option value="DEAD">Dead Stock</option>
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
                    <button class="btn btn-primary" type="submit">
                        <i class="fa fa-check"></i> Proses
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ================= SWEETALERT2 (SAFE CDN) ================= --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
/**
 * ======================================================
 * SAFETY NET BOOTSTRAP MODAL
 * ======================================================
 */
(function () {
    if (typeof $.fn.modal === 'undefined') {
        var script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js';
        document.head.appendChild(script);
    }
})();
</script>

<script>
let table;

/* =======================
   INIT DATATABLE
======================= */
function loadTable() {
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
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },

            { data: 'sku', name: 'p.sku' },
            { data: 'nama_barang', name: 'p.nama_barang' },

            { data: 'qty_in', searchable: false, orderable: false },
            { data: 'qty_out', searchable: false, orderable: false },
            { data: 'last_out_date', searchable: false, orderable: false },
            { data: 'movement_rate', searchable: false, orderable: false },
            { data: 'badge', orderable: false, searchable: false }
        ]
    });
}

$(document).ready(function () {
    loadTable();
});

/* =======================
   FILTER SUBMIT + SWAL
======================= */
$('#filterForm').on('submit', function (e) {
    e.preventDefault();

    const action = $('select[name=action]').val();
    const params = $(this).serialize();
    const exportUrl = "{{ route('stock_movement.export') }}?" + params;

    // close modal safely
    if ($.fn.modal) {
        $('#filterModal').modal('hide');
    } else {
        $('#filterModal').removeClass('show');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    }

    // ===== SHOW DATA =====
    if (action === 'show') {
        table.ajax.reload();
        return;
    }

    // ===== EXPORT EXCEL (WITH CONFIRMATION) =====
    Swal.fire({
        title: 'Export Pergerakan Barang?',
        text: 'File Excel akan di-generate sesuai filter yang dipilih.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Export',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open(exportUrl, '_blank');
        }
    });
});
</script>
@endsection
