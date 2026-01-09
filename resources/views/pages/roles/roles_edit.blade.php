@extends('layouts.admin')

@section('content')
<form id="roleEditForm">
    @csrf
    @method('PUT')
    <div class="row">

        {{-- LEFT --}}
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body">
                    <label>Nama Role</label>
                    <input name="name"
                        class="form-control mb-2"
                        value="{{ $role->name }}"
                        required>

                    {{-- KETERANGAN --}}
                    <div class="mb-3">
                        <small class="text-muted d-flex align-items-center mb-1">
                            <span class="d-inline-block me-2"
                                style="width:14px;height:14px;background:#f8f9fa;border:1px solid #ddd"></span>
                            Menu utama (header / group)
                        </small>
                        <small class="text-muted d-flex align-items-center">
                            <span class="d-inline-block me-2"
                                style="width:14px;height:14px;background:#ffffff;border:1px solid #ddd"></span>
                            Menu turunan
                        </small>
                    </div>

                    <button type="submit" class="btn btn-success">Update</button>
                    <a href="{{ route('roles.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </div>
        </div>
        {{-- RIGHT --}}
        <div class="col-md-8">
            <div class="card shadow">

                {{-- ================= WEB MENU ================= --}}
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Web Menu
                    </h6>
                    <button type="button"
                        class="btn btn-sm btn-outline-primary"
                        data-toggle="collapse"
                        data-target="#webMenuCollapse">
                        Toggle
                    </button>
                </div>

                <div id="webMenuCollapse" class="collapse show">
                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Menu</th>
                                    <th class="text-center" width="120">Enable</th>
                                </tr>
                            </thead>
                            <tbody>

                            @foreach($menus[null] ?? [] as $parent)
                                <tr class="bg-light font-weight-bold">
                                    <td>{{ $parent->name }}</td>
                                    <td class="text-center">
                                        <input type="checkbox"
                                            name="menus[]"
                                            value="{{ $parent->menu_id }}"
                                            {{ in_array($parent->menu_id, $roleMenus) ? 'checked' : '' }}>
                                    </td>
                                </tr>

                                @foreach($menus[$parent->menu_id] ?? [] as $child)
                                <tr>
                                    <td class="pl-4">{{ $child->name }}</td>
                                    <td class="text-center">
                                        <input type="checkbox"
                                            name="menus[]"
                                            value="{{ $child->menu_id }}"
                                            {{ in_array($child->menu_id, $roleMenus) ? 'checked' : '' }}>
                                    </td>
                                </tr>
                                @endforeach
                            @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ================= APPS MENU ================= --}}
                <div class="card-header py-3 d-flex justify-content-between align-items-center border-top">
                    <h6 class="m-0 font-weight-bold text-success">
                        Apps Menu
                    </h6>
                    <button type="button"
                        class="btn btn-sm btn-outline-success"
                        data-toggle="collapse"
                        data-target="#appsMenuCollapse">
                        Toggle
                    </button>
                </div>

                <div id="appsMenuCollapse" class="collapse">
                    <div class="card-body">
                        @forelse($apps ?? [] as $app)
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox"
                                    class="custom-control-input"
                                    id="app_{{ $app->app_code }}"
                                    name="apps[]"
                                    value="{{ $app->app_code }}"
                                    {{ in_array($app->app_code, $roleApps ?? []) ? 'checked' : '' }}>
                                <label class="custom-control-label"
                                    for="app_{{ $app->app_code }}">
                                    {{ $app->name }}
                                </label>
                            </div>
                        @empty
                            <span class="text-muted">Belum ada Apps terdaftar</span>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>
</form>

<script>
$('#roleEditForm').on('submit', function(e) {
    e.preventDefault();

    Swal.fire({
        title: 'Memperbarui...',
        text: 'Mohon tunggu',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        url: "{{ route('roles.update', $role->id) }}",
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
