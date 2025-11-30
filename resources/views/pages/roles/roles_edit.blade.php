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
        <h6 class="m-0 font-weight-bold text-primary">Role Management</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{route('roles.update',$role->id)}}">
            @csrf
            {{ method_field('PUT') }}
            <div class="mb-3">
                <label for="exampleFormControlInput1">Name</label>
                <input class="form-control" id="exampleFormControlInput1" name="name" type="text" value="{{$role->name}}" placeholder="Roles Name">
            </div>
            {{-- <div class="mb-3">
                <label for="exampleFormControlInput1">Permission</label><br/>
                @foreach($permission as $value)
                    <label>{{ Form::checkbox('permission[]', $value->id, false, array('class' => 'name')) }}
                    {{ $value->name }}</label>
                <br/>
                @endforeach
            </div> --}}
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
</div>
@endsection