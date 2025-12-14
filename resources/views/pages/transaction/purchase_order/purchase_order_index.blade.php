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
        <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#filterModal">
            <i class="fa fa-filter"></i> Filter
        </button>
        <button class="btn btn-secondary btn-sm" id="btnRefresh">
            <i class="fa fa-sync"></i> Refresh
        </button>
        {{-- <a href="#" class="btn btn-primary btn-flat btn-sm" data-toggle="modal" data-target="#exampleModal"><i
                class="fa fa-upload"></i> Upload Excel</a>
        <a download="Template_po.xlsx" href="{{ Storage::url('tpl/template_po.xlsx') }}"
            class="btn btn-primary btn-flat btn-sm" title="Template_po.xlsx"><i class="fa fa-download"></i> Download
            Template Excel</a> --}}
        <!-- <a href="{{route('purchase_order.bin')}}" class="btn btn-primary btn-flat btn-sm"><i class="fa fa-archive"></i>
            See Archive</a><br /> -->
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
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Filter Purchase Order</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label>Date From</label>
                    <input type="date" id="filter_date_from" class="form-control">
                </div>

                <div class="form-group">
                    <label>Date To</label>
                    <input type="date" id="filter_date_to" class="form-control">
                </div>

                <div class="form-group">
                    <label>Status PO</label>
                    <select id="filter_status" class="form-control">
                        <option value="">-- All Status --</option>
                        <option value="0">Created</option>
                        <option value="1">Progress</option>
                        <option value="2">Partial</option>
                        <option value="3">Complete</option>
                        <option value="4">Confirmed</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btnApplyFilter">
                    Go
                </button>
            </div>

        </div>
    </div>
</div>
<script type="text/javascript">
    let purchaseOrderTable;

    function loadPurchaseOrderData(filters = {}) {
        purchaseOrderTable = $('#purchaseOrderTable').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: {
                url: '{{ route('purchase_order.data') }}',
                data: function (d) {
                    d.date_from = filters.date_from ?? '';
                    d.date_to   = filters.date_to ?? '';
                    d.status_po = filters.status_po ?? '';
                }
            },
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
                            } else if(row.flag_approve === 'C'){
                                statusText = 'Confirmed';
                                badgeClass = 'badge badge-light';
                            } else {
                                statusText = '-';
                                badgeClass = 'badge badge-light';
                            }
                        } else {
                            switch (data.toString()) {
                                case '0':
                                    statusText = 'Created';
                                    badgeClass = 'badge badge-secondary'; 
                                    // Netral → baru dibuat, belum diproses
                                    break;

                                case '1':
                                    statusText = 'Progress';
                                    badgeClass = 'badge badge-warning';
                                    // Kuning → sedang berjalan / butuh perhatian
                                    break;

                                case '2':
                                    statusText = 'Partial';
                                    badgeClass = 'badge badge-info';
                                    // Biru muda → sebagian selesai
                                    break;

                                case '3':
                                    statusText = 'Complete';
                                    badgeClass = 'badge badge-success';
                                    // Hijau → selesai dengan baik
                                    break;

                                case '4':
                                    statusText = 'Confirmed';
                                    badgeClass = 'badge badge-primary';
                                    // Biru → sudah dikunci / resmi
                                    break;

                                case '5':
                                    statusText = 'Canceled';
                                    badgeClass = 'badge badge-danger';
                                    // Merah → dibatalkan / terminasi
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

    function confirmOrder(orderId, poNumber) {
        Swal.fire({
            title: 'Konfirmasi PO',
            text: `PO nomor ${poNumber} akan dikonfirmasi?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya',
            cancelButtonText: 'Tidak',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('purchase_order.confirm', ':id') }}".replace(':id', orderId),
                    type: 'GET',
                    success: function (res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'PO berhasil dikonfirmasi'
                            });

                            // reload datatable
                            purchaseOrderTable.ajax.reload(null, false);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: res.error ?? 'Konfirmasi gagal'
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan sistem'
                        });
                    }
                });
            }
            // jika Tidak → swal otomatis tertutup
        });
    }
    
    function bindDeleteButtons() {
        $('.show-alert-delete-box').off('click').on('click', function (event) {
            event.preventDefault();

            let btn   = $(this);
            let id    = btn.data('id');
            let noPo  = btn.data('no-po');

            Swal.fire({
                title: 'Yakin ingin Cancel?',
                html: `<strong>PO Number:</strong> ${noPo}`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Cancel',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {

                    $.ajax({
                        url: "{{ route('purchase_order.delete', ':id') }}".replace(':id', id),
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function (res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message
                            });

                            // reload datatable TANPA reset page
                            purchaseOrderTable.ajax.reload(null, false);
                        },
                        error: function (xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message ?? 'Cancel gagal'
                            });
                        }
                    });

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

    $('#btnApplyFilter').on('click', function () {
        let filters = {
            date_from: $('#filter_date_from').val(),
            date_to: $('#filter_date_to').val(),
            status_po: $('#filter_status').val()
        };

        purchaseOrderTable.destroy();
        loadPurchaseOrderData(filters);

        // hide modal safely
        if ($.fn.modal) {
            $('#filterModal').modal('hide');
        } else {
            document.getElementById('filterModal').classList.remove('show');
            document.body.classList.remove('modal-open');
            $('.modal-backdrop').remove();
        }
    });
    
    $('#btnRefresh').on('click', function () {
        // reset modal input
        $('#filter_date_from').val('');
        $('#filter_date_to').val('');
        $('#filter_status').val('');
        purchaseOrderTable.destroy();
        loadPurchaseOrderData();
    });
</script>
@endsection