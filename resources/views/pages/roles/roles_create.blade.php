@extends('layouts.admin')

@section('content')  
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Role Management</h6>
    </div>
    <div class="card-body">
        <form action="{{route('roles.store')}}" method="POST">
            @csrf 
            <div class="mb-3">
                <label for="exampleFormControlInput1">Name</label>
                <input class="form-control" id="exampleFormControlInput1" name="name" type="text" placeholder="Roles Name">
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