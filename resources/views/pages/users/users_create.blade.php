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
        <h6 class="m-0 font-weight-bold text-primary">User Management</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{route('users.store')}}">
            @csrf
            <div class="mb-3">
                <label for="exampleFormControlInput1">Name</label>
                <input class="form-control" id="exampleFormControlInput1" name="name" type="text"
                    placeholder="Jhon Doe">
            </div>
            <div class="mb-3">
                <label for="exampleFormControlInput1">Username</label>
                <input class="form-control" id="exampleFormControlInput1" name="username" type="text"
                    placeholder="Jhon Doe">
            </div>
            <div class="mb-3">
                <label for="exampleFormControlInput1">Password</label>
                <input class="form-control" id="myInput" name="password" type="password">
                <!-- <input type="checkbox" onclick="myFunction()">Show Password -->
            </div>
            <div class="mb-3">
                <label for="exampleFormControlInput1">Email address</label>
                <input class="form-control" id="exampleFormControlInput1" name="email" type="email"
                    placeholder="name@example.com">
            </div>
            <div class="mb-3">
                <label for="exampleFormControlInput1">Position</label>
                <select class="form-control" name="position" value="{{old('position')}}" required>
                    <option value="">....</option>
                    <option value="OPERATIONAL">Operational</option>
                    <option value="WAREHOUSE_ADMIN">Warehouse Admin</option>
                    <option value="PURCHASING">Purchasing</option>
                    <!-- <option value="INVOICINGTAX">Invoicing & Tax</option> -->
                    <!-- <option value="STAFF_FINANCE">Staff Finance</option> -->
                    <option value="MANAGER">Manager</option>
                    <!-- <option value="COURIER">Courier</option>
                    <option value="DIRECTOR">Director</option> -->
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
</div>
@endsection