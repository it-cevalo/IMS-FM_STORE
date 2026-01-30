@extends('layouts.admin')

@section('content')
<form id="roleEditForm">
@csrf
@method('PUT')

<div class="row">

{{-- ================= LEFT ================= --}}
<div class="col-md-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <label class="fw-semibold">Nama Role</label>
            <input name="name"
                   class="form-control mb-3"
                   value="{{ $role->name }}"
                   required>

            <div class="text-muted small">
                • Beranda selalu aktif dan tidak bisa dinonaktifkan<br>
                • Aktifkan menu utama untuk menampilkan sub-menu<br>
                • Aktifkan <b>Lihat</b> untuk membuka hak aksi<br>
                • <b>Full Akses</b> = semua hak ON (klik lagi untuk reset)
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-success">Update</button>
                <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </div>
    </div>
</div>

{{-- ================= RIGHT ================= --}}
<div class="col-md-8">
<div class="card shadow-sm">

{{-- ================= WEB MENU ================= --}}
<div class="card-header d-flex justify-content-between align-items-center">
    <h6 class="fw-bold text-primary mb-0">Hak Akses Web Menu</h6>
    <button type="button" id="btnFullAccess" class="btn btn-sm btn-danger">
        Full Akses
    </button>
</div>

<div class="card-body p-0">
<table class="table permission-table mb-0">
<thead>
<tr>
    <th>Menu</th>
    <th width="120">Aktif</th>
    <th>Aksi</th>
</tr>
</thead>
<tbody>

@foreach($menus[null] ?? [] as $parent)
@php
    $parentActive = collect($menus[$parent->menu_id] ?? [])
        ->pluck('menu_id')
        ->intersect($rolePermissions->keys())
        ->isNotEmpty();
@endphp

<tr class="menu-master {{ $parentActive ? 'is-active' : 'is-inactive' }}"
    data-parent="{{ $parent->menu_id }}">

    <td class="fw-bold text-uppercase">{{ $parent->name }}</td>

    <td class="text-center">
        <label class="switch">
            <input type="checkbox"
                   class="parent-toggle"
                   data-parent="{{ $parent->menu_id }}"
                   {{ $parentActive ? 'checked' : '' }}>
            <span class="slider"></span>
        </label>
    </td>

    <td class="text-muted small">Menu grup</td>
</tr>

@foreach($menus[$parent->menu_id] ?? [] as $child)
@php
    $perm = $rolePermissions[$child->menu_id] ?? null;
    $childActive = $perm && $perm->can_view;
@endphp

<tr class="menu-child {{ $parentActive ? '' : 'd-none' }} {{ $childActive ? 'is-active' : 'is-inactive' }}"
    data-parent="{{ $parent->menu_id }}"
    data-menu="{{ $child->menu_id }}">

    <td class="ps-4 fw-medium">{{ $child->name }}</td>

    <td class="text-center">
        <label class="switch">
            <input type="checkbox"
                   class="perm-view"
                   data-menu="{{ $child->menu_id }}"
                   name="permissions[{{ $child->menu_id }}][view]"
                   {{ $childActive ? 'checked' : '' }}>
            <span class="slider"></span>
        </label>
    </td>

    <td>
        <div class="action-wrap {{ $childActive ? '' : 'd-none' }}"
             id="action-{{ $child->menu_id }}">
            @foreach(['create'=>'Tambah','update'=>'Ubah','delete'=>'Hapus','approve'=>'Setujui','reject'=>'Tolak','print'=>'Cetak'] as $key=>$label)
            <label class="action-pill">
                <input type="checkbox"
                       class="perm-action"
                       name="permissions[{{ $child->menu_id }}][{{ $key }}]"
                       {{ $perm && $perm->{'can_'.$key} ? 'checked' : '' }}>
                <span>{{ $label }}</span>
            </label>
            @endforeach
        </div>
    </td>
</tr>
@endforeach
@endforeach

</tbody>
</table>
</div>

{{-- ================= APPS ================= --}}
<div class="card-header border-top">
    <h6 class="fw-bold text-success mb-0">Hak Akses Apps</h6>
</div>

<div class="card-body">
<div class="apps-grid">
@foreach($apps as $app)
@php $appActive = in_array($app->app_code, $roleApps); @endphp
    <div class="app-item {{ $appActive ? 'is-active' : 'is-inactive' }}">
        <div>
            <div class="fw-semibold">{{ $app->name }}</div>
            <div class="text-muted small">{{ $app->app_code }}</div>
        </div>
        <label class="switch">
            <input type="checkbox"
                   class="app-toggle"
                   name="apps[]"
                   value="{{ $app->app_code }}"
                   {{ $appActive ? 'checked' : '' }}>
            <span class="slider"></span>
        </label>
    </div>
@endforeach
</div>
</div>

</div>
</div>
</div>
</form>

{{-- ================= STYLE ================= --}}
<style>
.permission-table th { background:#f8fafc; font-size:13px; font-weight:600; }
.permission-table td { padding:12px; vertical-align:middle; }

.menu-master { background:#eef2f7; border-top:2px solid #e5e7eb; }

.is-inactive { background:#f3f4f6 !important; color:#9ca3af; }
.is-active { background:#ffffff; }

.action-wrap { display:flex; flex-wrap:wrap; gap:8px; }
.action-pill { padding:6px 12px; border-radius:999px; background:#eef2f7; font-size:12px; }

.apps-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.app-item {
    display:flex; justify-content:space-between; align-items:center;
    padding:12px; border:1px solid #e5e7eb; border-radius:8px;
}
.app-item.is-inactive { background:#f3f4f6; }
.app-item.is-active { background:#fff; border-color:#c7d2fe; }

.switch { position:relative; width:42px; height:22px; }
.switch input { display:none }
.slider { position:absolute; inset:0; background:#cbd5e1; border-radius:999px; }
.slider:before { content:""; position:absolute; width:16px; height:16px; left:3px; bottom:3px; background:white; border-radius:50%; transition:.3s; }
.switch input:checked + .slider { background:#2563eb; }
.switch input:checked + .slider:before { transform:translateX(20px); }
</style>

{{-- ================= SCRIPT ================= --}}
<script>
function setRowState(row, active) {
    row.toggleClass('is-active', active);
    row.toggleClass('is-inactive', !active);
}


/* ===== PARENT TOGGLE ===== */
// $('.parent-toggle').on('change', function () {
//     let parent = $(this).data('parent');
//     let parentRow = $(`.menu-master[data-parent="${parent}"]`);
//     let children = $(`.menu-child[data-parent="${parent}"]`);

//     if (this.checked) {
//         setRowState(parentRow, true);
//         children.removeClass('d-none');
//     } else {
//         setRowState(parentRow, false);
//         children.addClass('d-none')
//             .removeClass('is-active')
//             .addClass('is-inactive')
//             .find('input').prop('checked', false);

//         children.find('.action-wrap').addClass('d-none');
//     }
// });
$('.parent-toggle').on('change', function () {
    let parent = $(this).data('parent');
    let row = $(this).closest('tr');
    let children = $(`.menu-child[data-parent="${parent}"]`);

    if (this.checked) {
        row.removeClass('is-inactive').addClass('is-active');
        children.removeClass('d-none');
    } else {
        row.removeClass('is-active').addClass('is-inactive');
        children.addClass('d-none')
            .find('input').prop('checked', false);
        children.find('.action-wrap').addClass('d-none');
    }
});

/* ===== CHILD VIEW ===== */
$('.perm-view').on('change', function () {
    let menu = $(this).data('menu');
    let row = $(`.menu-child[data-menu="${menu}"]`);
    let box = $('#action-' + menu);

    if (this.checked) {
        setRowState(row, true);
        box.removeClass('d-none');
    } else {
        setRowState(row, false);
        box.addClass('d-none').find('input').prop('checked', false);
    }
});

/* ===== APPS TOGGLE ===== */
$('.app-toggle').on('change', function(){
    $(this).closest('.app-item')
        .toggleClass('is-active', this.checked)
        .toggleClass('is-inactive', !this.checked);
});

/* ===== AJAX UPDATE ===== */
$('#roleEditForm').on('submit', function(e){
    e.preventDefault();

    $.ajax({
        url: "{{ route('roles.update',$role->id) }}",
        type: "POST",
        data: $(this).serialize(),
        success: function(res){
            Swal.fire('Berhasil', res.message, 'success')
                .then(()=> window.location.href = "{{ route('roles.index') }}");
        },
        error: function(xhr){
            Swal.fire('Gagal', xhr.responseJSON?.message || 'Error', 'error');
        }
    });
});
</script>
@endsection
