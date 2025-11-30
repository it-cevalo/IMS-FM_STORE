@extends('layouts.sign-in')

@section('content')

<div class="col-lg-6" style="background-color:#f5f6f7;">
    <div class="p-5">
        <div class="text-center">
                <img src="assets/img/logo_customer.png" width="100px" height="100px"/>
            <h1 class="h4 text-gray-900 mb-4">SIGN IN</h1>
        </div>

        @if(\Session::has('fail'))
            <div class="alert alert-danger">
                <span>{{\Session::get('fail')}}</span>
            </div>
        @endif
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group row">
                <label for="username" class="col-md-4 text-gray-900 mb-4 text-md-right">{{ __('Username') }}</label>

                <div class="col-md-6">
                    <input id="username" type="username" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" required autocomplete="username" autofocus>

                    @error('username')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="password" class="col-md-4 text-gray-900 mb-4 text-md-right">{{ __('Password') }}</label>

                <div class="col-md-6">
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-6 offset-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                        <label class="text-gray-900 mb-4" for="remember">
                            {{ __('Remember Me') }}
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group row mb-0">
                <div class="col-md-8 offset-md-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-fw fa-sign-in-alt"></i>
                        {{ __('SIGN IN') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
