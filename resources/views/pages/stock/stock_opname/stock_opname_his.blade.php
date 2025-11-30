@extends('layouts.admin')

@section('content')
<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><a href="{{route('stock_opname.index')}}">Stock Opname History</a>
        </h6>
    </div>
    <div class="card-header py-3">
        <!-- @if(Auth::user()->position=='SUPERADMIN')
                            
                                <button type="button" class="btn btn-primary btn-flat btn-sm" data-toggle="modal" data-target="#exampleModal">
                                    <i class="fa fa-filter"></i> Filter
                                </button>
                                <a href="{{route('stock_opname.create')}}" class="btn btn-primary btn-flat btn-sm"><i class="fa fa-plus"></i> Add</a>
                                a href="#" class="btn btn-primary btn-flat btn-sm" data-toggle="modal" data-target="#exampleModal"><i class="fa fa-upload"></i> Upload Excel</a>
                                <a download="Template_po.xlsx" href="{{ Storage::url('tpl/template_po.xlsx') }}" class="btn btn-primary btn-flat btn-sm" title="Template_po.xlsx"><i class="fa fa-download"></i> Download Template Excel</a>
                            @elseif(Auth::user()->position=='MARKETING') 
                                <a href="{{route('stock_opname.create')}}" class="btn btn-primary btn-flat btn-sm"><i class="fa fa-plus"></i> Add</a>
                                <a href="#" class="btn btn-primary btn-flat btn-sm" data-toggle="modal" data-target="#exampleModal"><i class="fa fa-upload"></i> Upload Excel</a>
                                <a download="Template_po.xlsx" href="{{ Storage::url('tpl/template_po.xlsx') }}" class="btn btn-primary btn-flat btn-sm" title="Template_po.xlsx"><i class="fa fa-download"></i> Download Template Excel</a>
                            @else 
                            @endif -->
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
                        <th colspan="2" class="text-center">Warehouse</th>
                        <th colspan="2" class="text-center">Product</th>
                        <th colspan="3" class="text-center">QTY</th>
                        <th rowspan="2" class="text-center align-middle">Date Opname</th>
                        <!-- <th rowspan="2" colspan="3" class="text-center align-middle">Action</th> -->
                    </tr>
                    <tr>
                        <th class="text-center">Code</th>
                        <th class="text-center">Name</th>
                        <th class="text-center">Code</th>
                        <th class="text-center">Name</th>
                        <th class="text-center">In</th>
                        <th class="text-center">Out</th>
                        <th class="text-center">Last</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                                            $no=1;
                                        ?>
                    @foreach($stock_opname_his as $f)
                    <tr>
                        <td>{{$f->warehouse->code_wh}}</td>
                        <td>{{$f->warehouse->nama_wh}}</td>
                        <td>{{$f->product->SKU}}</td>
                        <td>{{$f->product->nama_barang}}</td>
                        <td>{{$f->qty_in}}</td>
                        <td>{{$f->qty_out}}</td>
                        <td>{{$f->qty_last}}</td>
                        <td>{{$f->tgl_opname}}</td>
                        <!-- <td><a href="{{route('stock_opname.show',$f->id)}}" class="btn btn-flat btn-primary btn-sm"><i class="fa fa-eye"></i></a></td>
                                            <td><a href="{{route('stock_opname.history',$f->id)}}" class="btn btn-flat btn-success btn-sm"><i class="fa fa-history"></i></a></td> 
                                            <td><a href="{{route('stock_opname.edit',$f->id)}}" class="btn btn-warning btn-flat btn-sm"><i class="fa fa-edit"></i></a></td> -->
                    </tr>
                </tbody>
                @endforeach
                <tr>
                    <td colspan="12" style="align-items: center;">
                    </td>
                </tr>
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
                <h5 class="modal-title" id="exampleModalLabel"><i class="fa fa-filter"></i> Filter</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="GET" action="{{route('report_courier.filter')}}">
                    <div class="mb-3">
                        <label for="exampleFormControlInput1">Product</label>
                        <select class="form-control select2" id="search-type" name="id_unit" value="{{old('id_unit')}}"
                            required>
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
                <button type="submit" class="btn btn-primary">Execute</button>
            </div>
            </form>
        </div>
    </div>
</div>
<!-- End Modal Filter -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script type="text/javascript">
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