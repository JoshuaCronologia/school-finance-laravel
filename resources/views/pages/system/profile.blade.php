@extends('layouts.app')
@section('title', 'My Profile')

@section('content')
<x-page-header title="My Profile" subtitle="Manage your account information" />

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Profile Card --}}
    <div class="card">
        <div class="card-body text-center py-8">
            <div class="w-20 h-20 bg-primary-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="text-2xl font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', auth()->user()->name)[1] ?? '', 0, 1)) }}</span>
            </div>
            <h3 class="text-lg font-semibold text-secondary-900">{{ auth()->user()->name }}</h3>
            <p class="text-sm text-secondary-500">{{ auth()->user()->email }}</p>
            <span class="inline-block mt-2 badge badge-info">{{ ucwords(str_replace('_', ' ', auth()->user()->roles->first()->name ?? 'User')) }}</span>
            <div class="mt-4 pt-4 border-t border-gray-100 text-sm text-secondary-500">
                <p>Member since {{ auth()->user()->created_at?->format('F d, Y') }}</p>
            </div>
        </div>
    </div>

    {{-- Edit Profile --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Update Info --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Account Information</h3></div>
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-input" value="{{ auth()->user()->name }}" required>
                            @error('name') <p class="text-xs text-danger-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-input" value="{{ auth()->user()->email }}" required>
                            @error('email') <p class="text-xs text-danger-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex justify-end mt-4">
                        <button type="submit" class="btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Change Password --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Change Password</h3></div>
            <div class="card-body">
                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-input" required>
                            @error('current_password') <p class="text-xs text-danger-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-input" required minlength="8">
                            @error('password') <p class="text-xs text-danger-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-input" required minlength="8">
                        </div>
                    </div>
                    <div class="flex justify-end mt-4">
                        <button type="submit" class="btn-primary">Change Password</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- My Permissions --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">My Access Permissions</h3></div>
            <div class="card-body">
                <div class="flex flex-wrap gap-1.5">
                    @foreach(auth()->user()->getAllPermissions()->sortBy('name') as $perm)
                    <span class="inline-block px-2.5 py-1 bg-primary-50 text-primary-700 text-xs font-medium rounded-full">{{ $perm->name }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
