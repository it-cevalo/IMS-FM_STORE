@extends('layouts.admin')

@section('content')
<div class="card shadow">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="m-0">Detail Scan Staging - {{ date('d-m-Y', strtotime($tgl)) }}</h5>
            <a href="{{ route('tdo_scan_staging.index') }}" class="btn btn-dark">
                Kembali
            </a>
        </div>

        <div id="accordionStaging">
            @forelse($rows as $sessionId => $items)
            <div class="card mb-2">
                <div class="card-header d-flex align-items-center bg-light">
                    <a data-toggle="collapse" href="#session{{ $loop->iteration }}" class="flex-grow-1 text-dark text-decoration-none">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <b>Session ID: {{ $sessionId }}</b>
                                <div class="small text-muted">
                                    Scanned by: {{ $items->first()->creator->username ?? 'Unknown' }}
                                </div>
                            </div>
                            <div>
                                <span class="badge badge-info">{{ $items->count() }} Item</span>
                            </div>
                        </div>
                    </a>
                </div>

                <div id="session{{ $loop->iteration }}" class="collapse">
                    <div class="card-body p-2">
                        @foreach($items as $item)
                        <div class="d-flex align-items-center border-bottom py-2">
                            <div class="ml-2">
                                <b>{{ $item->sku }}</b> - {{ $item->nama_barang }}<br>
                                <span class="small text-muted">{{ $item->qr_code }}</span><br>
                                <span class="badge badge-light">Status: {{ $item->status }}</span>
                                <span class="small ml-2">{{ date('H:i:s', strtotime($item->created_at)) }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @empty
            <div class="alert alert-warning">
                Tidak ada data scan pada tanggal ini
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
