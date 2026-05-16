@php $todayTasks = $tasksByDay[$today] ?? []; @endphp

{{-- Today's section is always shown, even when empty --}}
<div class="notepad-day" data-date="{{ $today }}" id="notepad-day-{{ $today }}">
    @include('notepad::partials.daySection', ['taskDate' => $today, 'tasks' => $todayTasks])
</div>

{{-- Previous days (only those with tasks, desc) --}}
@foreach ($tasksByDay as $date => $tasks)
    @if ($date === $today)
        @continue
    @endif
    <div class="notepad-day" data-date="{{ $date }}" id="notepad-day-{{ $date }}">
        @include('notepad::partials.daySection', ['taskDate' => $date, 'tasks' => $tasks])
    </div>
@endforeach

@if (empty($todayTasks) && count(array_filter($tasksByDay, fn($t, $d) => $d !== $today, ARRAY_FILTER_USE_BOTH)) === 0)
    <p style="color:var(--grey); margin-top:30px; text-align:center;">
        Your notepad is empty. Add a task above — your last 7 days will show up here.
    </p>
@endif
