@extends('layouts.admin')

@section('content')                    
                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Role Management</h6>
                        </div>
                        <div class="card-header py-3">
                            <div class="input-group">
                                {{-- <form method="" action="">
                                    <label>
                                        <input type="search" class="form-control form-control-sm" placeholder="Search" aria-controls="dataTable">
                                    </label>
                                    <button class="btn btn-primary btn-sm" type="button">
                                        <i class="fas fa-search fa-sm"></i>
                                    </button>
                                </form>
                            </div> --}}
                            {{-- <a href="{{route('roles.create')}}" class="btn btn-primary btn-flat btn-sm"><i class="fa fa-plus"></i> Add</a> --}}
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Name</th>
                                            <th>Guard Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no=1;
                                        ?>
                                        @foreach($roles as $f)
                                        <tr>
                                            <td>{{$no++}}</td>
                                            <td>{{$f->name}}</td>
                                            <td>{{$f->guard_name}}</td>
                                            {{-- <td>
                                                <!-- <a href="{{route('roles.edit',$f->id)}}" class="btn btn-link text-warning btn-sm">Edit</a>  -->
                                                {{-- <form action="{{route('roles.destroy',$f->id)}}" method="POST" class="formDelete">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link text-danger">Delete</button>
                                                </form>
                                            </td> --}}
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endsection