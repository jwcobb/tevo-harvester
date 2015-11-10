@extends('layouts.app')

@section('page-title', 'Login | TEvo Harvester for Ticket Evolution API')
@section('content')
    @include('common.messages')

    {!! Form::open(['action' => 'Auth\AuthController@postRegister', 'class' => 'form-horizontal']) !!}

    <div class="row">
        <div class="form-group">
            {!! Form::label('name', 'Your Name', array('class' => 'col-sm-2 control-label')) !!}
            <div class="col-sm-4">
                {!! Form::email('name', old('name'), ['class' => 'form-control', 'id' => 'name']) !!}
            </div>
        </div>
    </div>

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
            {!! Form::label('password_confirmation', 'Password Confirmation', array('class' => 'col-sm-2 control-label')) !!}
            <div class="col-sm-4">
                {!! Form::password('password_confirmation', ['class' => 'form-control', 'id' => 'password_confirmation']) !!}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group">
            <div class="col-sm-4 col-md-offset-2">
                <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-user"></span>&nbsp;Register</button>
            </div>
        </div>
    </div>

    {!! Form::close() !!}
@endsection
