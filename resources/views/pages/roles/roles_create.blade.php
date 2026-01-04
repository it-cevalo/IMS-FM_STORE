@extends('layouts.admin')

@section('content')
<form id="roleCreateForm">
    @csrf
    <div class="row">

        {{-- LEFT --}}
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body">
                    <label>Nama Role</label>
                    <input name="name" class="form-control mb-3" required>

                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('roles.index') }}" class="btn btn-success">Kembali</a>
                </div>
            </div>
        </div>

        <div class="mb-2">
            <small class="text-muted d-block">
                <span class="badge bg-secondary me-1">Parent</span>
                Menu utama (header / group)
            </small>
            <small class="text-muted d-block">
                <span class="badge bg-info me-1">Child</span>
                Menu turunan
            </small>
        </div>

        {{-- RIGHT --}}
        <div class="col-md-8">
            <div class="card shadow">
                <table class="table table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Menu</th>
                            <th width="120" class="text-center">Enable</th>
                        </tr>
                    </thead>
                    <tbody>

                    @foreach($menus[null] ?? [] as $parent)
                        <tr class="bg-light fw-bold">
                            <td>{{ $parent->name }}</td>
                            <td class="text-center">
                                <input type="checkbox" name="menus[]" value="{{ $parent->menu_id }}">
                            </td>
                        </tr>

                        @foreach($menus[$parent->menu_id] ?? [] as $child)
                        <tr>
                            <td class="ps-4">{{ $child->name }}</td>
                            <td class="text-center">
                                <input type="checkbox" name="menus[]" value="{{ $child->menu_id }}">
                            </td>
                        </tr>
                        @endforeach
                    @endforeach

                    </tbody>
                </table>
            </div>
        </div>

    </div>
</form>
<script>
$('#roleCreateForm').on('submit', function(e) {
    e.preventDefault();

    Swal.fire({
        title: 'Menyimpan...',
        text: 'Mohon tunggu',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        url: "{{ route('roles.store') }}",
        method: "POST",
        data: $(this).serialize(),
        success: function(res) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: res.message
            }).then(() => {
                window.location.href = "{{ route('roles.index') }}";
            });
        },
        error: function(xhr) {
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
