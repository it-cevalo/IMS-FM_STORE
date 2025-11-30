@extends('layouts.admin')

<!-- Start Style CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
<!-- End Style CSS -->

@section('content')
<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><a href="{{route('tax_invoice.index')}}">Tax Invoice</a></h6>
    </div>
    <div class="card-header py-3">
        @if(Auth::user()->position!='DIRECTOR')
        <a href="{{route('tax_invoice.create')}}" class="btn btn-primary btn-flat btn-sm"><i class="fa fa-plus"></i>
            Add</a>
        <a href="{{route('tax_invoice.bin')}}" class="btn btn-primary btn-flat btn-sm"><i class="fa fa-archive"></i> See
            Archive</a>
        @else
        @endif
    </div>
    <div class="card-body">
        <div class="input-group">
            <form method="GET" action="{{route('tax_invoice.search')}}">
                <label>
                    <input type="text" class="form-control form-control-sm" placeholder="Search" name="search"
                        aria-controls="dataTable">
                </label>
                <button class="btn btn-primary btn-sm" type="submit">
                    <i class="fas fa-search fa-sm"></i>
                </button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th rowspan="2" class="text-center align-middle">No</th>
                        <th colspan="2" class="text-center">Customer</th>
                        <th colspan="4" class="text-center">Purchase Order</th>
                        <th colspan="4" class="text-center">Delivery Order</th>
                        <th colspan="4" class="text-center">Invoice</th>
                        <th rowspan="2" class="text-center align-middle">SO Number</th>
                        <th rowspan="2" class="text-center align-middle text-wrap">Reason</th>
                        <th rowspan="2" class="text-center align-middle text-wrap">Status</th>
                        <th rowspan="2" class="text-center align-middle">Term</th>
                        <th rowspan="2" class="text-center align-middle">Grand Total</th>
                        <th rowspan="2" class="text-center align-middle">Tax Code</th>
                        <th rowspan="2" class="text-center align-middle">Shipping Via</th>
                        <th rowspan="2" colspan="5" class="text-center align-middle">Action</th>
                    </tr>
                    <tr>

                        <th class="text-center align-middle text-wrap">Code</th>
                        <th class="text-center align-middle text-wrap">Name</th>

                        <th class="text-center align-middle text-wrap">Date</th>
                        <th class="text-center align-middle text-wrap">No</th>
                        <th class="text-center align-middle text-wrap">Status</th>
                        <th class="text-center align-middle text-wrap">Reason</th>


                        <th class="text-center align-middle text-wrap">Date</th>
                        <th class="text-center align-middle text-wrap">No</th>
                        <th class="text-center align-middle text-wrap">Status</th>
                        <th class="text-center align-middle text-wrap">Reason</th>


                        <th class="text-center align-middle text-wrap">Date</th>
                        <th class="text-center align-middle text-wrap">No</th>
                        <th class="text-center align-middle text-wrap">Status</th>
                        <th class="text-center align-middle text-wrap">Reason</th>

                    </tr>
                </thead>
                <tbody>
                    <?php
                            $no=1;
                        ?>
                    @foreach($tax_invoice as $f)
                    <tr>
                        <td>{{$no++}}</td>
                        <td>{{$f->do->code_cust ?? 'NA'}}</td>
                        <td>{{$f->do->nama_cust ?? 'NA'}}</td>
                        <td>{{ \Carbon\Carbon::parse($f->do->tgl_po)->format('Y-m-d')}}</td>
                        <td>{{$f->do->no_po ?? 'NA'}}</td>
                        <td>{{$f->po->status_po ?? 'NA'}}</td>
                        <td>{{$f->po->reason_po ?? 'NA'}}</td>
                        <td>{{ \Carbon\Carbon::parse($f->do->tgl_do)->format('Y-m-d')}}</td>
                        <td>{{$f->do->no_do}}</td>
                        <td>{{$f->do->status_lmpr_do ?? 'NA'}}</td>
                        <td>{{$f->do->reason_do ?? 'NA'}}</td>
                        <td>{{ \Carbon\Carbon::parse($f->tgl_inv)->format('Y-m-d')}}</td>
                        <td>{{$f->no_inv}}</td>
                        <td>{{$f->status_inv ?? 'NA' }}</td>
                        <td>{{$f->reason_inv ?? 'NA' }}</td>
                        <td>{{$f->po->no_so ?? 'NA'}}</td>
                        <td>{{$f->reason_faktur_pajak}}</td>
                        <td>{{$f->status_faktur_pajak}}</td>
                        <td>{{$f->term}}</td>
                        <td>{{$f->grand_total}}</td>
                        <td>{{$f->no_seri_pajak}}</td>
                        <td>{{$f->shipping_via}}</td>
                        @if(Auth::user()->position=='SUPERADMIN' || Auth::user()->position=='INVOICINGTAX')
                        <td><a href="{{route('tax_invoice.show',$f->id)}}" class="btn btn-flat btn-primary btn-sm"><i
                                    class="fa fa-eye"></i></a></td>
                        <td><a href="{{route('tax_invoice.history',$f->id)}}" class="btn btn-flat btn-success btn-sm"><i
                                    class="fa fa-history"></i></a></td>
                        <td><a href="{{route('tax_invoice.pdf',$f->id)}}" class="btn btn-flat btn-info btn-sm"><i
                                    class="fa fa-file-pdf-o"></i></a></td>
                        <td><a href="{{route('tax_invoice.edit',$f->id)}}" class="btn btn-warning btn-flat btn-sm"><i
                                    class="fa fa-edit"></i></a></td>
                        <td>
                            <form action="{{route('tax_invoice.delete',$f->id)}}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-flat btn-danger show-alert-delete-box btn-sm"
                                    data-toggle="tooltip" title='Delete'><i class="fa fa-trash"></i></button>
                        </td>
                        </form>
                        </td>
                        @elseif(Auth::user()->position=='MANAGER_FINANCE')
                        <td><a href="{{route('tax_invoice.show',$f->id)}}" class="btn btn-flat btn-primary btn-sm"><i
                                    class="fa fa-eye"></i></a></td>
                        <td><a href="{{route('tax_invoice.history',$f->id)}}" class="btn btn-flat btn-success btn-sm"><i
                                    class="fa fa-history"></i></a></td>
                        @elseif(Auth::user()->position=='STAFF_FINANCE')
                        <td><a href="{{route('tax_invoice.show',$f->id)}}" class="btn btn-flat btn-primary btn-sm"><i
                                    class="fa fa-eye"></i></a></td>
                        <td><a href="{{route('tax_invoice.history',$f->id)}}" class="btn btn-flat btn-success btn-sm"><i
                                    class="fa fa-history"></i></a></td>
                        @elseif(Auth::user()->position=='DIRECTOR')
                        <td><a href="{{route('tax_invoice.edit',$f->id)}}" class="btn btn-warning btn-flat btn-sm"><i
                                    class="fa fa-edit"></i></a></td>
                        @endif
                    </tr>
                </tbody>
                @endforeach
                <tr>
                    <td colspan="33" style="align-items: center;">
                        {{ $tax_invoice->links() }}
                        Total : {{ $tax_invoice->total() }} data
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
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