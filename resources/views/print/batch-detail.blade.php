@extends('layouts.admin')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="font-weight-bold text-dark mb-0">
                <i class="fas fa-check-circle text-success mr-2"></i>Detail Batch Cetak
            </h5>
            <small class="text-muted">
                PO: <strong>{{ $po->no_po }}</strong> &bull; {{ $batch->batch_name }}
            </small>
        </div>
        <div>
            <a href="{{ route('purchase_order.print_status', $po->id) }}" class="btn btn-secondary btn-sm mr-1">
                <i class="fas fa-arrow-left mr-1"></i>Semua Batch
            </a>
            <a href="{{ route('purchase_order.show', $po->id) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-file-alt mr-1"></i>Kembali ke PO
            </a>
        </div>
    </div>

    <div class="row">

        {{-- Kiri: Info Batch + Item --}}
        <div class="col-lg-4 mb-3">

            {{-- Info Card --}}
            <div class="card shadow mb-3">
                <div class="card-header py-2">
                    <span class="font-weight-bold text-dark">Informasi Batch</span>
                </div>
                <div class="card-body py-3">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" style="width:40%">Nama Batch</td>
                            <td><strong>{{ $batch->batch_name }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">No. PO</td>
                            <td><strong>{{ $po->no_po }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Total Label</td>
                            <td><span class="badge badge-info">{{ $batch->total_labels }} label</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                @if($batch->status === 'Ready')
                                    <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Selesai</span>
                                @else
                                    <span class="badge badge-secondary">{{ $batch->status }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Waktu Generate</td>
                            <td class="small">
                                {{ \Carbon\Carbon::parse($batch->created_at)->format('d M Y, H:i') }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Items Card --}}
            @if(!empty($items))
            <div class="card shadow">
                <div class="card-header py-2">
                    <span class="font-weight-bold text-dark">Rincian SKU</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>SKU</th>
                                <th class="text-center">Sequence</th>
                                <th class="text-center">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                            <tr>
                                <td class="small font-weight-bold">{{ $item['sku'] }}</td>
                                <td class="text-center small text-muted">
                                    {{ $item['dari'] }} – {{ $item['sampai'] }}
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light border">{{ $item['jumlah'] }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="card shadow">
                <div class="card-body text-muted small">
                    {{ $batch->content_summary }}
                </div>
            </div>
            @endif

        </div>

        {{-- Kanan: PDF Viewer --}}
        <div class="col-lg-8 mb-3">
            <div class="card shadow h-100">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold text-dark">
                        <i class="fas fa-file-pdf text-danger mr-2"></i>Preview PDF
                    </span>
                    @if($batch->status === 'Ready' && $batch->file_path)
                    <button class="btn btn-success btn-sm" id="btnPrint">
                        <i class="fas fa-print mr-1"></i>Cetak
                    </button>
                    @endif
                </div>
                <div class="card-body p-0 position-relative">
                    @if($batch->status === 'Ready' && $batch->file_path)
                        <div id="pdfLoading"
                             style="position:absolute; inset:0; background:rgba(255,255,255,.9);
                                    z-index:10; display:flex; align-items:center; justify-content:center;">
                            <div class="text-center text-muted">
                                <i class="fas fa-spinner fa-spin fa-2x mb-2 d-block"></i>Memuat PDF...
                            </div>
                        </div>
                        <iframe id="pdfFrame"
                                src="{{ asset('storage/temp_prints/'.$batch->file_path) }}#toolbar=0&navpanes=0&scrollbar=0"
                                style="width:100%; height:78vh; border:none; display:block;">
                        </iframe>
                    @else
                        <div class="d-flex align-items-center justify-content-center text-muted" style="height:78vh">
                            <div class="text-center">
                                <i class="fas fa-hourglass-half fa-3x mb-3 d-block"></i>
                                PDF belum tersedia (status: {{ $batch->status }})
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

<script>
(function () {
    const frame   = document.getElementById('pdfFrame');
    const loading = document.getElementById('pdfLoading');
    const btnPrint = document.getElementById('btnPrint');

    if (frame && loading) {
        frame.addEventListener('load', function () {
            loading.style.display = 'none';
        });
    }

    if (btnPrint && frame) {
        btnPrint.addEventListener('click', function () {
            frame.contentWindow.focus();
            frame.contentWindow.print();
        });
    }
}());
</script>
@endsection
