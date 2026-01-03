@extends('layouts.admin')

@section('content')
<div class="card shadow">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">
            Scan Barang Masuk
        </h6>
    </div>

    <div class="card-body">
        <table class="table table-bordered table-hover" id="inboundTable" width="100%">
            <thead class="bg-light">
                <tr>
                    <th>Tanggal Masuk</th>
                    <th class="text-center">Jumlah PO</th>
                    <th class="text-center">Jumlah Retur (Barang) </th>
                    {{-- <th class="text-center">Total Barang</th> --}}
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<script>
let inboundTable;

/**
 * Init Datatable Product Inbound (Harian)
 */
function loadInboundTable() {

    if ($.fn.DataTable.isDataTable('#inboundTable')) {
        $('#inboundTable').DataTable().destroy();
    }

    inboundTable = $('#inboundTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "{{ route('product_inbound.datatable') }}",
            type: "GET"
        },
        columns: [
            {
                data: 'tgl_inbound',
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
            { data: 'jumlah_po', className: 'text-center font-weight-bold' },
            { data: 'jumlah_retur', className: 'text-center font-weight-bold' },
            // { data: 'total_barang', className: 'text-center' },
            {
                data: 'tgl_inbound',
                orderable: false,
                className: 'text-center',
                render: function (tgl) {
                    return `
                    <a href="{{ url('product_inbound/detail') }}/${tgl}" class="btn btn-sm btn-info">
                        Detail
                    </a>`;
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
    loadInboundTable();
});
</script>
@endsection