@extends($layout)

@section('content')

<x-global::pageheader :icon="'fa fa-handshake'">
    <h1>{{ __('headlines.oneonone.my_oneonones') }}</h1>
</x-global::pageheader>

@displayNotification()

<div class="maincontent">
    <div class="maincontentinner">

        {{-- Stats row --}}
        <div class="row tw-mb-m">
            <div class="col-md-3 col-sm-6 col-xs-6">
                <div class="bigNumberBox">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>{{ __('text.oneonone.total_sessions') }}</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-6">
                <div class="bigNumberBox">
                    <h3>{{ $stats['upcoming'] }}</h3>
                    <p>{{ __('text.oneonone.upcoming') }}</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-6">
                <div class="bigNumberBox">
                    <h3>{{ $stats['completed'] }}</h3>
                    <p>{{ __('text.oneonone.completed') }}</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-6">
                <div class="bigNumberBox">
                    <h3>{{ $stats['openActions'] }}</h3>
                    <p>{{ __('text.oneonone.open_actions') }}</p>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Left: sessions timeline --}}
            <div class="col-md-8 col-sm-12">
                <div class="maincontentinner">
                    <h4 class="widgettitle title-light">{{ __('headlines.oneonone.session_history') }}</h4>

                    @if (count($sessions) === 0)
                        <div class="tw-p-l tw-text-center">
                            <p>{{ __('text.oneonone.no_sessions_yet') }}</p>
                        </div>
                    @else
                        <ul class="tw-list-none tw-p-0 tw-m-0">
                            @foreach ($sessions as $session)
                                @include('oneonone::partials.sessionCard', [
                                    'session' => $session,
                                    'view' => 'employee',
                                ])
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            {{-- Right: my open action items --}}
            <div class="col-md-4 col-sm-12">
                <div class="maincontentinner">
                    <h4 class="widgettitle title-light">{{ __('headlines.oneonone.my_action_items') }}</h4>

                    @if (count($openActionItems) === 0)
                        <p class="tw-text-sm">{{ __('text.oneonone.no_open_actions') }}</p>
                    @else
                        <ul class="tw-list-none tw-p-0 tw-m-0">
                            @foreach ($openActionItems as $item)
                                <li class="tw-mb-s tw-p-s tw-rounded"
                                    style="border:1px solid var(--main-border-color); background:var(--secondary-background);">
                                    <div class="tw-flex tw-justify-between tw-items-start tw-gap-s">
                                        <div class="tw-flex-1">
                                            <div>{{ $item['content'] }}</div>
                                            <small class="tw-text-xs" style="color:var(--grey);">
                                                @if (!empty($item['dueDate']))
                                                    <span class="fa fa-calendar"></span>
                                                    {{ dtHelper()->parseDbDateTime($item['dueDate'])->setToUserTimezone()->format(__('language.dateformat')) }} &middot;
                                                @endif
                                                <a href="{{ BASE_URL }}/oneonone/showSession/{{ $item['sessionId'] }}">
                                                    {{ __('text.oneonone.from_session') }}
                                                </a>
                                            </small>
                                        </div>
                                        <button type="button"
                                                class="btn btn-xs"
                                                hx-patch="{{ BASE_URL }}/hx/oneonone/sessionItems/toggleItem"
                                                hx-vals='@json(["itemId" => $item["id"]])'
                                                hx-target="closest li"
                                                hx-swap="outerHTML"
                                                title="{{ __('buttons.mark_done') }}">
                                            <span class="fa fa-check"></span>
                                        </button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
