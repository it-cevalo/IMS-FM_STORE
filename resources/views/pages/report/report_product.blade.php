@extends('layouts.admin')

@section('content')
<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Report Product</h6>
    </div>
    <div class="card-header py-3">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
            <i class="fa fa-filter"></i> Filter Report
        </button>
        {{-- <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal2">
                <i class="fa fa-file-export"></i> Export Report
            </button> --}}
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
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <th class="text-center align-middle">No</th>
                    <th class="text-center align-middle">SKU</th>
                    <th class="text-center align-middle">Name</th>
                    <th class="text-center align-middle">Type</th>
                    <th class="text-center align-middle">UOM</th>
                    <th class="text-center align-middle">Active</th>
                </thead>
                <tbody>
                    <?php
                                        $no=1;
                                        ?>
                    @foreach($mproduct as $f)
                    <tr>
                        <td>{{$no++}}</td>
                        <td>{{$f->SKU}}</td>
                        <td>{{$f->nama_barang}}</td>
                        <td>{{$f->product_type->nama_tipe}}</td>
                        <td>{{$f->product_unit->nama_unit}}</td>
                        <td>{{$f->flag_active}}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td colspan="7" style="align-items: center;">
                            {{ $mproduct->links() }}
                            Total : {{ $mproduct->total() }} data
                        </td>
                    </tr>
                </tbody>
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
                <h5 class="modal-title" id="exampleModalLabel"><i class="fa fa-filter"></i> Filter Report</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="GET" action="{{route('report_product.filter')}}">
                    <div class="mb-3">
                        <label for="exampleFormControlInput1">Filter / Export</label>
                        <div class="input-group">
                            <select class="form-control" name="opt" value="{{old('opt')}}" required>
                                <option value="filter">Filter</option>
                                <option value="export">Export to PDF</option>
                            </select>
                        </div>
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
<script>
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