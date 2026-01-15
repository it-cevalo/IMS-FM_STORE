@extends('layouts.admin')

<!-- Start Style CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
<!-- End Style CSS -->

@section('content')
<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><a href="{{route('report_invoicing.index')}}">Report Invoice</a>
        </h6>
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
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th rowspan="2" class="text-center align-middle">Number</th>
                        <th colspan="2" class="text-center">Customer</th>
                        <th colspan="2" class="text-center">Invoice</th>
                        <th rowspan="2" class="text-center align-middle">Grand Total</th>
                    </tr>
                    <tr>

                        <th class="text-center align-middle text-wrap">Kode</th>
                        <th class="text-center align-middle text-wrap">Nama</th>

                        <th class="text-center align-middle text-wrap">Tanggal</th>
                        <th class="text-center align-middle text-wrap">Kode</th>

                    </tr>
                </thead>
                <tbody>
                    <?php
                        $no=1;
                    ?>
                    @if($invoice)
                    @foreach($invoice as $f)
                    <tr>
                        <td>{{$no++}}</td>
                        <td>{{$f->code_cust ?? 'NA'}}</td>
                        <td>{{$f->nama_cust ?? 'NA'}}</td>
                        <td>{{ \Carbon\Carbon::parse($f->tgl_inv)->format('Y-m-d')}}</td>
                        <td>{{$f->no_inv ?? 'NA'}}</td>
                        <td>{{$f->grand_total ?? 'NA'}}</td>
                    </tr>
                </tbody>
                @endforeach
                <tr>
                    <td colspan="11" style="align-items: center;">
                        {{ $invoice->links() }}
                        Total : {{ $invoice->total() }} data
                    </td>
                </tr>
                @else
                <tr>
                    <td colspan="14" class="text-center align-middle text-wrap">No Data</td>
                </tr>
                @endif
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
                <form method="GET" action="{{route('report_invoicing.filter')}}">
                    <div class="mb-3">
                        <label for="exampleFormControlInput1">Filter / Export</label>
                        <div class="input-group">
                            <select class="form-control" name="opt" value="{{old('opt')}}" required>
                                <option value="filter">Saring</option>
                                <option value="export">Export to PDF</option>
                            </select>
                        </div>
                    </div>
                    {{-- <div class="mb-3">
                                <label for="exampleFormControlInput1">Customer</label>
                                    <div class="input-group">
                                            <select class="form-control" name="id_cust" value="{{old('id_cust')}}">
                    <option value="">All</option>
                    @foreach($customer as $p)
                    <option value="{{$p->id}}">{{$p->code_cust}}/{{$p->nama_cust}}</option>
                    @endforeach
                    </select>
            </div>
        </div> --}}
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