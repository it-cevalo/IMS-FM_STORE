@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Master Data Customer</h6>
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
        <form id="formCustomerStore" method="POST">
            @csrf
            <div class="mb-3">
                <label for="exampleFormControlInput1">Customer Code</label>
                <input class="form-control" id="exampleFormControlInput1" name="code_cust" type="text"
                    placeholder="Input Customer Code">
            </div>
            <div class="validation"></div>
            @error('code_cust')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Customer Name</label>
                <input class="form-control" id="exampleFormControlInput1" name="nama_cust" type="text"
                    placeholder="Input Customer Name">
            </div>
            <div class="validation"></div>
            @error('nama_cust')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Customer Phone</label>
                <input class="form-control" id="exampleFormControlInput1" name="phone" type="number" min="0"
                    placeholder="Input Customer Phone">
            </div>
            <div class="validation"></div>
            @error('phone')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Customer Email</label>
                <input class="form-control" id="exampleFormControlInput1" name="email" type="email"
                    placeholder="Input Customer Email">
            </div>
            <div class="validation"></div>
            @error('email')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Customer Type</label>
                <select class="form-control select2" id="type_cust" name="type_cust" value="{{old('type_cust')}}"
                    required>
                    <option value="#">....</option>
                    <option value="B">Business</option>
                    <option value="C">Non Business</option>
                </select>
            </div>
            <div class="validation"></div>
            @error('type_cust')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Customer NPWP</label>
                <input class="form-control" id="exampleFormControlInput1" name="npwp_cust" type="number" max="16"
                    placeholder="Input Customer NPWP">
            </div>
            <div class="validation"></div>
            @error('npwp_cust')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Customer Address</label>
                <input class="form-control" id="exampleFormControlInput1" name="address_cust" type="text"
                    placeholder="Input Customer Address">
            </div>
            <div class="validation"></div>
            @error('address_cust')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Customer Address NPWP</label>
                <input class="form-control" id="exampleFormControlInput1" name="address_npwp" type="text"
                    placeholder="Input Customer Address NPWP">
            </div>
            <div class="validation"></div>
            @error('address_npwp')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <button type="button" class="btn btn-primary" id="btnStoreCustomer">Submit</button>
            <a href="{{route('customers.index')}}" class="btn btn-dark">Cancel</a>
        </form>
    </div>
</div>
<script>
    $('#btnStoreCustomer').on('click', function () {
        const form = $('#formCustomerStore')[0];
        const formData = new FormData(form);

        $.ajax({
            url: "{{ route('customers.store') }}",
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                $('#btnStoreCustomer').prop('disabled', true).text('Submitting...');
            },
            success: function (res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message || 'Customer berhasil ditambahkan.'
                }).then(() => {
                    window.location.href = "{{ route('customers.index') }}";
                });
            },
            error: function (xhr) {
                $('#btnStoreCustomer').prop('disabled', false).text('Submit');

                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let list = '<ul class="text-start">';
                    $.each(errors, function (key, messages) {
                        messages.forEach(msg => {
                            list += `<li>${msg}</li>`;
                        });
                    });
                    list += '</ul>';
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validasi Gagal!',
                        html: list
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan. Silakan coba kembali.'
                    });
                }
            }
        });
    });
</script>
@endsection