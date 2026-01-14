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
                                                <form action="{{route('users.destroy',$f->id)}}" method="POST" class="formDelete">
                                                    @csrf
                                                    @method('DELETE')
                                                    <a href="{{route('users.edit',$f->id)}}" class="btn btn-warning btn-flat btn-sm"><i class="fa fa-edit"></i></a> 
                                                    <button type="submit" class="btn btn-flat btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endsection