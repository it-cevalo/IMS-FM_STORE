@extends('layouts.admin')

@section('content')

{{-- ===== PANDUAN LANGKAH ===== --}}
<div class="row mb-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="background:#f0f4ff;">
            <div class="card-body py-3">
                <p class="mb-2 font-weight-bold text-primary"><i class="fas fa-info-circle mr-1"></i> Cara mengisi form ini:</p>
                <div class="d-flex flex-wrap" style="gap:.6rem;">
                    <span class="badge badge-primary px-3 py-2" style="font-size:.8rem;">1. Pilih Gudang</span>
                    <i class="fas fa-arrow-right align-self-center text-muted"></i>
                    <span class="badge badge-primary px-3 py-2" style="font-size:.8rem;">2. Pilih Barang</span>
                    <i class="fas fa-arrow-right align-self-center text-muted"></i>
                    <span class="badge badge-primary px-3 py-2" style="font-size:.8rem;">3. Isi jumlah stok fisik</span>
                    <i class="fas fa-arrow-right align-self-center text-muted"></i>
                    <span class="badge badge-success px-3 py-2" style="font-size:.8rem;">4. Simpan</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-plus-circle mr-1"></i> Catat Stok Opname Baru
                </h6>
            </div>
            <div class="card-body">

                @if(\Session::has('error'))
                <div class="alert alert-danger alert-dismissible">
                    <span>{{ \Session::get('error') }}</span>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
                @endif

                <form action="{{ route('stock_opname.store') }}" method="POST">
                    @csrf

                    {{-- STEP 1 & 2: Lokasi & Barang --}}
                    <div class="card border mb-4">
                        <div class="card-header bg-light py-2">
                            <small class="font-weight-bold text-uppercase text-muted">
                                <i class="fas fa-map-marker-alt mr-1"></i> Langkah 1 — Pilih Lokasi & Barang
                            </small>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="font-weight-bold">Gudang <span class="text-danger">*</span></label>
                                <select class="form-control" name="id_warehouse" required>
                                    <option value="">-- Pilih gudang tempat barang disimpan --</option>
                                    @foreach($warehouse as $p)
                                    <option value="{{ $p->id }}" {{ old('id_warehouse') == $p->id ? 'selected' : '' }}>
                                        {{ $p->code_wh }} — {{ $p->nama_wh }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('id_warehouse')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label class="font-weight-bold">Barang <span class="text-danger">*</span></label>
                                <select class="form-control" name="id_product" required>
                                    <option value="">-- Pilih barang yang dihitung --</option>
                                    @foreach($product as $p)
                                    <option value="{{ $p->id }}" {{ old('id_product') == $p->id ? 'selected' : '' }}>
                                        {{ $p->sku }} — {{ $p->nama_barang }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('id_product')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- STEP 3: Jumlah Stok --}}
                    <div class="card border mb-4">
                        <div class="card-header bg-light py-2">
                            <small class="font-weight-bold text-uppercase text-muted">
                                <i class="fas fa-calculator mr-1"></i> Langkah 2 — Isi Jumlah Stok Fisik
                            </small>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-4 mb-3">
                                    <label class="font-weight-bold">Qty Masuk <span class="text-danger">*</span></label>
                                    <input class="form-control" name="qty_in" type="number" min="0"
                                        value="{{ old('qty_in', 0) }}" required>
                                    <small class="text-muted">Jumlah barang yang masuk saat ini</small>
                                    @error('qty_in')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-sm-4 mb-3">
                                    <label class="font-weight-bold">Qty Keluar <span class="text-danger">*</span></label>
                                    <input class="form-control" name="qty_out" type="number" min="0"
                                        value="{{ old('qty_out', 0) }}" required>
                                    <small class="text-muted">Jumlah barang yang keluar saat ini</small>
                                    @error('qty_out')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-sm-4 mb-3">
                                    <label class="font-weight-bold text-primary">
                                        Qty Fisik Akhir <span class="text-danger">*</span>
                                    </label>
                                    <input class="form-control border-primary font-weight-bold" name="qty_last"
                                        type="number" min="0" value="{{ old('qty_last') }}" required
                                        placeholder="Hasil hitung fisik">
                                    <small class="text-muted">
                                        <i class="fas fa-star text-warning" style="font-size:.7rem;"></i>
                                        Jumlah total barang yang ada secara fisik sekarang
                                    </small>
                                    @error('qty_last')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-0">
                                <label class="font-weight-bold">Tanggal Opname <span class="text-danger">*</span></label>
                                <input class="form-control" name="tgl_opname" type="date"
                                    value="{{ old('tgl_opname', date('Y-m-d')) }}" required>
                                @error('tgl_opname')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex" style="gap:.5rem;">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save mr-1"></i> Simpan Catatan
                        </button>
                        <a href="{{ route('stock_opname.index') }}" class="btn btn-outline-secondary">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
