@extends('layouts.admin')

@section('content')
<div class="card shadow">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">Product Inbound</h6>
    </div>

    <div class="card-body">
        <table class="table table-bordered" id="inboundTable" width="100%">
            <thead>
                <tr>
                    <th>QR</th>
                    <th>SKU</th>
                    <th>Produk</th>
                    <th>Qty</th>
                    <th>PO</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<script>
    let inboundTable;

    /**
     * Init Datatable Product Inbound
     */
    function loadInboundTable() {

        // destroy jika sudah pernah di-init (safe reload)
        if ($.fn.DataTable.isDataTable('#inboundTable')) {
            $('#inboundTable').DataTable().destroy();
        }

        inboundTable = $('#inboundTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: "{{ route('product_inbound.datatable') }}",
                type: "GET"
            },
            columns: [
                { data: 'qr_code' },
                { data: 'SKU' },
                { data: 'nama_barang' },
                { data: 'qty', className: 'text-center' },
                { data: 'no_po' },
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function (id) {
                        return `
                            <a href="/product_inbound/${id}/edit"
                               class="btn btn-sm btn-primary">
                                Process
                            </a>
                        `;
                    }
                }
            ],
            order: [[0, 'desc']],
            language: {
                processing: "Loading data..."
            }
        });
    }

    /**
     * Auto load ketika halaman muncul
     */
    $(document).ready(function () {
        loadInboundTable();
    });
</script>
@endsection