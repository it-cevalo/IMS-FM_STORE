@extends('layouts.admin')

<!-- Start Style CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
<!-- End Style CSS -->

@section('content')
<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><a href="{{route('invoice.index')}}">Invoice</a></h6>
    </div>
    <div class="card-header py-3">
        @if(Auth::user()->position!='DIRECTOR')
        <a href="{{route('invoice.create')}}" class="btn btn-primary btn-flat btn-sm"><i class="fa fa-plus"></i>
            Tambah</a>
        <!-- <a href="{{route('invoice.bin')}}" class="btn btn-primary btn-flat btn-sm"><i class="fa fa-archive"></i> See
            Archive</a> -->
        @else
        @endif
    </div>
    <div class="card-body">
        @if(\Session::has('error'))
        <div class="alert alert-danger">
            <span>{{ \Session::get('error') }}</span>
            <button type="button" class="close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @elseif(\Session::has('success'))
        <div class="alert alert-success">
            <span>{{ \Session::get('success') }}</span>
            <button type="button" class="close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
        <div class="table-responsive"> 
            <table class="table table-bordered" id="invoiceTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Pelanggan</th>
                        <th>Nama Pelanggan</th>
                        <th>Invoice Date</th>
                        <th>Invoice No</th>
                        <th>Grand Total</th>
                        <th>Bank Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        loadInvoiceTable();
    });

    function loadInvoiceTable() {
        $('#invoiceTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('invoice.getdata') }}',
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'code_cust', name: 'code_cust' },
                { data: 'customer.nama_cust', name: 'customer.nama_cust' },
                { data: 'tgl_inv', name: 'tgl_inv' },
                { data: 'no_inv', name: 'no_inv' },
                { data: 'grand_total', name: 'grand_total' },
                { data: 'bank.nama_bank', name: 'bank.nama_bank' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ]
        });
    }

    function printInvoicePDF(id) {
        Swal.fire({
            title: 'Sedang menyiapkan file...',
            text: 'Mohon tunggu sebentar.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();

                // Tunggu sebentar agar user sempat melihat loading, lalu buka PDF
                setTimeout(function () {
                    window.open('/Inv_Export2PDF/' + id, '_blank');
                    Swal.close(); // Tutup loading setelah tab terbuka
                }, 1000); // 1 detik loading (bisa diatur sesuai kebutuhan)
            }
        });
    }
</script>
@endsection