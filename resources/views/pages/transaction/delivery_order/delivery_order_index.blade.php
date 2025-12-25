@extends('layouts.admin')

@section('content')
<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <a href="{{route('delivery_order.index')}}">Delivery Order</a>
        </h6>
    </div>

    <div class="card-header py-3">
        @if(Auth::user()->position=='SUPERADMIN')
        {{-- <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target=".modalBesar1">
            <i class="fa fa-upload"></i> Upload File DO
        </button> --}}
        <a href="{{route('delivery_order.create')}}" class="btn btn-primary btn-flat btn-sm">
            <i class="fa fa-plus"></i> Add
        </a>
        {{-- <a href="{{route('delivery_order.bin')}}" class="btn btn-primary btn-flat btn-sm">
            <i class="fa fa-archive"></i> See Archive
        </a> --}}
        @elseif(Auth::user()->position=='WAREHOUSE')
        {{-- <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target=".modalBesar1">
            <i class="fa fa-upload"></i> Upload File DO
        </button> --}}
        <a href="{{route('delivery_order.create')}}" class="btn btn-primary btn-flat btn-sm">
            <i class="fa fa-plus"></i> Add
        </a>
        {{-- <a href="{{route('delivery_order.bin')}}" class="btn btn-primary btn-flat btn-sm">
            <i class="fa fa-archive"></i> See Archive
        </a> --}}
        @endif
    </div>

    <div class="card-body">
        @if(\Session::has('fail'))
        <div class="alert alert-danger">
            <span>{{\Session::get('fail')}}</span>
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered" id="deliveryOrderTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Date</th>
                        {{-- <th>Supplier</th> --}}
                        {{-- <th>PO Number</th> --}}
                        <th>DO Number</th>
                        <th>Shipping Via</th>
                        {{-- <th>File</th>
                        <th>Upload Date</th>
                        <th>Attachment Status</th> --}}
                        <th>Note</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Upload DO File --}}
{{-- <div class="modal fade modalBesar1" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload DO File Here</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="post" action="{{route('delivery_order.uploadDO')}}" enctype="multipart/form-data">
                <div class="modal-body">
                    {{ csrf_field() }}
                    <div class="input-group">
                        <select class="form-control" name="id_do" required>
                            <option value="">Select DO</option>
                            @foreach($delivery_order as $p)
                            <option value="{{$p->id}}">
                                {{ \Carbon\Carbon::parse($p->tgl_do)->format('Y-m-d')}}/{{$p->no_do}}/{{$p->nama_cust}}
                            </option>
                            @endforeach
                        </select>
                    </div><br />
                    <div class="form-group">
                        <input type="file" name="file" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div> --}}

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
function loadDeliveryOrderData() {
    const baseUrl = "{{ config('app.url') }}";

    $('#deliveryOrderTable').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: '{{ route('delivery_order.data') }}',
        columns: [            
            { data: 'tgl_do', render: data => data ? data.split(' ')[0] : '' },

            // ✅ Gabungan Supplier Code + Name
            // { 
            //     data: null, 
            //     name: 'supplier',
            //     render: function(data, type, row) {
            //         let code = row.po?.code_spl || '-';
            //         let name = row.po?.nama_spl || '-';
            //         return `${code} - ${name}`;
            //     }
            // },

            // Klik No PO ke halaman PO.show
            // { 
            //     data: 'no_po', 
            //     name: 'no_po',
            //     render: function(data, type, row) {
            //         if (!data || !row.po_id) return data || '-';
            //         return `<a href="${baseUrl}/purchase_order/${row.po_id}" class="text-primary font-weight-bold">${data}</a>`;
            //     }
            // },
            {
                data: 'no_do', 
                name: 'no_do',
            },
            { data: 'shipping_via', name: 'shipping_via' },
            // { 
            //     data: 'file', 
            //     name: 'file', 
            //     render: (data) => data 
            //         ? `<a href="/storage/${data}" target="_blank">${data}</a>` 
            //         : 'No Document'
            // },
            // { data: 'upload_date_at', name: 'upload_date_at' },
            // { data: 'status_lmpr_do', name: 'status_lmpr_do' },
            { data: 'reason_do', name: 'reason_do' },
            {
                data: 'flag_approve',
                render: function(data) {
                    if (data === 'Y') {
                        return `<span class="badge badge-success">Approved</span>`;
                    }
                    return `<span class="badge badge-secondary">Created</span>`;
                }
            },
            { 
                data: 'action', 
                name: 'action', 
                orderable: false, 
                searchable: false,
                render: function(data) {
                    return data.replace(/<a[^>]*fa-eye[^>]*><\/i><\/a>/, '');
                }
            }
        ]
    });
}

function approveDO(id, noDo) {
    Swal.fire({
        title: 'Approve Delivery Order?',
        html: `<b>DO Number:</b> ${noDo}`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Approve',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {

        if (result.isConfirmed) {

            // 🔄 Loading
            Swal.fire({
                title: 'Memproses...',
                text: 'Mohon tunggu',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('delivery_order.approve', ':id') }}".replace(':id', id),
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function (res) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: res.message || 'Delivery Order berhasil di-approve'
                    });

                    $('#deliveryOrderTable')
                        .DataTable()
                        .ajax.reload(null, false);
                },
                error: function (xhr) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.error || 'Approve gagal'
                    });
                }
            });
        }
    });
}

// function approveDO(id, noDo) {
//     Swal.fire({
//         title: 'Approve Delivery Order?',
//         html: `<b>DO Number:</b> ${noDo}`,
//         icon: 'warning',
//         showCancelButton: true,
//         confirmButtonText: 'Yes, Approve',
//         cancelButtonText: 'Cancel',
//         reverseButtons: true
//     }).then((result) => {
//         if (result.isConfirmed) {
//             $.get(
//                 "{{ route('delivery_order.approve', ':id') }}".replace(':id', id),
//                 function () {
//                     Swal.fire('Success', 'Delivery Order berhasil di-approve', 'success');
//                     $('#deliveryOrderTable').DataTable().ajax.reload(null, false);
//                 }
//             ).fail(err => {
//                 Swal.fire(
//                     'Error',
//                     err.responseJSON?.error || 'Approve gagal',
//                     'error'
//                 );
//             });
//         }
//     });
// }

function deleteDO(id, noDo) {
    Swal.fire({
        title: 'Delete Delivery Order?',
        html: `<b>DO Number:</b> ${noDo}<br><span class="text-danger">Data akan dihapus permanen</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('delivery_order.delete', ':id') }}".replace(':id', id),
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function (res) {
                    Swal.fire('Deleted!', res.message, 'success');
                    $('#deliveryOrderTable').DataTable().ajax.reload(null, false);
                },
                error: function (xhr) {
                    Swal.fire(
                        'Error',
                        xhr.responseJSON?.message || 'Delete gagal',
                        'error'
                    );
                }
            });
        }
    });
}

$(document).ready(function () {
    loadDeliveryOrderData();
});
</script>
@endsection
