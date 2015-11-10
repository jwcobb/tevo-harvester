@extends('layouts.app')

@section('content')
    @include('common.messages')

    <p>TEvo Harvester can be used to populate local database tables with a cache of the Ticket Evolution data. If
        you choose to do this then you should be sure to run each of these scripts at least daily.</p>
    <p>To make it simple, you can use the <a href="http://laravel.com/docs/5.1/scheduling">Laravel Scheduler</a> to run these commands automatically. Just be sure to <a href="http://laravel.com/docs/5.1/scheduling#introduction">add the Laravel Scheduler to your <code>crontab</code></a>.</p>

    <h2>Status of Harvests based upon <i>last_run_at</i> date</h2>
    <table class="table table-striped table-bordered table-hover">
        <thead>
        <tr>
            <th>Resource</th>
            <th>Action</th>
            <th>Last Run</th>
            <th>Scheduled</th>
            <th>Ping Before</th>
            <th>Ping After</th>
            <th>Edit</th>
            <th>Run Now</th>
        </tr>
        </thead>
        <tbody>

        @foreach ($harvests as $resource)
            @foreach ($resource as $action)
                <tr>
                    @if ($action == $harvests[$action->resource]->first())
                        <td rowspan="{{ count($harvests[$action->resource]) }}">{{ ucwords($action->resource) }}</td>
                    @endif
                    <td>{{ ucwords($action->action) }}</td>
                    @if ($action->last_run_at != null)
                        <td>@lastrundiff($action->last_run_at)</td>
                    @else
                        <td>Not yet run</td>
                    @endif
                    <td>{{ $action->scheduler_frequency_method }}</td>
                    <td>{{ $action->ping_before_url }}</td>
                    <td>{{ $action->then_ping_url }}</td>
                    <td>
                        <a class="btn btn-default"
                           href="/resources/{{ mb_strtolower($action['resource']) . '/' . mb_strtolower($action['action']) . '/edit' }}"><span class="glyphicon glyphicon-edit"></span>&nbsp;Edit Properties</a>
                    </td>
                    <td>
                        <div class="btn-group">
                            <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#"><span
                                        class="glyphicon glyphicon-tasks icon-white"></span>&nbsp;Run Now&nbsp;<span
                                        class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="/resources/{{ mb_strtolower($action['resource'] . '/' . $action['action'] . '/harvest') }}"><span class="glyphicon glyphicon-refresh"></span>&nbsp;Normal</a>
                                </li>
                                <li class="divider"></li>
                                <li>
                                    <a href="/resources/{{ mb_strtolower($action['resource'] . '/' . $action['action'] . '/refresh') }}"><span class="glyphicon glyphicon-repeat"></span>&nbsp;Full Refresh</a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>
@endsection