@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Status Antrian Cetak QR - PO #{{ $id }}</h6>
            <a href="{{ route('purchase_order.show', $id) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali ke PO
            </a>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Sistem sedang memproses label dalam beberapa batch (maks 100 label per file) untuk menjaga performa server. Silakan refresh halaman ini secara berkala.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>Nama Batch</th>
                            <th>Detail SKU & Sequence</th>
                            <th>Total Label</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batches as $batch)
                        <tr>
                            <td class="font-weight-bold text-dark">{{ $batch->batch_name }}</td>
                            <td>
                                <span class="text-muted small">{{ $batch->content_summary }}</span>
                            </td>
                            <td>{{ $batch->total_labels }}</td>
                            <td>
                                @if($batch->status == 'Pending')
                                    <span class="badge badge-secondary"><i class="fas fa-clock"></i> Menunggu...</span>
                                @elseif($batch->status == 'Processing')
                                    <span class="badge badge-warning text-dark"><i class="fas fa-spinner fa-spin"></i> Sedang Diproses...</span>
                                @elseif($batch->status == 'Ready')
                                    <span class="badge badge-success"><i class="fas fa-check-circle"></i> Selesai</span>
                                @else
                                    <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Gagal</span>
                                @endif
                            </td>
                            <td>
                                @if($batch->status == 'Ready')
                                    <a href="{{ asset('storage/temp_prints/' . $batch->file_path) }}" 
                                       target="_blank" 
                                       class="btn btn-primary btn-sm btn-block">
                                        <i class="fas fa-print"></i> Buka Print Preview
                                    </a>
                                @elseif($batch->status == 'Failed')
                                    <button class="btn btn-danger btn-sm btn-block" disabled title="{{ $batch->error_message }}">
                                        Error
                                    </button>
                                @else
                                    <button class="btn btn-outline-secondary btn-sm btn-block" disabled>
                                        Memproses...
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">Belum ada antrian cetak untuk PO ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto refresh setiap 10 detik jika ada yang masih pending/processing
    @if($batches->whereIn('status', ['Pending', 'Processing'])->count() > 0)
        setTimeout(function(){
            window.location.reload();
        }, 10000);
    @endif
</script>
@endsection
