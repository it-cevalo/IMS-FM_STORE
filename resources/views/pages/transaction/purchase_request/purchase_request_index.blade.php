@extends('layouts.admin')

@section('content')
<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><a href="{{route('purchase_request.index')}}">Purchase
                Request</a>
        </h6>
    </div>
    <div class="card-header py-3">
        <!-- <button type="button" class="btn btn-primary btn-flat btn-sm" data-toggle="modal" data-target="#exampleModal">
            <i class="fa fa-filter"></i> Filter
        </button> -->
        <a href="{{route('purchase_request.create')}}" class="btn btn-primary btn-flat btn-sm"><i
                class="fa fa-plus"></i> Tambah</a>
        <!-- <a href="#" class="btn btn-primary btn-flat btn-sm" data-toggle="modal" data-target="#exampleModal"><i class="fa fa-upload"></i> Upload Excel</a>
                <a download="Template_po.xlsx" href="{{ Storage::url('tpl/template_po.xlsx') }}" class="btn btn-primary btn-flat btn-sm" title="Template_po.xlsx"><i class="fa fa-download"></i> Download Template Excel</a> -->
    </div>
    <div class="card-body">
        @if(\Session::has('fail'))
        <div class="alert alert-danger">
            <span>{{\Session::get('fail')}}</span>
        </div>
        @endif
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-center">Kode</th>
                        <th class="text-center">Kode Gudang</th>
                        <th class="text-center">Nama Gudang</th>
                        <th class="text-center">Qty Request</th>
                        <th class="text-center">Desc Request</th>
                        <th class="text-center">Requested By</th>
                        <th class="text-center">Request Date</th>
                        <th class="text-center">Approved By</th>
                        <th class="text-center">Approve Date</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>                
            </table>
        </div>
    </div>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript">
$(document).ready(function () {
    loadPurchaseRequest();

    function loadPurchaseRequest() {
        $('#dataTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('purchase_request.getData') }}',
            columns: [
                { data: 'code_pr', name: 'code_pr' },
                { data: 'code_wh', name: 'code_wh' },
                { data: 'nama_wh', name: 'nama_wh' },
                { data: 'total_qty_req', name: 'total_qty_req' },
                { data: 'desc_req', name: 'desc_req' },
                { data: 'request_name', name: 'request_name' },
                { data: 'request_date', name: 'request_date' },
                { data: 'approve_name', name: 'approve_name' },
                { data: 'approve_date', name: 'approve_date' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            drawCallback: function () {
                bindApproveButtons();
            }
        });
    }

    function bindApproveButtons() {
        $('#dataTable').on('click', '.btn-approve', function () {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Setujui Permintaan?',
                text: "Pastikan semua data sudah benar sebelum menyetujui.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Setujui',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/purchase_request/' + id + '/approve',
                        type: 'GET',
                        success: function (res) {
                            Swal.fire('Berhasil!', 'Permintaan berhasil disetujui.', 'success');
                            $('#dataTable').DataTable().ajax.reload();
                        },
                        error: function (xhr) {
                            let msg = 'Terjadi kesalahan saat menyetujui.';
                            if (xhr.status === 404) msg = 'Data tidak ditemukan.';
                            else if (xhr.status === 500) msg = 'Kesalahan server. Coba beberapa saat lagi.';
                            Swal.fire('Error!', msg, 'error');
                        }
                    });
                }
            });
        });
    }
});

function approveOrder(orderId) {
    $.ajax({
        url: "{{ route('purchase_request.approve', ':id') }}".replace(':id', orderId),
        type: 'GET',
        success: function(response) {
            $('#approveBtn' + orderId).removeClass('btn-primary').addClass('btn-success').prop('disabled',
                true);
            Swal.fire({
                icon: 'success',
                title: 'Pesanan Disetujui!',
            });
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Terjadi kesalahan saat menyetujui pesanan!',
            });
        }
    });
}

$('.show-alert-delete-box').click(function(event) {
    var form = $(this).closest("form");
    var name = $(this).data("name");
    event.preventDefault();
    swal({
        title: "Are you sure you want to delete this record?",
        text: "If you delete this, it will be go to archive.",
        icon: "warning",
        type: "warning",
        buttons: ["Cancel", "Yes!"],
        confirmButtonColor: '#0000FF',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((willDelete) => {
        if (willDelete) {
            form.submit();
        }
    });
});
</script>
@endsection