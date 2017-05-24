@extends('layouts.app')

@section('page-title')
    {{ ucwords($harvest->action . ' ' . $harvest->resource) }} | TEvo Harvester
@endsection
@section('page-header')
    {{ ucwords($harvest->action . ' ' . $harvest->resource) }}
@endsection

@section('content')
    @include('partials.resource-details')
@endsection