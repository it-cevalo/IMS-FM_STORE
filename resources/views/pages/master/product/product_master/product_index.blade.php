@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Master Data Produk</h6>
        <div>
            <a href="{{ route('product.template.download') }}" class="btn btn-success btn-sm">
                <i class="fa fa-file-excel"></i> Download Template
            </a>
            <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalImportExcel" id="btnImportExcel">
                <i class="fa fa-upload"></i> Import Excel
            </button>
            <a href="{{route('product.create')}}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> Tambah
            </a>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="productTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>SKU</th>
                        <th>Nama</th>
                        <th>Tipe</th>
                        <th>Satuan Barang</th>
                        <th>Aktif</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody> <!-- diisi oleh DataTables -->
            </table>
        </div>
        
        <!-- Modal Import Excel -->
        <div class="modal fade" id="modalImportExcel" tabindex="-1" aria-labelledby="modalImportLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form id="formImportExcel" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalImportLabel">Import Data Produk</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Upload File (.xlsx)</label>
                                <input type="file" name="file" class="form-control" accept=".xlsx" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function loadProductData() {
        $('#productTable').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: '{{ route('product.data') }}',
            columns: [
                { data: 'DT_RowIndex', orderable: false },
                { data: 'sku', name: 'sku' },
                { data: 'nama_barang' },
                { data: 'type' },
                { data: 'uom' },
                { data: 'flag_active' },
                { data: 'action', orderable: false }
            ]
        });
    }

    $(document).ready(function () {
        loadProductData();

        // =========================
        // AJAX IMPORT EXCEL
        // =========================
        $('#formImportExcel').on('submit', function (e) {
            e.preventDefault();

            let formData = new FormData(this);

            Swal.fire({
                title: 'Mengupload...',
                text: 'Mohon tunggu, sistem sedang memproses file.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: "{{ route('product.import') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {

                    // reload datatable
                    $('#productTable').DataTable().ajax.reload(null, false);

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // =========================
                        // AUTO CLOSE MODAL
                        // =========================
                        $('#modalImportExcel').modal('hide');
                    });
                },
                error: function (xhr) {

                    let msg = "Terjadi kesalahan saat import.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: msg
                    });
                }
            });
        });

        // =========================
        // RESET FORM SETELAH MODAL BENAR2 TERTUTUP
        // =========================
        $('#modalImportExcel').on('hidden.bs.modal', function () {
            $('#formImportExcel')[0].reset();
        });

        // Auto hide alert
        setTimeout(() => $('.alert').fadeOut(), 5000);
        $('.alert button.close').on('click', function () {
            $(this).closest('.alert').fadeOut();
        });
    });
</script>
@endsection