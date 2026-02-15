@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Ajukan Ulang Cetak QR</h6>
        <a href="{{ route('purchase_order.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
    </div>
    <div class="card-body">
        @foreach($requestsGrouped as $poNo => $items)
        <h5 class="mt-3">PO: {{ $poNo }} ({{ $items[0]->tgl_po }})</h5>

        <!-- Bulk Action Buttons -->
        <div class="mb-2">
            <button class="btn btn-success btn-sm" onclick="bulkApprove('{{ $poNo }}')">Setujui</button>
            <button class="btn btn-danger btn-sm" onclick="bulkReject('{{ $poNo }}')">Tolak</button>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" class="check-all" data-po="{{ $poNo }}">
                    </th>
                    <th>SKU</th>
                    <th>Nama Barang</th>
                    <th>No Urut</th>
                    <th>Alasan</th>
                    <th>Status</th>
                    <th>Tanggal Pengajuan</th>
                    <th>Yang Mengajukan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $r)
                <tr id="row-{{ $r->request_id }}">
                    <td>
                        @if($r->status == 'PENDING')
                        <input type="checkbox" class="check-item" data-po="{{ $poNo }}" data-id="{{ $r->request_id }}">
                        @endif
                    </td>
                    <td>{{ $r->part_number }}</td>
                    <td>{{ $r->product_name }}</td>
                    <td>{{ $r->sequence_no }}</td>
                    <td>{{ $r->reason }}</td>
                    <td id="status-{{ $r->request_id }}">{{ $r->status }}</td>
                    <td>{{ $r->requested_at }}</td>
                    <td>{{ $r->requested_by }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endforeach
    </div>
</div>

<script>
$(document).ready(function() {
    // CHECK ALL LOGIC
    $('.check-all').change(function() {
        var poNo = $(this).data('po');
        var checked = $(this).is(':checked');
        $('input.check-item[data-po="'+poNo+'"]').prop('checked', checked);
    });

    $('.check-item').change(function() {
        var poNo = $(this).data('po');
        var all = $('input.check-item[data-po="'+poNo+'"]');
        var allChecked = all.length === all.filter(':checked').length;
        $('input.check-all[data-po="'+poNo+'"]').prop('checked', allChecked);
    });

    // INITIAL HIDE BULK BUTTONS IF NO PENDING ITEM
    $('div.mb-2').each(function() {
        var poNo = $(this).find('button.btn-success, button.btn-danger').first().attr('onclick').match(/'(.+?)'/)[1];
        if($('input.check-item[data-po="'+poNo+'"]').length === 0) {
            $(this).hide();
        }
    });
});

// HIDE BULK BUTTONS IF ALL ITEMS PROCESSED
function hideButtonsIfAllProcessed(poNo) {
    if($('input.check-item[data-po="'+poNo+'"]').length === 0) {
        $('button:contains("Approve Selected")[onclick*="'+poNo+'"]').hide();
        $('button:contains("Reject Selected")[onclick*="'+poNo+'"]').hide();
    }
}

// SWEETALERT2 BULK APPROVE / REJECT
function bulkAction(poNo, action) {
    let ids = [];
    $('input.check-item[data-po="'+poNo+'"]:checked').each(function(){
        ids.push($(this).data('id'));
    });
    if(ids.length === 0){
        Swal.fire('Oops', 'Pilih minimal 1 barang.', 'warning');
        return;
    }

    Swal.fire({
        title: (action === 'approve' ? 'Proses Persetujuan...' : 'Proses Menolak...'),
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
            $.post(
                action === 'approve'
                    ? '{{ route("reprint.approve") }}'
                    : '{{ route("reprint.reject") }}',
                {_token:'{{ csrf_token() }}', ids:ids},
                function(res){
                    Swal.close();

                    if(res.success){

                        ids.forEach(function(id){
                            $('#status-'+id).text(action === 'approve' ? 'APPROVED' : 'REJECTED');
                            $('#row-'+id+' input.check-item').remove();
                        });

                        hideButtonsIfAllProcessed(poNo);

                        Swal.fire('Success', 'Operation completed.', 'success');

                        // 🔥 AUTO PRINT SETELAH APPROVE
                        if(action === 'approve' && res.printUrl){
                            window.open(res.printUrl, '_blank');
                        }

                    } else {
                        Swal.fire('Error', 'Operation failed.', 'error');
                    }
                }
            )
            // $.post(
            //     action === 'approve' ? '{{ route("reprint.approve") }}' : '{{ route("reprint.reject") }}',
            //     {_token:'{{ csrf_token() }}', ids:ids},
            //     function(res){
            //         Swal.close();
            //         if(res.success){
            //             ids.forEach(function(id){
            //                 $('#status-'+id).text(action === 'approve' ? 'APPROVED' : 'REJECTED');
            //                 $('#row-'+id+' input.check-item').remove();
            //             });
            //             hideButtonsIfAllProcessed(poNo);
            //             Swal.fire('Success', 'Operation completed.', 'success');
            //         } else {
            //             Swal.fire('Error', 'Operation failed.', 'error');
            //         }
            //     }
            // ).fail(function(){
            //     Swal.close();
            //     Swal.fire('Error', 'Request failed.', 'error');
            // });
        }
    });
}

function bulkApprove(poNo) {
    bulkAction(poNo, 'approve');
}

function bulkReject(poNo) {
    bulkAction(poNo, 'reject');
}
</script>
@endsection
