@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-boxes mr-1"></i> Laporan Stock
        </h6>
        <button class="btn btn-primary btn-sm" id="btnFilter">
            <i class="fa fa-filter"></i> Filter & Export
        </button>
    </div>

    <div class="card-body">

        {{-- Info periode aktif --}}
        <div class="alert alert-info py-2 mb-3" id="periodeInfo" style="display:none;">
            <i class="fas fa-calendar-alt mr-1"></i>
            Periode: <strong id="labelPeriode"></strong>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="stockReportTable" width="100%">
                <thead class="bg-light">
                    <tr class="text-center">
                        <th rowspan="2" class="align-middle" style="width:50px">No</th>
                        <th rowspan="2" class="align-middle">SKU</th>
                        <th rowspan="2" class="align-middle">Nama Barang</th>
                        <th rowspan="2" class="align-middle">Last Stock</th>
                        <th colspan="3" class="text-center">Mutasi Periode</th>
                        <th rowspan="2" class="align-middle">Remain</th>
                    </tr>
                    <tr class="text-center">
                        <th>Stock In</th>
                        <th>Stock Out</th>
                        <th>Return</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr class="font-weight-bold bg-light text-center">
                        <td colspan="3" class="text-right">Total</td>
                        <td id="footLastStock">0</td>
                        <td id="footStockIn">0</td>
                        <td id="footStockOut">0</td>
                        <td id="footReturn">0</td>
                        <td id="footRemain">0</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- ===================== MODAL FILTER ===================== --}}
<div class="modal fade" id="filterModal" style="display:none;">
    <div class="modal-dialog">
        <form id="filterForm">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-filter mr-1"></i> Filter Laporan Stock
                    </h5>
                    <button type="button" class="close text-white btnCloseModal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Dari Tanggal <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="fd" id="inputFd" required>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Sampai Tanggal <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="td" id="inputTd" required>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Aksi</label>
                        <select name="action" class="form-control">
                            <option value="show">Tampilkan Data</option>
                            <option value="export">Export Excel</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btnCloseModal">Batal</button>
                    <button class="btn btn-primary" type="submit">
                        <i class="fa fa-check"></i> Proses
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let table;
let totLastStock = 0, totStockIn = 0, totStockOut = 0, totReturn = 0, totRemain = 0;

/* ========================
   HELPER: format angka
======================== */
function fmtNum(n) {
    return parseInt(n || 0).toLocaleString('id-ID');
}

/* ========================
   INIT DATATABLE
======================== */
function loadTable(fd, td) {
    if (table) {
        table.destroy();
    }

    // reset totals
    totLastStock = totStockIn = totStockOut = totReturn = totRemain = 0;

    table = $('#stockReportTable').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: {
            url: "{{ route('stock_report.data') }}",
            data: function (d) {
                d.fd = fd;
                d.td = td;
            }
        },
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
        },
        columns: [
            { data: 'DT_RowIndex',  orderable: false, searchable: false, className: 'text-center' },
            { data: 'sku',          name: 'p.sku' },
            { data: 'nama_barang',  name: 'p.nama_barang' },
            { data: 'last_stock',   orderable: true,  searchable: false, className: 'text-right' },
            { data: 'stock_in',     orderable: true,  searchable: false, className: 'text-right' },
            { data: 'stock_out',    orderable: true,  searchable: false, className: 'text-right' },
            { data: 'return_qty',   orderable: true,  searchable: false, className: 'text-right' },
            { data: 'remain',       orderable: true,  searchable: false, className: 'text-right font-weight-bold' },
        ],
        columnDefs: [
            {
                targets: [3, 4, 5, 6, 7],
                render: function(data) {
                    return fmtNum(data);
                }
            }
        ],
        drawCallback: function(settings) {
            // accumulate totals from all pages via footerCallback
        },
        footerCallback: function(row, data, start, end, display) {
            var api = this.api();

            // Totals across ALL data (not just current page)
            var totalLastStock = api.column(3, {search: 'applied'}).data().reduce(function(a,b){ return a + parseInt(b||0); }, 0);
            var totalStockIn   = api.column(4, {search: 'applied'}).data().reduce(function(a,b){ return a + parseInt(b||0); }, 0);
            var totalStockOut  = api.column(5, {search: 'applied'}).data().reduce(function(a,b){ return a + parseInt(b||0); }, 0);
            var totalReturn    = api.column(6, {search: 'applied'}).data().reduce(function(a,b){ return a + parseInt(b||0); }, 0);
            var totalRemain    = api.column(7, {search: 'applied'}).data().reduce(function(a,b){ return a + parseInt(b||0); }, 0);

            $('#footLastStock').text(fmtNum(totalLastStock));
            $('#footStockIn').text(fmtNum(totalStockIn));
            $('#footStockOut').text(fmtNum(totalStockOut));
            $('#footReturn').text(fmtNum(totalReturn));
            $('#footRemain').text(fmtNum(totalRemain));
        }
    });
}

$(document).ready(function () {
    // Default load: bulan berjalan
    var today = new Date();
    var firstDay = today.getFullYear() + '-' + String(today.getMonth()+1).padStart(2,'0') + '-01';
    var todayStr = today.toISOString().split('T')[0];

    $('#inputFd').val(firstDay);
    $('#inputTd').val(todayStr);

    loadTable(firstDay, todayStr);
    updatePeriodeLabel(firstDay, todayStr);
});

/* ========================
   PERIODE LABEL
======================== */
function updatePeriodeLabel(fd, td) {
    if (!fd && !td) { $('#periodeInfo').hide(); return; }
    var label = (fd || '-') + ' s/d ' + (td || '-');
    $('#labelPeriode').text(label);
    $('#periodeInfo').show();
}

/* ========================
   MODAL CONTROL
======================== */
$('#btnFilter').on('click', function () {
    $('#filterModal').fadeIn(200).addClass('show');
    $('body').addClass('modal-open');
});

$('.btnCloseModal').on('click', function () {
    $('#filterModal').fadeOut(200).removeClass('show');
    $('body').removeClass('modal-open');
});

$(document).on('keydown', function(e) {
    if (e.key === 'Escape') {
        $('#filterModal').fadeOut(200).removeClass('show');
        $('body').removeClass('modal-open');
    }
});

/* ========================
   FILTER SUBMIT
======================== */
$('#filterForm').on('submit', function (e) {
    e.preventDefault();

    var fd     = $('input[name=fd]').val();
    var td     = $('input[name=td]').val();
    var action = $('select[name=action]').val();

    if (!fd || !td) {
        Swal.fire('Perhatian', 'Tanggal mulai dan akhir wajib diisi.', 'warning');
        return;
    }

    if (fd > td) {
        Swal.fire('Perhatian', 'Tanggal mulai tidak boleh melebihi tanggal akhir.', 'warning');
        return;
    }

    // Tutup modal
    $('#filterModal').fadeOut(200).removeClass('show');
    $('body').removeClass('modal-open');

    if (action === 'show') {
        updatePeriodeLabel(fd, td);
        loadTable(fd, td);
        return;
    }

    // Export Excel
    var exportUrl = "{{ route('stock_report.export') }}?fd=" + fd + "&td=" + td;

    Swal.fire({
        title: 'Export Laporan Stock?',
        text: 'File Excel akan di-generate untuk periode ' + fd + ' s/d ' + td,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Export',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (result.isConfirmed) {
            window.open(exportUrl, '_blank');
        }
    });
});
</script>
@endsection
