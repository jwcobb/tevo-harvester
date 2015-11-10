@extends('layouts.app')

@section('page-title', ucwords($harvest->action . ' ' . $harvest->resource) . ' | TEvo Harvester')
@section('page-header', ucwords($harvest->action . ' ' . $harvest->resource) . ' <small>Settings</small>')

@section('content')
    {!! Form::model($harvest, ['method' => 'post', 'class' => 'form-horizontal']) !!}
    <div class="row">
        <div class="form-group">
            {!! Form::label('scheduler_frequency_method', 'Schedule Frequency', array('class' => 'col-sm-2 control-label')) !!}
            <div class="col-sm-4">
                {!! Form::select('scheduler_frequency_method', [
                        'everyFiveMinutes' => 'everyFiveMinutes',
                        'everyTenMinutes' => 'everyTenMinutes',
                        'everyThirtyMinutes' => 'everyThirtyMinutes',
                        'hourly' => 'hourly',
                        'daily' => 'daily',
                        'twiceDaily' => 'twiceDaily(1, 13)',
                        'weekly' => 'weekly',
                        'monthly' => 'monthly',
                    ], $harvest->scheduler_frequency_method, ['class' => 'form-control', 'id' => 'scheduler_frequency_method']) !!}
                <span id="scheduler_frequency_method_help" class="help-block">See the <a
                            href="http://laravel.com/docs/master/scheduling#schedule-frequency-options">Laravel Schedule
                        Frequency Options</a> for further explanation.</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group">
            {!! Form::label('ping_before_url', 'Ping Before URL', array('class' => 'col-sm-2 control-label')) !!}
            <div class="col-sm-4">
                {!! Form::url('ping_before_url', $harvest->ping_before_url, ['class' => 'form-control', 'id' => 'ping_before_url']) !!}
                <span id="ping_before_url_help" class="help-block">A URL to ping before the update starts. See <a
                            href="http://laravel.com/docs/5.1/scheduling#task-hooks">Laravel Scheduler Pinging URLs</a> for further explanation.</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group">
            {!! Form::label('then_ping_url', 'Then Ping URL', array('class' => 'col-sm-2 control-label')) !!}
            <div class="col-sm-4">
                {!! Form::url('then_ping_url', $harvest->then_ping_url, ['class' => 'form-control', 'id' => 'then_ping_url']) !!}
                <span id="then_ping_url_help" class="help-block">A URL to ping after the update completes. See <a
                            href="http://laravel.com/docs/5.1/scheduling#task-hooks">Laravel Scheduler Pinging URLs</a> for further explanation.</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group">
            <div class="col-sm-4 col-md-offset-2">
                <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-save"></span>&nbsp;Save
                    Settings
                </button>
            </div>
        </div>
    </div>

    {!! Form::close() !!}
@endsection
