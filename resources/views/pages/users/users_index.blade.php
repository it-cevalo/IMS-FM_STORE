@extends('layouts.admin')

@section('content')                    
                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Pengelolaan Pengguna</h6>
                        </div>
                        <div class="card-header py-3">
                            {{-- <div class="input-group">
                                <form method="" action="">
                                    <label>
                                        <input type="search" class="form-control form-control-sm" placeholder="Search" aria-controls="dataTable">
                                    </label>
                                    <button class="btn btn-primary btn-sm" type="button">
                                        <i class="fas fa-search fa-sm"></i>
                                    </button>
                                </form>
                            </div> --}}
                            <a href="{{route('users.create')}}" class="btn btn-primary btn-flat btn-sm"><i class="fa fa-plus"></i> Tambah</a>

                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Username</th>
                                            <th>Nama</th>
                                            <th>Role</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no=1;
                                        ?>
                                        @foreach($data as $f)
                                        <tr>
                                            <td>{{$no++}}</td>
                                            <td>{{$f->username}}</td>
                                            <td>{{$f->name}}</td>
                                            <td>{{ $f->role->name ?? '-' }}</td>
                                            <td>
                                                <form action="{{route('users.destroy',$f->id)}}" method="POST" class="formDelete d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <a href="{{route('users.edit',$f->id)}}" class="btn btn-warning btn-flat btn-sm"><i class="fa fa-edit"></i></a>
                                                    <button type="submit" class="btn btn-flat btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                                                </form>
                                                @if(Auth::check() && strtolower(Auth::user()->role->name ?? '') === 'admin')
                                                <button type="button"
                                                    class="btn btn-secondary btn-flat btn-sm btn-reset-pwd"
                                                    data-id="{{ $f->id }}"
                                                    data-username="{{ $f->username }}"
                                                    title="Reset password ke default">
                                                    <i class="fa fa-key"></i>
                                                </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <script>
                    $(document).on('click', '.btn-reset-pwd', function () {
                        var id       = $(this).data('id');
                        var username = $(this).data('username');

                        Swal.fire({
                            title: 'Reset Password?',
                            html: 'Password <strong>' + username + '</strong> akan direset ke default.<br><small class="text-muted">Password default: <code>C3v4l0123!</code></small>',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#e74a3b',
                            cancelButtonColor: '#858796',
                            confirmButtonText: 'Ya, Reset',
                            cancelButtonText: 'Batal',
                        }).then(function (result) {
                            if (!result.isConfirmed) return;

                            Swal.fire({
                                title: 'Mereset...',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });

                            $.ajax({
                                url: '/users/' + id + '/reset-password',
                                method: 'POST',
                                data: { _token: '{{ csrf_token() }}' },
                                success: function (res) {
                                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message });
                                },
                                error: function (xhr) {
                                    var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan.';
                                    Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                                }
                            });
                        });
                    });
                    </script>
                    @endsection