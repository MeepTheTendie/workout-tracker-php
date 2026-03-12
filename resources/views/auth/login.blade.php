@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="page-header">
    <div class="page-title">Login</div>
</div>

<div class="section">
    <form method="POST" action="{{ route('login') }}">
        @csrf
        
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-input" value="{{ old('email') }}" required autofocus>
        </div>
        
        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-input" required>
        </div>
        
        <button type="submit" class="btn btn-full">Login</button>
        
        @if ($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @endif
    </form>
</div>
@endsection
