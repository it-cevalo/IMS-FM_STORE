@extends('layouts.admin')

@section('content')
<div class="card shadow">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            Hasil Scan Staging (Belum Tersimpan)
        </h6>
        <a href="{{ route('tdo_scan_staging.detail_all') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-list"></i> Lihat Detail (Accordion)
        </a>
    </div>

    <div class="card-body">
        <table class="table table-bordered table-hover" id="stagingTable" width="100%">
            <thead class="bg-light">
                <tr>
                    <th>Tanggal Scan</th>
                    <th class="text-center">Jumlah Sesi</th>
                    <th class="text-center">Jumlah User</th>
                    <th class="text-center">Total Scan</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<script>
let stagingTable;

function loadStagingTable() {
    if ($.fn.DataTable.isDataTable('#stagingTable')) {
        $('#stagingTable').DataTable().destroy();
    }

    stagingTable = $('#stagingTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "{{ route('tdo_scan_staging.datatable') }}",
            type: "GET"
        },
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
        },
        columns: [
            {
                data: 'tgl_scan',
                render: {
                    display: function (data) {
                        if (!data) return '-';
                        const d = new Date(data);
                        return `${String(d.getDate()).padStart(2, '0')}-${String(d.getMonth() + 1).padStart(2, '0')}-${d.getFullYear()}`;
                    },
                    sort: function (data) { return data; }
                }
            },
            { data: 'jumlah_sesi', className: 'text-center' },
            { data: 'jumlah_user', className: 'text-center' },
            { data: 'total_scan', className: 'text-center font-weight-bold' },
            {
                data: 'tgl_scan',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (tgl) {
                    return `
                    <a href="{{ url('tdo_scan_staging/detail') }}/${tgl}" class="btn btn-sm btn-info">
                        Detail
                    </a>
                    <form action="{{ route('tdo_scan_staging.generate_do') }}" method="POST" style="display:inline;" onsubmit="return confirm('Generate DO for ${tgl}?')">
                        @csrf
                        <input type="hidden" name="tgl" value="${tgl}">
                        <button type="submit" class="btn btn-sm btn-success">
                            Generate DO
                        </button>
                    </form>
                    `;
                }
            }
        ],
        order: [[0, 'desc']]
    });
}

$(document).ready(function () {
    loadStagingTable();
});
</script>
@endsection
