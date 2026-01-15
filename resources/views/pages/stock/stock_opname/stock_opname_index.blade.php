@extends('layouts.admin')

@section('content')                    
<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><a href="{{route('stock_opname.index')}}">Stock Opname</a></h6>
    </div>
    <div class="card-header py-3">
        @if(Auth::user()->position=='SUPERADMIN')
        
            <button type="button" class="btn btn-primary btn-flat btn-sm" data-toggle="modal" data-target="#exampleModal">
                <i class="fa fa-filter"></i> Filter
            </button>
            {{-- <a href="{{route('stock_opname.create')}}" class="btn btn-primary btn-flat btn-sm"><i class="fa fa-plus"></i> Tambah</a> --}}
            <!-- <a href="#" class="btn btn-primary btn-flat btn-sm" data-toggle="modal" data-target="#exampleModal"><i class="fa fa-upload"></i> Upload Excel</a>
            <a download="Template_po.xlsx" href="{{ Storage::url('tpl/template_po.xlsx') }}" class="btn btn-primary btn-flat btn-sm" title="Template_po.xlsx"><i class="fa fa-download"></i> Download Template Excel</a> -->
        @elseif(Auth::user()->position=='MARKETING') 
            {{-- <a href="{{route('stock_opname.create')}}" class="btn btn-primary btn-flat btn-sm"><i class="fa fa-plus"></i> Tambah</a> --}}
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
            <table class="table table-bordered" id="stockOpnameTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th colspan="2" class="text-center">Gudang</th>
                        <th colspan="2" class="text-center">Produk</th>
                        <th rowspan="2" class="text-center align-middle">QTY Terakhir</th>
                        <th rowspan="2" class="text-center align-middle">Tanggal Opname</th>
                        <th rowspan="2" class="text-center align-middle">Aksi</th>
                    </tr>
                    <tr>
                        <th class="text-center">Kode</th>
                        <th class="text-center">Nama</th>
                        <th class="text-center">Kode</th>
                        <th class="text-center">Nama</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>                    
    </div>
</div>
<!-- Start Modal Filter -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><i class="fa fa-filter"></i> Saring</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="filterForm">
                        <div class="mb-3">
                            <label for="exampleFormControlInput1">Produk</label>
                                <select class="form-control select2" id="search-type" name="id_unit" value="{{old('id_unit')}}" required>
                                    <option value="#">....</option>
                                    @foreach($products as $p)
                                    <option value="{{$p->id}}">{{$p->nama_barang}}</option>
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
                    <button type="button" id="filterSubmit" class="btn btn-primary">Execute</button>
                </div>
                </form>
            </div>
        </div>
    </div>
<!-- End Modal Filter -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script type="text/javascript">
    function loadStockOpnameData(params = {}) {
        $('#stockOpnameTable').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: {
                url: '{{ route('stock_opname.data') }}',
                data: params
            },
            columns: [
                { data: 'warehouse_code', name: 'warehouse.code_wh' },
                { data: 'warehouse_name', name: 'warehouse.nama_wh' },
                { data: 'product_code', name: 'product.SKU' },
                { data: 'product_name', name: 'product.nama_barang' },
                { data: 'qty_last', name: 'qty_last' },
                {
                    data: 'created_at',
                    name: 'created_at',
                    render: function (data) {
                        if (!data) return '-';

                        const d = new Date(data);
                        const pad = n => n.toString().padStart(2, '0');

                        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
                    }
                },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    }

    $(document).ready(function () {
        loadStockOpnameData();
        setTimeout(() => $('.alert').fadeOut(), 5000);
        // submit filter dan tutup modal
$('#filterSubmit').on('click', function () {
    let params = {
        product_id: $('#search-type').val(),
        fd: $('[name="fd"]').val(),
        td: $('[name="td"]').val()
    };

    // Tutup modal secara manual
    $('#exampleModal').removeClass('show').hide();
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open').css('padding-right', '');

    // Reload datatable
    loadStockOpnameData(params);
});


    });

    $('.show-alert-delete-box').click(function(event){
        var form =  $(this).closest("form");
        var name = $(this).data("name");
        event.preventDefault();
        swal({
            title: "Are you sure you want to delete this record?",
            text: "If you delete this, it will be go to archive.",
            icon: "warning",
            type: "warning",
            buttons: ["Cancel","Yes!"],
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