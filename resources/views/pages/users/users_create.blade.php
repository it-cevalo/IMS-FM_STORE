@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div><br />
    @endif
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Pengelolaan Pengguna</h6>
    </div>
    <div class="card-body">
        <form id="userCreateForm">
            @csrf
            <div class="mb-3">
                <label for="exampleFormControlInput1">Nama</label>
                <input class="form-control" id="exampleFormControlInput1" name="name" type="text"
                    placeholder="Jhon Doe">
            </div>
            <div class="mb-3">
                <label for="exampleFormControlInput1">Username</label>
                <input class="form-control" id="exampleFormControlInput1" name="username" type="text"
                    placeholder="Jhon Doe">
            </div>
            <div class="mb-3">
                <label for="exampleFormControlInput1">Password</label>
                <input class="form-control" id="myInput" name="password" type="password">
                <!-- <input type="checkbox" onclick="myFunction()">Show Password -->
            </div>
            <div class="mb-3">
                <label for="exampleFormControlInput1">Email address</label>
                <input class="form-control" id="exampleFormControlInput1" name="email" type="email"
                    placeholder="name@example.com">
            </div>
            <div class="mb-3">
                <label>Role</label>
                <select name="role_id" class="form-control" required>
                    <option value="">-- Pilih Role --</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('users.index') }}" class="btn btn-dark">Kembali</a>
        </form>
    </div>
</div>
<script>
    $('#userCreateForm').submit(function(e){
        e.preventDefault();

        Swal.fire({
            title: 'Menyimpan...',
            text: 'Mohon tunggu',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: "{{ route('users.store') }}",
            method: "POST",
            data: $(this).serialize(),
            success: function(res){
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.message
                }).then(() => {
                    window.location.href = "{{ route('users.index') }}";
                });
            },
            error: function(xhr){
                let msg = 'Terjadi kesalahan';

                if (xhr.status === 422) {
                    msg = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: msg
                });
            }
        });
    });
</script>
@endsection