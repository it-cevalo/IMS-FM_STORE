@extends('layouts.admin')

@section('content')
<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Master Data Bank</h6>
    </div>
    <div class="card-header py-3">
        <a href="{{ route('bank.create') }}" class="btn btn-primary btn-flat btn-sm">
            <i class="fa fa-plus"></i> Tambah
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>No Rekening</th>
                        <th>Nama Pemilik</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody> <!-- Biarkan kosong, diisi oleh AJAX -->
            </table>
        </div>
    </div>
</div>
<!-- DataTables JS (pastikan sudah include jQuery dan DataTables) -->
<script>
    function loadBankData() {
        bankTable = $('#dataTable').DataTable({
            processing: true,
            serverSide: true,
            destroy: true, // penting agar bisa re-init jika perlu
            ajax: '{{ route('bank.data') }}',
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'code_bank', name: 'code_bank' },
                { data: 'nama_bank', name: 'nama_bank' },
                { data: 'norek_bank', name: 'norek_bank' },
                { data: 'atasnama_bank', name: 'atasnama_bank' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    }

    $(document).ready(function () {
        loadBankData(); // panggil function saat pertama load
    });
</script>
@endsection