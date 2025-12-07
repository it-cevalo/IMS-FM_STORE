@extends('layouts.admin')

@section('content')
<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><a href="{{route('purchase_order.index')}}">Purchase Order</a>
        </h6>
    </div>
    <div class="card-header py-3">
        @if(Auth::user()->position=='MANAGER' || Auth::user()->position=='SUPERADMIN' ||
        Auth::user()->position=='PURCHASING')
        <a href="{{route('purchase_order.create')}}" class="btn btn-primary btn-flat btn-sm"><i class="fa fa-plus"></i>
            Add</a>
        {{-- <a href="#" class="btn btn-primary btn-flat btn-sm" data-toggle="modal" data-target="#exampleModal"><i
                class="fa fa-upload"></i> Upload Excel</a>
        <a download="Template_po.xlsx" href="{{ Storage::url('tpl/template_po.xlsx') }}"
            class="btn btn-primary btn-flat btn-sm" title="Template_po.xlsx"><i class="fa fa-download"></i> Download
            Template Excel</a> --}}
        <a href="{{route('purchase_order.bin')}}" class="btn btn-primary btn-flat btn-sm"><i class="fa fa-archive"></i>
            See Archive</a><br />
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
            <table class="table table-bordered" id="purchaseOrderTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-center">Date</th>
                        <th class="text-center">Supplier</th>
                        <th class="text-center">PO Number</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Note</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>        
    </div>
</div>

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Upload Excel Here</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="{{route('purchase_order.upload')}}" enctype="multipart/form-data">
                <!-- <div class="modal-content"> -->
                <div class="modal-body">

                    {{ csrf_field() }}

                    <div class="form-group">
                        <input type="file" name="file" required="required">
                    </div>
                    <!-- <div class="form-group">
                            <ul>
                                <h6>Tata Cara Mengisi Template</h6>
                                <li>Kolom A untuk Tanggal PO dengan Format YYYY-MM-DD (Contoh : 2022-08-17)</li>
                                <li>Kolom B untuk Kode Customer</li>
                                <li>Kolom C untuk Nama Customer</li>
                                <li>Kolom D untuk Nomor PO</li>
                                <li>Kolom E untuk Nomor SO</li>
                                <li>Kolom F untuk Status PO</li>
                                <li>Kolom G untuk Reason PO</li>
                                <img src="{{url('/assets/img/po.png')}}" alt="Image" class="img-fluid rounded mx-auto d-block" />
                            </ul>
                        </div> -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
                <!-- </div> -->
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    function loadPurchaseOrderData() {
        $('#purchaseOrderTable').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: '{{ route('purchase_order.data') }}',
            columns: [
                { 
                    data: 'tgl_po', 
                    name: 'tgl_po',
                    render: function(data) {
                        if (!data) return '';
                        return data.split(' ')[0];
                    }
                },
                {
                    data: null,
                    name: 'supplier',
                    render: function (data) {
                        return `${data.code_spl} - ${data.nama_spl}`;
                    }
                },
                { data: 'no_po', name: 'no_po' },
                {
                    data: 'status_po',
                    name: 'status_po',
                    render: function (data, type, row) {
                        let statusText = '';
                        let badgeClass = '';

                        if (!data) {
                            if (row.flag_approve === 'N') {
                                statusText = 'Waiting Approval';
                                badgeClass = 'badge badge-secondary';
                            } else if (row.flag_approve === 'Y') {
                                statusText = 'Approved';
                                badgeClass = 'badge badge-success';
                            } else {
                                statusText = '-';
                                badgeClass = 'badge badge-light';
                            }
                        } else {
                            switch (data.toString()) {
                                case '0':
                                    statusText = 'Rejected';
                                    badgeClass = 'badge badge-danger';
                                    break;
                                case '1':
                                    statusText = 'Progress';
                                    badgeClass = 'badge badge-warning';
                                    break;
                                case '2':
                                    statusText = 'Partial';
                                    badgeClass = 'badge badge-info';
                                    break;
                                case '3':
                                    statusText = 'Complete';
                                    badgeClass = 'badge badge-primary';
                                    break;
                                default:
                                    statusText = data;
                                    badgeClass = 'badge badge-light';
                            }
                        }

                        return `<span class="${badgeClass}">${statusText}</span>`;
                    }
                },
                { data: 'reason_po', name: 'reason_po' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            drawCallback: function () {
                bindApproveButtons();
                bindDeleteButtons();
            }
        });
    }

    function bindApproveButtons() {
        $('[id^="approveBtn"]').off('click').on('click', function () {
            var id = $(this).data('id');

            $.ajax({
                url: '{{ route("purchase_order.approve", ":id") }}'.replace(':id', id),
                type: 'GET',
                success: function (response) {
                    $('#dataTable').DataTable().ajax.reload(null, false); // reload datatable
                    Swal.fire({
                        icon: 'success',
                        title: 'Pesanan Disetujui!',
                    });
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Gagal menyetujui pesanan!',
                    });
                }
            });
        });
    }
    
    function bindDeleteButtons() {
        $('.show-alert-delete-box').off('click').on('click', function (event) {
            var form = $(this).closest("form");
            event.preventDefault();
            Swal.fire({
                title: 'Yakin ingin hapus?',
                text: 'Data akan dipindahkan ke arsip.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya!',
                cancelButtonText: 'Batal',
                reverseButtons: false
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    }

    function approveOrder(orderId) {
        $.ajax({
            url: "{{ route('purchase_order.approve', ':id') }}".replace(':id', orderId),
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

    $(document).ready(function() {
        loadPurchaseOrderData();
    });
    
</script>
@endsection