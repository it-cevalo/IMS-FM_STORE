@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Master Data SKU</h6>
        <div>
            <a href="{{ route('sku.template.download') }}" class="btn btn-success btn-sm">
                <i class="fa fa-file-excel"></i> Download Template
            </a>
            <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalImportExcel" id="btnImportExcel">
                <i class="fa fa-upload"></i> Import Excel
            </button>
            <a href="{{ route('sku.create') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> Add
            </a>
        </div>
    </div>

    <div class="card-body">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @elseif(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered" id="skuTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-center align-middle">No</th>
                        <th class="text-center align-middle">SKU Code</th>
                        {{-- <th class="text-center align-middle">Name</th> --}}
                        <th class="text-center align-middle">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <!-- Modal Import Excel -->
        <div class="modal fade" id="modalImportExcel" tabindex="-1" aria-labelledby="modalImportLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form id="formImportExcel" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalImportLabel">Import Data SKU</h5>
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
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>

    // ==== DATATABLE LOAD ====
    function loadSkuData() {
        $('#skuTable').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: '{{ route('sku.data') }}',
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false, className: "text-center" },
                { data: 'kode', name: 'kode' },
                // { data: 'nama', name: 'nama' },
                { data: 'action', orderable: false, searchable: false, className: "text-center" }
            ]
        });
    }

    function closeModal(id) {
        const el = document.querySelector(id);

        // Bootstrap 5
        if (window.bootstrap?.Modal) {
            let modal = bootstrap.Modal.getInstance(el);
            if (!modal) modal = new bootstrap.Modal(el);
            modal.hide();
            return;
        }

        // Bootstrap 4 (jQuery)
        if (window.jQuery && $(el).modal) {
            $(el).modal('hide');
            return;
        }

        // fallback manual
        el.classList.remove('show');
        el.style.display = 'none';
        document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
        document.body.classList.remove('modal-open');
    }

    $(document).ready(function () {

        loadSkuData();

        // Auto fade alert
        setTimeout(() => $('.alert').fadeOut(), 4000);


        // ==== AJAX IMPORT EXCEL ====
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
                url: "{{ route('sku.import') }}", // AJAX route
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {

                    // reload table
                    $('#skuTable').DataTable().ajax.reload(null, false);

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    });

                    // tutup modal & reset form
                    closeModal('#modalImportExcel');
                    $('#formImportExcel')[0].reset();
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


        // ==== DELETE SKU ====
        $(document).on('click', '.btnDeleteSku', function (e) {
            e.preventDefault();

            let deleteUrl = $(this).data('url');

            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: "Data SKU ini tidak bisa dikembalikan setelah dihapus.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: deleteUrl,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function (res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: res.message
                            });

                            $('#skuTable').DataTable().ajax.reload(null, false);
                        },
                        error: function (xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message ?? 'Terjadi kesalahan saat menghapus data.'
                            });
                        }
                    });
                }
            });
        });

    });
</script>
@endsection