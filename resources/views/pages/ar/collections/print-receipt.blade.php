@extends('layouts.app')
@section('title', 'Print Receipt')

@section('content')
<x-page-header title="Print Receipt" subtitle="Print view not yet implemented" />

<div class="card">
    <div class="card-body">
        <p class="text-sm text-secondary-600">Printable receipt template is not yet implemented. Collection ID: {{ $collection->id }}</p>
    </div>
</div>
@endsection
