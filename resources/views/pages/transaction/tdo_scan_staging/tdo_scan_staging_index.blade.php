@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4" id="processingStatus" style="display:none;">
    <div class="card-body bg-light border-left-info">
        <div class="d-flex align-items-center">
            <div class="spinner-border text-info mr-3" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <div>
                <h6 class="m-0 font-weight-bold text-info">Proses Generate DO sedang berjalan...</h6>
                <small class="text-muted">Data sedang diproses di background. Sisa data OPEN: <span id="openCount">-</span>, Sedang diproses: <span id="processingCount">-</span></small>
            </div>
        </div>
    </div>
</div>

<div class="card shadow">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
        <h6 class="m-0 font-weight-bold text-primary">
            Hasil Scan Staging (Belum Tersimpan)
        </h6>
        <div class="d-flex gap-2">
            <button id="btnGenerateAll" class="btn btn-success btn-sm mr-2">
                <i class="fas fa-play-circle"></i> Generate All DO (Queue)
            </button>
            <a href="{{ route('tdo_scan_staging.detail_all') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-list"></i> Lihat Detail (Accordion)
            </a>
        </div>
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
let statusInterval;

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
                        <button type="submit" class="btn btn-sm btn-success btn-generate-single">
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

function checkProcessingStatus() {
    $.get("{{ route('tdo_scan_staging.status') }}", function(res) {
        $('#openCount').text(res.open);
        $('#processingCount').text(res.processing);

        if (res.processing > 0) {
            $('#processingStatus').fadeIn();
            $('#btnGenerateAll').prop('disabled', true);
            $('.btn-generate-single').prop('disabled', true);
            
            // Start interval if not already started
            if (!statusInterval) {
                statusInterval = setInterval(checkProcessingStatus, 5000);
            }
        } else {
            if (statusInterval) {
                clearInterval(statusInterval);
                statusInterval = null;
                $('#processingStatus').fadeOut();
                $('#btnGenerateAll').prop('disabled', false);
                stagingTable.ajax.reload();
                
                // Show success message if it was previously processing
                Swal.fire('Selesai', 'Proses generate DO di background telah selesai.', 'success');
            }
        }
    });
}

$(document).ready(function () {
    loadStagingTable();
    checkProcessingStatus();

    $('#btnGenerateAll').click(function() {
        Swal.fire({
            title: 'Generate All DO?',
            text: "Semua data 'OPEN' akan diproses di background.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Jalankan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("{{ route('tdo_scan_staging.dispatch') }}", {
                    _token: "{{ csrf_token() }}"
                }, function(res) {
                    if (res.success) {
                        Swal.fire('Berhasil', res.message, 'success');
                        checkProcessingStatus();
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                }).fail(function() {
                    Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                });
            }
        });
    });
});
</script>
@endsection
