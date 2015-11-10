<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('page-title', 'TEvo Harvester for the Ticket Evolution API')</title>
    <link rel="stylesheet" href="/css/app.css">
    @yield('styles')
</head>

<body>
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                    data-target="#harvest-navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a href="{{ route('dashboard') }}" class="navbar-brand">TEvo Harvester</a>
        </div>

        @if (Auth::user())
                <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="harvest-navbar-collapse">
            <ul class="nav navbar-nav">
                <li><a href="{{ route('dashboard') }}">Dashboard <span
                                class="sr-only">(current)</span></a></li>
            </ul>

            <ul class="nav navbar-nav navbar-right">
                <li><a href="{{ action('Auth\AuthController@getLogout') }}"
                       class="navbar-right navbar-link">Logout {{ Auth::user()->name }}</a>&nbsp;&nbsp;&nbsp;</li>
            </ul>
        </div><!-- /.navbar-collapse -->
        @endif
    </div><!-- /.container-fluid -->
</nav>

<div class="container main">
    <div class="page-header">
        <h1>@yield('page-header', 'TEvo Harvester <small>for the Ticket Evolution API</small>')</h1>
    </div>
</div>

<div class="container main">
    @yield('content')
</div>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
@yield('scripts')
</body>
</html>