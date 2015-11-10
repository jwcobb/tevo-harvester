@extends('layouts.app')

@section('page-title', 'Login | TEvo Harvester for Ticket Evolution API')
@section('content')
    @include('common.messages')

    {!! Form::open(['action' => 'Auth\AuthController@postLogin', 'class' => 'form-horizontal']) !!}

    <div class="row">
        <div class="form-group">
            {!! Form::label('email', 'Email', array('class' => 'col-sm-2 control-label')) !!}
            <div class="col-sm-4">
                {!! Form::email('email', old('email'), ['class' => 'form-control', 'id' => 'email']) !!}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group">
            {!! Form::label('password', 'Password', array('class' => 'col-sm-2 control-label')) !!}
            <div class="col-sm-4">
                {!! Form::password('password', ['class' => 'form-control', 'id' => 'password']) !!}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group">
            <div class="col-sm-4 col-md-offset-2">
                <div class="checkbox">
                    <label>
                        <input checked="checked" name="remember" id="remember" type="checkbox"> Remember Me
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group">
            <div class="col-sm-4 col-md-offset-2">
                <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-user"></span>&nbsp;Login</button>
            </div>
        </div>
    </div>

    {!! Form::close() !!}
@endsection