@extends('layouts.app')

@section('page-title')
    {{ ucwords($harvest->action . ' ' . $harvest->resource) }} | TEvo Harvester
@endsection
@section('page-header')
    {{ ucwords($harvest->action . ' ' . $harvest->resource) }}
    <small>Settings</small>
@endsection

@section('content')
    <form method="POST" action="{{ action('ResourceController@store', ['resource' => $harvest->resource, 'action' => $harvest->action]) }}" accept-charset="UTF-8" class="form-horizontal">
        {{ csrf_field() }}
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="scheduler_frequency_method">Schedule Frequency</label>
            <div class="col-sm-5">
                <select id="scheduler_frequency_method" name="scheduler_frequency_method" class="form-control">
                    {!! getSelectListOptions([
                        'everyFiveMinutes' => 'everyFiveMinutes',
                        'everyTenMinutes' => 'everyTenMinutes',
                        'everyThirtyMinutes' => 'everyThirtyMinutes',
                        'hourly' => 'hourly',
                        'daily' => 'daily',
                        'twiceDaily' => 'twiceDaily(1, 13)',
                        'weekly' => 'weekly',
                        'monthly' => 'monthly',
                    ], $harvest->scheduler_frequency_method) !!}
                </select>
                <span id="scheduler_frequency_method_help" class="help-block">See the <a href="http://laravel.com/docs/5.7/scheduling#schedule-frequency-options">Laravel Schedule Frequency Options</a> for further explanation.</span>
            </div>
        </div>

        <div class="row form-group">
            <label class="col-sm-2 col-form-label" for="ping_before_url">Ping Before URL</label>
            <div class="col-sm-5">
                <input class="form-control" id="ping_before_url" name="ping_before_url" type="url" value="{{ $harvest->ping_before_url }}">
                <span id="ping_before_url_help" class="help-block">A URL to ping before the update starts. See <a href="http://laravel.com/docs/5.7/scheduling#task-hooks">Laravel Scheduler Pinging URLs</a> for further explanation.</span>
            </div>
        </div>

        <div class="row form-group">
            <label class="col-sm-2 col-form-label" for="then_ping_url">Then Ping URL</label>
            <div class="col-sm-5">
                <input class="form-control" id="then_ping_url" name="then_ping_url" type="url" value="{{ $harvest->then_ping_url }}">
                <span id="then_ping_url_help" class="help-block">A URL to ping after the update completes. See <a href="http://laravel.com/docs/5.7/scheduling#task-hooks">Laravel Scheduler Pinging URLs</a> for further explanation.</span>
            </div>
        </div>

        <div class="row form-group">
            <div class="col-sm-5 offset-sm-2">
                <button type="submit" class="btn btn-primary">
                    @svg('save-disk', 'icon-xs zondicon-light')
                    &nbsp;Save&nbsp;Settings
                </button>
            </div>
        </div>

    </form>
@endsection
