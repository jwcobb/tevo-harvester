<dl class="dl-horizontal">
    <dt>Last Run</dt>
    <dd>
        @if ($harvest['last_run_at'] != null)
            @lastrundiff($harvest['last_run_at'])
        @else
            Not yet run
        @endif
    </dd>

    <dt>Scheduled to Run</dt>
    <dd>{{ $harvest['scheduler_frequency_method'] }}</dd>

    <dt>Ping Before</dt>
    <dd>{{ $harvest['ping_before_url'] }}</dd>

    <dt>Ping After</dt>
    <dd>{{ $harvest['then_ping_url'] }}</dd>

    <dt>Manually Harvest via Artisan</dt>
    <dd><code>artisan harvester:update {{ $harvest['resource'] }} --action={{ $harvest['action'] }}</code></dd>

    <dt>Manually Refresh via Artisan</dt>
    <dd><code>artisan harvester:refresh {{ $harvest['resource'] }} --action={{ $harvest['action'] }}</code></dd>
</dl>

<a class="btn btn-primary" href="{{ url('/resources/' . $harvest['resource'] . '/' . $harvest['action']) . '/harvest' }}">Harvest</a>
<a class="btn btn-primary" href="{{ url('/resources/' . $harvest['resource'] . '/' . $harvest['action']) . '/refresh' }}">Refresh</a>
<a class="btn btn-primary" href="{{ url('/resources/' .$harvest['resource'] . '/' . $harvest['action']) . '/edit' }}">Edit</a>