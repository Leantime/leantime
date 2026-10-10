@extends($layout)

@section('content')
<x-global::pageheader :icon="'fa-solid fa-magnifying-glass'">
    <h5>{{ __('search.headline') }}</h5>
    <h1>{{ __('search.results_for') }} “{{ $term }}”</h1>
</x-global::pageheader>

<div class="maincontent">
    {!! $tpl->displayNotification() !!}

    <div class="maincontentinner searchPage">

        @php
            $activeFilterCount = count(array_filter([
                $filterValues['projectId'] > 0,
                $filterValues['from'] !== '',
                $filterValues['to'] !== '',
                $filterValues['mine'],
            ]));
            $typesNarrowed = count($selectedTypes) !== count($providers);
            $anyFilterActive = $activeFilterCount > 0 || $typesNarrowed;
        @endphp

        {{-- Same floating bar as the to-do board: search on the left, pill buttons on the right.
             Every control belongs to the one GET form via form="searchPageForm". --}}
        <div class="lt-tabs lt-tabs--floating searchPage__bar">
            <form class="searchPage__termRow" role="search" action="{{ BASE_URL }}/search/show" method="get" autocomplete="off" id="searchPageForm">
                <span class="fa-solid fa-magnifying-glass searchPage__formIcon" aria-hidden="true"></span>
                <input
                    type="search"
                    name="q"
                    class="searchPage__input"
                    value="{{ $term }}"
                    placeholder="{{ __('search.placeholder') }}"
                    aria-label="{{ __('search.label') }}"
                    autofocus
                />
                <button type="submit" class="btn btn-primary">{{ __('buttons.search') }}</button>
            </form>

            <div class="lt-tabs-actions">
                <x-global::actions.dropdown variant="filter" menu-class="searchPage__typeMenu">
                    <x-slot:trigger class="btn-link" data-tippy-content="{{ __('search.filter.types') }}">
                        <i class="fa-solid fa-layer-group"></i> {{ __('search.filter.types_button') }}
                        @if ($typesNarrowed)
                            <span class="badge badge-primary">{{ count($selectedTypes) }}</span>
                        @endif
                    </x-slot:trigger>
                    @foreach ($providers as $key => $provider)
                        <li>
                            <label>
                                <input type="checkbox" name="types[]" value="{{ $key }}" form="searchPageForm" @checked(in_array($key, $selectedTypes, true)) onchange="document.getElementById('searchPageForm').submit()" />
                                <span class="{{ $provider->icon() }}" aria-hidden="true"></span>{{ $provider->label() }}
                            </label>
                        </li>
                    @endforeach

                </x-global::actions.dropdown>

                <div class="filterWrapper">
                    <a class="btn btn-link" href="javascript:void(0);" onclick="jQuery(this).next('.filterBar').toggle();" data-tippy-content="{{ __('popover.filter') }}">
                        <i class="fas fa-filter"></i> {{ __('popover.filter') }}
                        @if ($activeFilterCount > 0)
                            <span class="badge badge-primary">{{ $activeFilterCount }}</span>
                        @endif
                    </a>
                    <div class="filterBar hideOnLoad searchPage__filterPanel">
                        <label class="searchPage__filter">
                            <span class="searchPage__filterLabel">{{ __('search.filter.project') }}</span>
                            <x-global::forms.select name="projectId" form="searchPageForm" onchange="document.getElementById('searchPageForm').submit()">
                                <option value="">{{ __('search.filter.all_projects') }}</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project['id'] }}" @selected($filterValues['projectId'] === $project['id'])>{{ $project['name'] }}</option>
                                @endforeach
                            </x-global::forms.select>
                        </label>

                        <label class="searchPage__filter">
                            <span class="searchPage__filterLabel">{{ __('search.filter.from') }}</span>
                            <input type="date" name="from" form="searchPageForm" value="{{ $filterValues['from'] }}" onchange="document.getElementById('searchPageForm').submit()" />
                        </label>

                        <label class="searchPage__filter">
                            <span class="searchPage__filterLabel">{{ __('search.filter.to') }}</span>
                            <input type="date" name="to" form="searchPageForm" value="{{ $filterValues['to'] }}" onchange="document.getElementById('searchPageForm').submit()" />
                        </label>

                        <label class="searchPage__filter searchPage__filter--check">
                            <input type="checkbox" name="mine" value="1" form="searchPageForm" @checked($filterValues['mine']) onchange="document.getElementById('searchPageForm').submit()" />
                            <span>{{ __('search.filter.mine') }}</span>
                        </label>

                        @if ($anyFilterActive)
                            <a class="searchPage__reset" href="{{ BASE_URL }}/search/show?q={{ urlencode($term) }}">{{ __('search.filter.reset') }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if (! $searchable)
            <p class="searchPage__empty">{{ __('search.hint_min_chars') }}</p>
        @else
            @php
                $panelVals = array_filter([
                    'q' => $term,
                    'projectId' => $filterValues['projectId'] ?: '',
                    'from' => $filterValues['from'],
                    'to' => $filterValues['to'],
                    'mine' => $filterValues['mine'] ? '1' : '',
                ], fn ($value) => $value !== '');
            @endphp
            <div class="searchPage__panels">
                @foreach ($selectedTypes as $key)
                    @php $provider = $providers[$key]; @endphp
                    <x-global::accordion id="search-{{ $key }}" class="searchPage__panel">
                        <x-slot name="title" id="searchPanel-{{ $key }}">
                            <span class="{{ $provider->icon() }} searchPage__panelIcon" aria-hidden="true"></span>{{ $provider->label() }}
                            <span class="searchPage__count" id="searchCount-{{ $key }}"></span>
                        </x-slot>
                        <x-slot name="content">
                            <x-global::hx
                                endpoint="search/results/get"
                                :vals="$panelVals + ['type' => $key]"
                                trigger="load"
                                loader="line"
                                :loaderCount="3"
                            />
                        </x-slot>
                    </x-global::accordion>
                @endforeach
            </div>
        @endif

    </div>
</div>
@endsection
