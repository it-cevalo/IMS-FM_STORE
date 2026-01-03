@extends('layouts.admin')

@section('content')
<div class="card shadow">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">
            Scan Barang Keluar
        </h6>
    </div>

    <div class="card-body">
        <table class="table table-bordered table-hover" id="outboundTable" width="100%">
            <thead class="bg-light">
                <tr>
                    <th>Tanggal Masuk</th>
                    <th class="text-center">Jumlah DO</th>
                    <th class="text-center">Total Barang</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<script>
let outboundTable;

/**
 * Init Datatable Product Outbound (Harian)
 */
function loadOutboundTable() {

    if ($.fn.DataTable.isDataTable('#outboundTable')) {
        $('#outboundTable').DataTable().destroy();
    }

    outboundTable = $('#outboundTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "{{ route('product_outbound.datatable') }}",
            type: "GET"
        },
        columns: [
            {
                data: 'tgl_outbound',
                render: {
                    display: function (data) {
                        if (!data) return '-';

                        const d = new Date(data);
                        const day   = String(d.getDate()).padStart(2, '0');
                        const month = String(d.getMonth() + 1).padStart(2, '0');
                        const year  = d.getFullYear();

                        return `${day}-${month}-${year}`;
                    },
                    sort: function (data) {
                        // PAKAI FORMAT ASLI BUAT SORT
                        return data;
                    }
                }
            },
            { 
                data: 'jumlah_do', 
                className: 'text-center font-weight-bold' 
            },
            { 
                data: 'total_barang', 
                className: 'text-center' 
            },
            {
                data: 'tgl_outbound',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (tgl) {
                    return `
                    <a href="{{ url('product_outbound/detail') }}/${tgl}" class="btn btn-sm btn-info">
                        Detail
                    </a>
                    `;
                }
            }
        ],
        order: [[0, 'desc']],
        language: {
            processing: "Loading data..."
        }
    });
}

/**
 * Auto load ketika halaman muncul
 */
$(document).ready(function () {
    loadOutboundTable();
});
</script>
@endsection