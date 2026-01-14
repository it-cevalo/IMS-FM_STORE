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
            <div class="mb-3">
                <label for="exampleFormControlInput1">Nama</label> :
                {{$user->name}}            
            </div>
            <div class="mb-3">
                <label for="exampleFormControlInput1">Email address</label> :
                {{$user->email}}
            </div>
            <div class="mb-3">
                <label for="exampleFormControlInput1">Username</label> :
                {{$user->username}}
            </div>
    </div>
</div>
@endsection