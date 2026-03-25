@extends('layouts.app')
@section('title', 'Invoice Details')

@section('content')
<x-page-header :title="$invoice->invoice_number ?? 'Invoice'" subtitle="Details view not yet implemented" />

<div class="card">
    <div class="card-body">
        <p class="text-sm text-secondary-600">Invoice display is not implemented yet. Invoice ID: {{ $invoice->id }}</p>
    </div>
</div>
@endsection
