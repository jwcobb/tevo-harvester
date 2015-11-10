@extends('layouts.app')

@section('page-title', ucwords($harvest->action . ' ' . $harvest->resource) . ' | TEvo Harvester')
@section('page-header', ucwords($harvest->action . ' ' . $harvest->resource))

@section('content')
    @include('partials.resource-details')
@endsection