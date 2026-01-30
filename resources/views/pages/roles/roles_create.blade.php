@extends('layouts.admin')

@section('content')
<form id="roleCreateForm">
@csrf

<div class="row">

{{-- ================= LEFT ================= --}}
<div class="col-md-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <label class="fw-semibold">Nama Role</label>
            <input name="name" class="form-control mb-3" required>

            <div class="text-muted small">
                • Beranda selalu aktif dan tidak bisa dinonaktifkan<br>
                • Aktifkan menu utama untuk menampilkan sub-menu<br>
                • Aktifkan <b>Lihat</b> untuk membuka hak aksi<br>
                • <b>Full Akses</b> = semua hak ON (klik lagi untuk reset)
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Simpan</button>
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
@php $isBeranda = $parent->menu_id === 'MENU-0001'; @endphp

<tr class="menu-master {{ $isBeranda ? 'is-active' : 'is-inactive' }}"
    data-parent="{{ $parent->menu_id }}">

    <td class="fw-bold text-uppercase">{{ $parent->name }}</td>
    <td class="text-center">

        <input type="hidden"
               name="permissions[{{ $parent->menu_id }}][enabled]"
               value="{{ $isBeranda ? 1 : 0 }}"
               class="parent-enabled">

        <label class="switch">
            <input type="checkbox"
                   class="parent-toggle"
                   data-parent="{{ $parent->menu_id }}"
                   {{ $isBeranda ? 'checked disabled' : '' }}>
            <span class="slider"></span>
        </label>

        @if($isBeranda)
            <input type="hidden"
                   name="permissions[{{ $parent->menu_id }}][view]"
                   value="1">
        @endif
    </td>

    <td class="text-muted small">
        {{ $isBeranda ? 'Menu wajib (selalu aktif)' : 'Mengaktifkan seluruh sub menu' }}
    </td>
</tr>

@foreach($menus[$parent->menu_id] ?? [] as $child)
<tr class="menu-child is-inactive d-none"
    data-parent="{{ $parent->menu_id }}"
    data-menu="{{ $child->menu_id }}">

    <td class="ps-4 fw-medium">{{ $child->name }}</td>

    <td class="text-center">
        <label class="switch">
            <input type="checkbox"
                   class="perm-view"
                   data-menu="{{ $child->menu_id }}"
                   name="permissions[{{ $child->menu_id }}][view]">
            <span class="slider"></span>
        </label>
    </td>

    <td>
        <div class="action-wrap d-none" id="action-{{ $child->menu_id }}">
        @foreach(['create'=>'Tambah','update'=>'Ubah','delete'=>'Hapus','approve'=>'Setujui','reject'=>'Tolak','print'=>'Cetak'] as $k=>$v)
            <label class="action-pill">
                <input type="checkbox"
                       class="perm-action"
                       name="permissions[{{ $child->menu_id }}][{{ $k }}]">
                <span>{{ $v }}</span>
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
<div class="app-item is-inactive">
    <div>
        <div class="fw-semibold">{{ $app->name }}</div>
        <div class="text-muted small">{{ $app->app_code }}</div>
    </div>
    <label class="switch">
        <input type="checkbox"
               class="app-toggle"
               name="apps[]"
               value="{{ $app->app_code }}">
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
.app-item { display:flex; justify-content:space-between; align-items:center; padding:12px; border:1px solid #e5e7eb; border-radius:8px; }
.app-item.is-inactive { background:#f3f4f6; }
.app-item.is-active { background:#fff; border-color:#c7d2fe; }
.switch { position:relative; width:42px; height:22px; }
.switch input { display:none }
.slider { position:absolute; inset:0; background:#cbd5e1; border-radius:999px; }
.slider:before { content:""; position:absolute; width:16px; height:16px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.3s; }
.switch input:checked + .slider { background:#2563eb; }
.switch input:checked + .slider:before { transform:translateX(20px); }
</style>

{{-- ================= SCRIPT ================= --}}
<script>
function setRow(row, active){
    row.toggleClass('is-active', active);
    row.toggleClass('is-inactive', !active);
}

/* ===== PARENT TOGGLE (INI FIX UTAMA) ===== */
$('.parent-toggle').on('change', function(){
    let parent   = $(this).data('parent');
    let parentRow = $(`.menu-master[data-parent="${parent}"]`);
    let children = $(`.menu-child[data-parent="${parent}"]`);
    let hidden   = $(`input[name="permissions[${parent}][enabled]"]`);

    hidden.val(this.checked ? 1 : 0);
    setRow(parentRow, this.checked);

    if(this.checked){
        children.removeClass('d-none');
    }else{
        children.addClass('d-none')
            .removeClass('is-active')
            .addClass('is-inactive')
            .find('input').prop('checked', false);

        children.find('.action-wrap').addClass('d-none');
    }
});

/* ===== CHILD VIEW ===== */
$('.perm-view').on('change', function(){
    let menu = $(this).data('menu');
    let row  = $(`.menu-child[data-menu="${menu}"]`);
    let box  = $('#action-'+menu);

    if(this.checked){
        setRow(row,true);
        box.removeClass('d-none');
    }else{
        setRow(row,false);
        box.addClass('d-none').find('input').prop('checked',false);
    }
});

/* ===== APPS ===== */
$('.app-toggle').on('change', function(){
    $(this).closest('.app-item')
        .toggleClass('is-active', this.checked)
        .toggleClass('is-inactive', !this.checked);
});

/* ===== ACCESS ===== */
let fullAccessOn = false;

$('#btnFullAccess').on('click', function () {

    fullAccessOn = !fullAccessOn;

    if (fullAccessOn) {

        /* =========================
         * 1️⃣ AKTIFKAN SEMUA PARENT
         * ========================= */
        $('.parent-toggle').each(function () {
            if (!this.disabled) {
                $(this).prop('checked', true).trigger('change');
            }
        });

        /* =========================
         * 2️⃣ AKTIFKAN VIEW + BUKA SUB MENU
         * ========================= */
        $('.menu-child').each(function () {
            let row  = $(this);
            let view = row.find('.perm-view');

            row.removeClass('d-none');
            setRow(row, true);

            view.prop('checked', true);
            $('#action-' + row.data('menu')).removeClass('d-none');
        });

        /* =========================
         * 3️⃣ AKTIFKAN SEMUA ACTION
         * ========================= */
        $('.perm-action').each(function () {
            $(this).prop('checked', true);
        });

        /* =========================
         * 4️⃣ AKTIFKAN APPS
         * ========================= */
        $('.app-toggle').each(function () {
            $(this).prop('checked', true).trigger('change');
        });

        $('#btnFullAccess').text('Reset Akses');

    } else {

        /* =========================
         * RESET SEMUA
         * ========================= */
        $('.parent-toggle').each(function () {
            if (!this.disabled) {
                $(this).prop('checked', false).trigger('change');
            }
        });

        $('.perm-view').prop('checked', false).trigger('change');
        $('.perm-action').prop('checked', false);

        $('.menu-child')
            .addClass('d-none')
            .removeClass('is-active')
            .addClass('is-inactive');

        $('.action-wrap').addClass('d-none');

        $('.app-toggle').prop('checked', false).trigger('change');

        $('#btnFullAccess').text('Full Akses');
    }
});

/* ===== AJAX SUBMIT ===== */
$('#roleCreateForm').on('submit', function(e){
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
        success: function(res){
            Swal.fire('Berhasil', res.message ?? 'Role berhasil dibuat', 'success')
                .then(() => window.location.href = "{{ route('roles.index') }}");
        },
        error: function(){
            Swal.fire('Gagal', 'Terjadi kesalahan', 'error');
        }
    });
});
</script>
@endsection
