@extends('layouts.admin')

@section('content')

<div class="card shadow mb-4">

    {{-- HEADER --}}
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Role Management</h6>
        <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Role
        </a>
    </div>

    {{-- BODY --}}
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%">
                <thead class="table-light">
                    <tr>
                        <th width="60">No</th>
                        <th>Nama Role</th>
                        <th>Guard</th>
                        <th width="140" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>

                    @forelse($roles as $index => $role)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $role->name }}</strong>
                        </td>
                        <td>{{ $role->guard_name }}</td>
                        <td class="text-center">

                            <a href="{{ route('roles.edit', $role->id) }}"
                               class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>

                            {{-- DELETE (optional, comment jika belum dipakai) --}}
                            {{--
                            <form action="{{ route('roles.destroy',$role->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Hapus role ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            --}}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            Belum ada data role
                        </td>
                    </tr>
                    @endforelse

                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
