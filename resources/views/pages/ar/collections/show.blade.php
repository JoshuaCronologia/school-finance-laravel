@extends('layouts.app')
@section('title', 'Collection Details')

@section('content')
<x-page-header :title="$collection->receipt_number ?? 'Collection'" subtitle="Details view not yet implemented" />

<div class="card">
    <div class="card-body">
        <p class="text-sm text-secondary-600">Collection display is not implemented yet. Collection ID: {{ $collection->id }}</p>
    </div>
</div>
@endsection
