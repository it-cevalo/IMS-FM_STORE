@extends('layouts.admin')

@section('content')
<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <a href="{{route('purchase_order.index')}}">Stock In</a>
        </h6>
    </div>
    <div class="card-header py-3">
        @if(Auth::user()->position=='MANAGER' || Auth::user()->position=='SUPERADMIN' || Auth::user()->position=='PURCHASING')
        <a href="{{route('purchase_order.create')}}" class="btn btn-primary btn-flat btn-sm">
            <i class="fa fa-plus"></i> Tambah
        </a>
        <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#filterModal">
            <i class="fa fa-filter"></i> Saring
        </button>
        <button class="btn btn-secondary btn-sm" id="btnRefresh">
            <i class="fa fa-sync"></i> Segarkan
        </button>
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
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Pemasok</th>
                        <th class="text-center">Nomor</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Catatan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>        
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter Pemesanan Barang</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Dari Tanggal</label>
                    <input type="date" id="filter_date_from" class="form-control">
                </div>
                <div class="form-group">
                    <label>Sampai Tanggal</label>
                    <input type="date" id="filter_date_to" class="form-control">
                </div>
                <div class="form-group">
                    <label>Status Penerimaan Barang</label>
                    <select id="filter_status" class="form-control">
                        <option value="">-- Semua Status --</option>
                        <option value="0">Dibuat</option>
                        <option value="1">Proses</option>
                        <option value="2">Berkala</option>
                        <option value="3">Lengkap</option>
                        <option value="4">Terkonfirmasi</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btnApplyFilter">Cek</button>
            </div>
        </div>
    </div>
</div>
<!-- Reprint Modal -->
<div class="modal fade" id="modalReprintList" tabindex="-1" role="dialog" aria-labelledby="modalReprintLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalReprintLabel">Ajukan Ulang Cetak QR</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>PO</th>
                            <th>Detail</th>
                            <th>No Urut</th>
                            <th>Alasan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="reqBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<script>
$(document).ready(function() {
    // ================= DataTable =================
    let purchaseOrderTable = $('#purchaseOrderTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('purchase_order.data') }}',
            data: function(d) {
                d.date_from = $('#filter_date_from').val() || '';
                d.date_to = $('#filter_date_to').val() || '';
                d.status_po = $('#filter_status').val() || '';
            }
        },
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
        },
        columns: [
            { data: 'tgl_po', render: data => data ? data.split(' ')[0] : '' },
            { data: null, render: d => `${d.code_spl} - ${d.nama_spl}` },
            { data: 'no_po' },
            { 
                data: 'status_po',
                render: function(data, type, row) {
                    let statusText = '', badgeClass = '';
                    if (!data) {
                        switch(row.flag_approve) {
                            case 'N': statusText='Menunggu Persetujuan'; badgeClass='badge badge-secondary'; break;
                            case 'Y': statusText='Disetujui'; badgeClass='badge badge-success'; break;
                            case 'C': statusText='Terkonfirmasi'; badgeClass='badge badge-light'; break;
                            default: statusText='-'; badgeClass='badge badge-light';
                        }
                    } else {
                        switch(data.toString()) {
                            case '0': statusText='Dibuat'; badgeClass='badge badge-secondary'; break;
                            case '1': statusText='Proses'; badgeClass='badge badge-warning'; break;
                            case '2': statusText='Berkala'; badgeClass='badge badge-info'; break;
                            case '3': statusText='Lengkap'; badgeClass='badge badge-success'; break;
                            case '4': statusText='Terkonfirmasi'; badgeClass='badge badge-primary'; break;
                            case '5': statusText='Dibatalkan'; badgeClass='badge badge-danger'; break;
                            default: statusText=data; badgeClass='badge badge-light';
                        }
                    }
                    return `<span class="${badgeClass}">${statusText}</span>`;
                }
            },
            { data: 'reason_po' },
            { data: 'action', orderable: false, searchable: false }
        ],
        drawCallback: function() {
            bindApproveButtons();
            bindDeleteButtons();
        }
    });

    // =============== Approve / Delete ===============
    function bindApproveButtons(){
        $('[id^="approveBtn"]').off('click').on('click', function(){
            let id = $(this).data('id');
            $.get(`{{ route("purchase_order.approve", ":id") }}`.replace(':id', id), function(){
                purchaseOrderTable.ajax.reload(null,false);
                Swal.fire({icon:'success',title:'Pesanan Disetujui!'});
            }).fail(()=>Swal.fire({icon:'error',title:'Oops...',text:'Gagal menyetujui pesanan!'}));
        });
    }

    function bindDeleteButtons(){
        $('.show-alert-delete-box').off('click').on('click', function(e){
            e.preventDefault();
            let btn = $(this), id = btn.data('id'), noPo = btn.data('no-po');
            Swal.fire({
                title:'Yakin ingin dibatalkan?',
                html:`<strong>Number:</strong> ${noPo}`,
                icon:'warning', showCancelButton:true,
                confirmButtonText:'Ya, Cancel', cancelButtonText:'Batal', reverseButtons:true
            }).then(result=>{
                if(result.isConfirmed){
                    $.ajax({
                        url: `{{ route('purchase_order.delete', ':id') }}`.replace(':id', id),
                        type:'DELETE',
                        data:{_token:'{{ csrf_token() }}'},
                        success:function(res){ Swal.fire({icon:'success',title:'Berhasil',text:res.message}); purchaseOrderTable.ajax.reload(null,false); },
                        error:function(xhr){ Swal.fire({icon:'error',title:'Gagal',text:xhr.responseJSON?.message||'Gagal dibatalkan'}); }
                    });
                }
            });
        });
    }

    // ================= Filter =================
    $('#btnApplyFilter').on('click', function(){
        purchaseOrderTable.ajax.reload();
        $('#filterModal').modal('hide');
    });

    $('#btnRefresh').on('click', function(){
        $('#filter_date_from').val('');
        $('#filter_date_to').val('');
        $('#filter_status').val('');
        purchaseOrderTable.ajax.reload();
    });

    // ================= Reprint =================
    // Tombol Reprint
    $("#btnReprintRequest").on("click", function(){
        // Pastikan modal Bootstrap 4 sudah tersedia
        // if (typeof $.fn.modal === 'function') {
            $("#modalReprintList").modal('show');
            loadReprintRequest();
        // } else {
        //     alert("Bootstrap Modal tidak tersedia. Pastikan bootstrap.js sudah dimuat di layout.");
        // }
    });

    // Fungsi load reprint request
    function loadReprintRequest(){
        $.get('/qr/reprint/list', function(res){
            $("#totalReq").text(res.length); // update total request

            let html = '';
            res.forEach(r => {
                html += `
                <tr>
                    <td>${r.created_at}</td>
                    <td>${r.no_po}</td>
                    <td>${r.id_po_detail}</td>
                    <td>${r.sequence_no}</td>
                    <td>${r.reason}</td>
                    <td>
                        <button class="btn btn-success btn-sm" onclick="approveReq(${r.id})">Setujui</button>
                        <button class="btn btn-danger btn-sm" onclick="rejectReq(${r.id})">Tolak</button>
                    </td>
                </tr>`;
            });

            $("#reqBody").html(html);
        });
    }

    // Buat global supaya bisa dipanggil dari tombol di tabel
    window.approveReq = function(id){
        $.post('/qr/reprint/approve', { id:id, _token:'{{ csrf_token() }}' }, loadReprintRequest);
    }

    window.rejectReq = function(id){
        $.post('/qr/reprint/reject', { id:id, _token:'{{ csrf_token() }}' }, loadReprintRequest);
    }
    
    window.confirmOrder = function (id, noPo) {
        Swal.fire({
            title: 'Konfirmasi Pemesanan Barang',
            html: `
                <p>Apakah Anda yakin ingin <b>Konfirmasi</b> Penerimaan Barang berikut?</p>
                <p><strong>Nomor:</strong> ${noPo}</p>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ route('purchase_order.confirm', ':id') }}`.replace(':id', id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message || 'Pemesanan Barang berhasil dikonfirmasi'
                        });

                        $('#purchaseOrderTable')
                            .DataTable()
                            .ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: xhr.responseJSON?.message || 'Confirm PO gagal'
                        });
                    }
                });
            }
        });
    }
});
</script>
@endsection