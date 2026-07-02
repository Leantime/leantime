<table class="table table-condensed">
    <thead>
        <tr>
            <th>{!! __('label.date') !!}</th>
            <th>{!! __('label.employee') !!}</th>
            <th>{!! __('label.action') !!}</th>
            <th>{!! __('label.hours') !!}</th>
        </tr>
    </thead>
    <tbody>
    @forelse ($history as $event)
        <tr>
            <td>{{ format($event['dateCreated'])->date() }} {{ format($event['dateCreated'])->time() }}</td>
            <td>{!! sprintf(__('text.full_name'), $tpl->escape($event['firstname']), $tpl->escape($event['lastname'])) !!}</td>
            <td>{{ $event['action'] === 'logged' ? __('label.timelog_action_logged') : __('label.timelog_action_modified') }}</td>
            <td>{{ $event['hours'] }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="4">{{ __('text.no_time_history') }}</td>
        </tr>
    @endforelse
    </tbody>
</table>
