@extends('layouts.app')

@section('page-title')
    {{ ucwords($harvests[0]['resource']) }} | TEvo Harvester'
@endsection
@section('page-header')
    {{ ucwords($harvests[0]['resource']) }} <small>Actions Available</small>
@endsection

@section('content')
        @foreach($harvests as $harvest)
            <h2>{{ ucwords($harvest['action'] . ' ' . $harvest['resource']) }}</h2>
            @include('partials.resource-details')
        @endforeach
@endsection