@extends('layouts.admin')

@section('content')
<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><a href="{{route('product_transfer.index')}}">Product Transfer</a>
        </h6>
    </div>
    <div class="card-header py-3">
        @if(Auth::user()->position=='SUPERADMIN')
        <button type="button" class="btn btn-primary btn-flat btn-sm" data-toggle="modal" data-target="#exampleModal">
            <i class="fa fa-filter"></i> Filter
        </button>
        <a href="{{route('product_transfer.create')}}" class="btn btn-primary btn-flat btn-sm"><i
                class="fa fa-plus"></i> Tambah</a>
        <!-- <a href="#" class="btn btn-primary btn-flat btn-sm" data-toggle="modal" data-target="#exampleModal"><i class="fa fa-upload"></i> Upload Excel</a>
                <a download="Template_po.xlsx" href="{{ Storage::url('tpl/template_po.xlsx') }}" class="btn btn-primary btn-flat btn-sm" title="Template_po.xlsx"><i class="fa fa-download"></i> Download Template Excel</a> -->
        @elseif(Auth::user()->position=='MARKETING')
        <a href="{{route('product_transfer.create')}}" class="btn btn-primary btn-flat btn-sm"><i
                class="fa fa-plus"></i> Tambah</a>
        <!-- <a href="#" class="btn btn-primary btn-flat btn-sm" data-toggle="modal" data-target="#exampleModal"><i class="fa fa-upload"></i> Upload Excel</a>
                <a download="Template_po.xlsx" href="{{ Storage::url('tpl/template_po.xlsx') }}" class="btn btn-primary btn-flat btn-sm" title="Template_po.xlsx"><i class="fa fa-download"></i> Download Template Excel</a> -->
        @else
        @endif
    </div>
    <div class="card-body">
        @if(\Session::has('fail'))
        <div class="alert alert-danger">
            <span>{{\Session::get('fail')}}</span>
        </div>
        @endif
        <div class="table-responsive">
            <table class="table table-bordered" id="transferTable" width="100%" cellspacing="0">
                <thead>
                <tr>
                    <th class="text-center">Kode Barang</th>
                    <th class="text-center">Nama Barang</th>
                    <th class="text-center">From WH Code</th>
                    <th class="text-center">From WH Name</th>
                    <th class="text-center">To WH Code</th>
                    <th class="text-center">To WH Name</th>
                    <th class="text-center">Qty Transfer</th>
                    <th class="text-center">Date Transfer</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Start Modal Filter -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><i class="fa fa-filter"></i> Saring</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="GET" action="{{route('product_transfer.filter')}}">
                    <div class="mb-3">
                        <label for="exampleFormControlInput1">Gudang From</label>
                        <select class="form-control select2" id="search-type" name="id_warehouse_from"
                            value="{{old('id_warehouse_from')}}" required>
                            <option value="#">....</option>
                            @foreach($warehouses as $p)
                            <option value="{{$p->id}}">{{$p->nama_wh}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="exampleFormControlInput1">Gudang To</label>
                        <select class="form-control select2" id="search-type" name="id_warehouse_to"
                            value="{{old('id_warehouse_to')}}" required>
                            <option value="#">....</option>
                            @foreach($warehouses as $p)
                            <option value="{{$p->id}}">{{$p->nama_wh}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="exampleFormControlInput1">From Date</label>
                        <div class="input-group">
                            <input class="form-control" id="exampleFormControlInput1" name="fd" type="date" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="exampleFormControlInput1">To Date</label>
                        <div class="input-group">
                            <input class="form-control" id="exampleFormControlInput1" name="td" type="date" required>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Execute</button>
            </div>
            </form>
        </div>
    </div>
</div>
<!-- End Modal Filter -->
<script>
    // Fungsi utama untuk load datatable
    function loadTransferData() {
        $('#transferTable').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: '{{ route('product_transfer.getData') }}',
            columns: [
                { data: 'SKU', name: 'SKU' },
                { data: 'nama_barang', name: 'nama_barang' },
                { data: 'code_wh', name: 'code_wh' },
                { data: 'nama_wh', name: 'nama_wh' },
                { data: 'to_code_wh', name: 'to_code_wh' },
                { data: 'to_nama_wh', name: 'to_nama_wh' },
                { data: 'qty_prd', name: 'qty_prd' },
                { data: 'tgl_trf', name: 'tgl_trf' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            drawCallback: function () {
                bindDeleteButtons(); // bind ulang saat datatable reload
            }
        });
    }
    // Fungsi delete dengan swal2
    function bindDeleteButtons() {
        $('#dataTable').on('click', '.btn-delete', function (e) {
            e.preventDefault();
            var id = $(this).data('id');

            Swal.fire({
                title: 'Hapus Data?',
                text: 'Data akan dipindahkan ke arsip dan tidak dapat diakses pengguna biasa.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/product_transfer/delete/' + id,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            if (response.success) {
                                Swal.fire('Berhasil!', response.message, 'success');
                                $('#dataTable').DataTable().ajax.reload();
                            } else {
                                Swal.fire('Gagal!', response.message, 'error');
                            }
                        },
                        error: function (xhr) {
                            let msg = 'Terjadi kesalahan tidak terduga.';
                            if (xhr.status === 404) {
                                msg = 'Data tidak ditemukan. Mungkin sudah dihapus sebelumnya.';
                            } else if (xhr.status === 500) {
                                msg = 'Server mengalami masalah. Silakan coba beberapa saat lagi.';
                            }
                            Swal.fire('Error!', msg, 'error');
                        }
                    });
                }
            });
        });
    }
    $(document).ready(function () {
        loadTransferData();
    });
    </script>    
@endsection