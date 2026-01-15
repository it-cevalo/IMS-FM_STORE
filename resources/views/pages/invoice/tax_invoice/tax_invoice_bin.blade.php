@extends('layouts.admin')

@section('content')
<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><a href="{{route('tax_invoice.bin')}}">Tax Invoice BIN</a></h6>
    </div>
    <div class="card-header py-3">
        <!-- <div class="input-group">
                                <form method="GET" action="{{route('purchase_order.search')}}">
                                    <label>
                                        <input type="search" class="form-control form-control-sm" placeholder="Search" name="search" aria-controls="dataTable">
                                    </label>
                                    <button class="btn btn-primary btn-sm" type="submit">
                                        <i class="fas fa-search fa-sm"></i>
                                    </button>
                                </form>
                            </div> -->
        <!-- <a href="{{route('purchase_order.create')}}">TambahPemesanan Barang</a> -->
        <!-- <a href="#" class="btn btn-link btn-sm" data-toggle="modal" data-target="#exampleModal"><i class="fa fa-upload"></i> Upload Excel</a> -->
        <!-- <a download="Template_po.xlsx" href="{{ Storage::url('tpl/template_po.xlsx') }}" title="Template_po.xlsx"><i class="fa fa-download"></i> Download Template Excel</a> -->

        <!-- <a href="{{route('purchase_order.download')}}" class="btn btn-link btn-sm"><i class="fa fa-download"></i> Download Template Excel</a> -->
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th rowspan="2" class="text-center align-middle">No</th>
                        <th colspan="2" class="text-center">Customer</th>
                        <th colspan="4" class="text-center">Pemesanan Barang</th>
                        <th colspan="4" class="text-center">Pengiriman Barang</th>
                        <th colspan="4" class="text-center">Invoice</th>
                        <th rowspan="2" class="text-center align-middle">SO Number</th>
                        <th rowspan="2" class="text-center align-middle text-wrap">Reason</th>
                        <th rowspan="2" class="text-center align-middle text-wrap">Status</th>
                        <th rowspan="2" class="text-center align-middle">Term</th>
                        <th rowspan="2" class="text-center align-middle">Grand Total</th>
                        <th rowspan="2" class="text-center align-middle">Tax Code</th>
                        <th rowspan="2" class="text-center align-middle">Metode Pengiriman</th>
                        <th rowspan="2" colspan="3" class="text-center align-middle">Aksi</th>
                    </tr>
                    <tr>

                        <th class="text-center align-middle text-wrap">Kode</th>
                        <th class="text-center align-middle text-wrap">Nama</th>

                        <th class="text-center align-middle text-wrap">Tanggal</th>
                        <th class="text-center align-middle text-wrap">Kode</th>
                        <th class="text-center align-middle text-wrap">Status</th>
                        <th class="text-center align-middle text-wrap">Reason</th>


                        <th class="text-center align-middle text-wrap">Tanggal</th>
                        <th class="text-center align-middle text-wrap">Kode</th>
                        <th class="text-center align-middle text-wrap">Status</th>
                        <th class="text-center align-middle text-wrap">Reason</th>


                        <th class="text-center align-middle text-wrap">Tanggal</th>
                        <th class="text-center align-middle text-wrap">Kode</th>
                        <th class="text-center align-middle text-wrap">Status</th>
                        <th class="text-center align-middle text-wrap">Reason</th>

                    </tr>
                </thead>
                <tbody>
                    <?php
                                            $no=1;
                                        ?>
                    @foreach($data as $f)
                    <tr>
                        <!-- <td style="font-size:13px;">{{$no++}}</td> -->
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
                        <td>
                            <a href="{{route('tax_invoice.rollback', $f->id)}}"
                                class="btn btn-flat btn-primary btn-sm show-alert-rollback-box"><i
                                    class="fa fa-undo"></i></a>
                        </td>
                    </tr>
                </tbody>
                @endforeach
                <tr>
                    <td colspan="10" style="align-items: center;">
                        {{ $data->links() }}
                        Total : {{ $data->total() }} data
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
@endsection