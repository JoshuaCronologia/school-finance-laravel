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

@php
    $isSSO = session('is_sso');
    $userName = $isSSO ? session('user_info.name', 'User') : auth()->user()->name;
    $userEmail = $isSSO ? session('user_info.email', '') : auth()->user()->email;
    $userRole = $isSSO
        ? class_basename(session('user_info.user_type', 'User'))
        : (auth()->user()->roles->first()->name ?? 'User');
    $userPlatform = session('platform', null);
    $userBranch = session('branch_code', null);
    $userPermissions = $isSSO
        ? session('permissions', [])
        : auth()->user()->getAllPermissions()->sortBy('name')->pluck('name')->toArray();
    $initials = strtoupper(substr($userName, 0, 1)) . strtoupper(substr(explode(' ', $userName)[1] ?? '', 0, 1));
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Profile Card --}}
    <div class="card">
        <div class="card-body text-center py-8">
            <div class="w-20 h-20 bg-primary-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="text-2xl font-bold text-white">{{ $initials }}</span>
            </div>
            <h3 class="text-lg font-semibold text-secondary-900">{{ $userName }}</h3>
            <p class="text-sm text-secondary-500">{{ $userEmail }}</p>
            <span class="inline-block mt-2 badge badge-info">{{ ucwords($userRole) }}</span>
            @if($userPlatform)
                <span class="inline-block mt-1 badge badge-secondary">{{ $userPlatform }}</span>
            @endif
            @if($userBranch)
                <p class="mt-2 text-xs text-secondary-400">Branch: {{ strtoupper($userBranch) }}</p>
            @endif
            @if(!$isSSO)
            <div class="mt-4 pt-4 border-t border-gray-100 text-sm text-secondary-500">
                <p>Member since {{ (auth()->user()->created_at ? auth()->user()->created_at->format('F d, Y') : '-') }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Edit Profile / Info --}}
    <div class="lg:col-span-2 space-y-6">
        @if(!$isSSO)
        {{-- Admin: Update Info --}}
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

        {{-- Admin: Change Password --}}
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
        @else
        {{-- SSO: Account Info (read-only) --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Account Information</h3></div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Full Name</label>
                        <p class="form-input bg-gray-50">{{ $userName }}</p>
                    </div>
                    <div>
                        <label class="form-label">Email Address</label>
                        <p class="form-input bg-gray-50">{{ $userEmail ?: '-' }}</p>
                    </div>
                    <div>
                        <label class="form-label">User Type</label>
                        <p class="form-input bg-gray-50">{{ ucwords($userRole) }}</p>
                    </div>
                    <div>
                        <label class="form-label">Platform</label>
                        <p class="form-input bg-gray-50">{{ $userPlatform ?? '-' }}</p>
                    </div>
                </div>
                <p class="text-xs text-secondary-400 mt-3">Account information is managed by your school's SIS. Contact your administrator for changes.</p>
            </div>
        </div>
        @endif

        {{-- My Permissions --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">My Access Permissions</h3></div>
            <div class="card-body">
                <div class="flex flex-wrap gap-1.5">
                    @forelse($userPermissions as $perm)
                    <span class="inline-block px-2.5 py-1 bg-primary-50 text-primary-700 text-xs font-medium rounded-full">{{ is_string($perm) ? $perm : $perm->name }}</span>
                    @empty
                    <p class="text-sm text-secondary-400">No permissions assigned.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
