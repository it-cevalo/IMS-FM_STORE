@extends('layouts.admin')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-lock mr-1"></i> Ganti Password
                </h6>
            </div>
            <div class="card-body">
                <form id="changePasswordForm">
                    @csrf
                    <div class="mb-3">
                        <label>Password Lama <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="current_password" id="current_password" placeholder="Masukkan password lama">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Password Baru <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="new_password" id="new_password" placeholder="Minimal 8 karakter">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        {{-- Syarat password --}}
                        <div class="mt-2 px-2 py-2 rounded" style="background:#f8f9fc;border:1px solid #e3e6f0;font-size:.8rem;">
                            <p class="mb-1 font-weight-bold text-muted">Password harus memenuhi semua syarat:</p>
                            <ul class="mb-0 pl-3" id="pwdChecklist" style="line-height:1.8;">
                                <li id="chk-len"  class="text-danger"><i class="fas fa-times-circle mr-1"></i> Minimal 8 karakter</li>
                                <li id="chk-upper" class="text-danger"><i class="fas fa-times-circle mr-1"></i> Ada huruf kapital (A–Z)</li>
                                <li id="chk-lower" class="text-danger"><i class="fas fa-times-circle mr-1"></i> Ada huruf kecil (a–z)</li>
                                <li id="chk-num"   class="text-danger"><i class="fas fa-times-circle mr-1"></i> Ada angka (0–9)</li>
                                <li id="chk-sym"   class="text-danger"><i class="fas fa-times-circle mr-1"></i> Ada simbol (contoh: @$!#%)</li>
                            </ul>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label>Konfirmasi Password Baru <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="new_password_confirmation" id="new_password_confirmation" placeholder="Ulangi password baru">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password_confirmation">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan Password
                    </button>
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary ml-2">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Real-time password checklist
    document.getElementById('new_password').addEventListener('input', function () {
        var v = this.value;
        function mark(id, ok) {
            var el = document.getElementById(id);
            el.className = ok ? 'text-success' : 'text-danger';
            el.innerHTML = (ok
                ? '<i class="fas fa-check-circle mr-1"></i>'
                : '<i class="fas fa-times-circle mr-1"></i>'
            ) + el.textContent.trim().replace(/^[^\s]+\s/, '');
        }
        mark('chk-len',   v.length >= 8);
        mark('chk-upper', /[A-Z]/.test(v));
        mark('chk-lower', /[a-z]/.test(v));
        mark('chk-num',   /[0-9]/.test(v));
        mark('chk-sym',   /[@$!%*#?&^()\-_+=\[\]{};:'",.<>\/\\|~]/.test(v));
    });

    // Toggle show/hide password
    document.querySelectorAll('.toggle-password').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var input = document.getElementById(this.dataset.target);
            var icon  = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

    $('#changePasswordForm').submit(function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Menyimpan...',
            text: 'Mohon tunggu',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: "{{ route('profile.update-password') }}",
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.message
                }).then(() => {
                    $('#changePasswordForm')[0].reset();
                });
            },
            error: function(xhr) {
                var msg = 'Terjadi kesalahan.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            }
        });
    });
</script>
@endsection
