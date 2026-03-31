@extends('layouts.admin')

@section('content')
<div class="card shadow">
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Detail Scan Staging (Grouping Per Hari)</h6>
        <div>
            <a href="{{ route('tdo_scan_staging.index') }}" class="btn btn-dark btn-sm">
                Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(\Session::has('error'))
            <div class="alert alert-danger">
                <span>{{ \Session::get('error') }}</span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @elseif(\Session::has('success'))
            <div class="alert alert-success">
                <span>{{ \Session::get('success') }}</span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <form action="{{ route('tdo_scan_staging.generate_do_batch') }}" method="POST" id="formBatchDo">
            @csrf
            <div id="accordionStaging">
                @forelse($data as $tgl => $items)
                @php
                    $groupedBySku = $items->groupBy('sku');
                @endphp
                <div class="card mb-3 border-left-primary shadow-sm">
                    <div class="card-header bg-white d-flex align-items-center">
                        <div class="mr-3">
                            <input type="checkbox" name="dates[]" value="{{ $tgl }}" style="transform: scale(1.5);">
                        </div>
                        <a data-toggle="collapse" href="#collapse{{ $loop->iteration }}" class="flex-grow-1 text-dark text-decoration-none">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="mb-0 font-weight-bold">
                                        Tanggal: {{ date('d-m-Y', strtotime($tgl)) }}
                                    </h6>
                                    <span class="small text-muted">{{ $items->count() }} Total Scan</span>
                                </div>
                                <div>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div id="collapse{{ $loop->iteration }}" class="collapse">
                        <div class="card-body">
                            {{-- Tampilan Mirip Detail DO --}}
                            <div class="table-responsive">
                                <label class="font-weight-bold">Ringkasan Barang:</label>
                                <table class="table table-bordered table-sm">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="text-center" width="20%">SKU</th>
                                            <th class="text-center">Nama Barang</th>
                                            <th class="text-center" width="15%">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($groupedBySku as $sku => $skuItems)
                                        <tr>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" value="{{ $sku }}" disabled>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" value="{{ $skuItems->first()->nama_barang }}" disabled>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm text-right" value="{{ $skuItems->count() }}" disabled>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                <button type="button" class="btn btn-link btn-sm p-0" data-toggle="collapse" data-target="#detail{{ $loop->iteration }}">
                                    Lihat Detail Per Scan QR <i class="fas fa-qrcode"></i>
                                </button>
                                <div id="detail{{ $loop->iteration }}" class="collapse mt-2">
                                    <table class="table table-hover table-sm small">
                                        <thead>
                                            <tr>
                                                <th>QR Code</th>
                                                <th>User</th>
                                                <th>Waktu</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($items as $item)
                                            <tr>
                                                <td>{{ $item->qr_code }}</td>
                                                <td>{{ $item->creator->username ?? 'User ID: '.$item->created_by }}</td>
                                                <td>{{ $item->created_at->format('H:i:s') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="alert alert-info">
                    Tidak ada data scan staging yang berstatus OPEN.
                </div>
                @endforelse
            </div>

            @if($data->count() > 0)
            <div class="sticky-bottom bg-white p-3 border-top shadow-lg" style="position: sticky; bottom: 0; z-index: 1000;">
                <button type="submit" class="btn btn-primary btn-lg btn-block shadow" onclick="return confirm('Proses tanggal yang dipilih menjadi Delivery Order?')">
                    <i class="fas fa-file-invoice"></i> Generate DO untuk Tanggal Terpilih
                </button>
            </div>
            @endif
        </form>
    </div>
</div>

<style>
    .border-left-primary { border-left: .25rem solid #4e73df!important; }
    .sticky-bottom { margin-left: -1.25rem; margin-right: -1.25rem; margin-bottom: -1.25rem; }
</style>
@endsection
