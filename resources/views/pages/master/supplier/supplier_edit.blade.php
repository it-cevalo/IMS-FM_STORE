@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Master Data Supplier</h6>
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
        <form id="formSupplierUpdate" action="{{route('suppliers.update', $suppliers->id)}}" method="POST">
            @csrf
            {{ method_field('PUT') }}
            <div class="mb-3">
                <label for="exampleFormControlInput1">Supplier Code</label>
                <input class="form-control" id="exampleFormControlInput1" name="code_spl" type="text"
                    value="{{$suppliers->code_spl}}">
            </div>
            <div class="validation"></div>
            @error('code_spl')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Supplier Name</label>
                <input class="form-control" id="exampleFormControlInput1" name="nama_spl" type="text"
                    value="{{$suppliers->nama_spl}}">
            </div>
            <div class="validation"></div>
            @error('nama_spl')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Supplier Phone</label>
                <input class="form-control" id="exampleFormControlInput1" name="phone" type="number" min="0"
                    value="{{$suppliers->phone}}">
            </div>
            <div class="validation"></div>
            @error('phone')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Supplier Email</label>
                <input class="form-control" id="exampleFormControlInput1" name="email" type="email"
                    value="{{$suppliers->email}}">
            </div>
            <div class="validation"></div>
            @error('email')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Supplier NPWP</label>
                <input class="form-control" id="exampleFormControlInput1" name="npwp_spl" type="text"
                    value="{{$suppliers->npwp_spl}}">
            </div>
            <div class="validation"></div>
            @error('npwp_spl')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Supplier Address</label>
                <input class="form-control" id="exampleFormControlInput1" name="address_spl" type="text"
                    value="{{$suppliers->address_spl}}">
            </div>
            <div class="validation"></div>
            @error('address_spl')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Supplier Address NPWP</label>
                <input class="form-control" id="exampleFormControlInput1" name="address_npwp" type="text"
                    value="{{$suppliers->address_npwp}}">
            </div>
            <div class="validation"></div>
            @error('address_npwp')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Supplier PIC Name</label>
                <input class="form-control" id="exampleFormControlInput1" name="name_pic" type="text"
                    value="{{$suppliers->name_pic}}">
            </div>
            <div class="validation"></div>
            @error('name_pic')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Supplier PIC Phone</label>
                <input class="form-control" id="exampleFormControlInput1" name="phone_pic" type="number" min="0"
                    value="{{$suppliers->phone_pic}}">
            </div>
            <div class="validation"></div>
            @error('phone_pic')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Supplier PIC Email</label>
                <input class="form-control" id="exampleFormControlInput1" name="email_pic" type="email"
                    value="{{$suppliers->email_pic}}">
            </div>
            <div class="validation"></div>
            @error('email_pic')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <button type="button" class="btn btn-primary" id="btnUpdateSupplier">Submit</button>
            <a href="{{route('suppliers.index')}}" class="btn btn-dark">Cancel</a>
        </form>
    </div>
</div>
<script>
    $('#btnUpdateSupplier').on('click', function () {
        let form = $('#formSupplierUpdate')[0];
        let formData = new FormData(form);
        let url = $('#formSupplierUpdate').attr('action');

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            beforeSend: function () {
                $('#btnUpdateSupplier').prop('disabled', true).text('Saving...');
            },
            success: function (res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: res.message
                }).then(() => {
                    window.location.href = "{{ route('suppliers.index') }}";
                });
            },
            error: function (xhr) {
                $('#btnUpdateSupplier').prop('disabled', false).text('Submit');

                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let errorList = '<ul class="text-start">';
                    $.each(errors, function (key, messages) {
                        $.each(messages, function (i, msg) {
                            errorList += `<li>${msg}</li>`;
                        });
                    });
                    errorList += '</ul>';

                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Input',
                        html: errorList
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: res && res.message ? res.message : 'An unexpected error occurred. Please try again later.'
                    });
                }
            }
        });
    });
</script>
@endsection