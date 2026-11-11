<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ setting('shop_name', config('app.name')) }} - Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-100 p-4">
    <div class="w-full max-w-md">
        <div class="mb-8 text-center">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-600 text-xl font-bold text-white">SC</div>
            <h1 class="text-2xl font-bold text-slate-900">{{ setting('shop_name', config('app.name')) }}</h1>
            <p class="text-sm text-slate-500">Sign in to the service center manager</p>
        </div>
        <div class="card p-8">
            @yield('content')
        </div>
        <p class="mt-6 text-center text-xs text-slate-400">Service Center Management System</p>
    </div>
</body>
</html>
