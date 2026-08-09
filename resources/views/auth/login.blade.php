@extends('layouts.auth')

@section('content')
    <h2 class="mb-6 text-lg font-semibold text-slate-800">Sign in to your account</h2>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="input">
        </div>
        <div>
            <label for="password" class="label">Password</label>
            <input id="password" type="password" name="password" required class="input">
        </div>
        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                Remember me
            </label>
        </div>
        <button type="submit" class="btn-primary w-full justify-center">Sign in</button>
    </form>
@endsection
