@extends($layout)

@section('content')
<x-global::pageheader :icon="'fa-solid fa-magnifying-glass'">
    <h5>{{ __('search.headline') }}</h5>
    <h1>{{ __('search.results_for') }} “{{ $term }}”</h1>
</x-global::pageheader>

<div class="maincontent">
    {!! $tpl->displayNotification() !!}

    <div class="maincontentinner searchPage">

        <form class="searchPage__form" role="search" action="{{ BASE_URL }}/search/show" method="get" autocomplete="off" id="searchPageForm">
            <div class="searchPage__termRow">
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
            </div>

            <div class="searchPage__filters">
                <fieldset class="searchPage__types" aria-label="{{ __('search.filter.types') }}">
                    @foreach ($providers as $key => $provider)
                        <label class="searchPage__typeChip">
                            <input type="checkbox" name="types[]" value="{{ $key }}" @checked(in_array($key, $selectedTypes, true)) onchange="this.form.submit()" />
                            <span class="{{ $provider->icon() }}" aria-hidden="true"></span>{{ $provider->label() }}
                        </label>
                    @endforeach
                </fieldset>

                <div class="searchPage__filterRow">
                    <label class="searchPage__filter">
                        <span class="searchPage__filterLabel">{{ __('search.filter.project') }}</span>
                        <select name="projectId" onchange="this.form.submit()">
                            <option value="">{{ __('search.filter.all_projects') }}</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project['id'] }}" @selected($filterValues['projectId'] === $project['id'])>{{ $project['name'] }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="searchPage__filter">
                        <span class="searchPage__filterLabel">{{ __('search.filter.from') }}</span>
                        <input type="date" name="from" value="{{ $filterValues['from'] }}" onchange="this.form.submit()" />
                    </label>

                    <label class="searchPage__filter">
                        <span class="searchPage__filterLabel">{{ __('search.filter.to') }}</span>
                        <input type="date" name="to" value="{{ $filterValues['to'] }}" onchange="this.form.submit()" />
                    </label>

                    <label class="searchPage__filter searchPage__filter--check">
                        <input type="checkbox" name="mine" value="1" @checked($filterValues['mine']) onchange="this.form.submit()" />
                        <span>{{ __('search.filter.mine') }}</span>
                    </label>

                    @if ($filterValues['projectId'] || $filterValues['from'] !== '' || $filterValues['to'] !== '' || $filterValues['mine'] || count($selectedTypes) !== count($providers))
                        <a class="searchPage__reset" href="{{ BASE_URL }}/search/show?q={{ urlencode($term) }}">{{ __('search.filter.reset') }}</a>
                    @endif
                </div>
            </div>
        </form>

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
                    <section class="searchPage__panel" aria-labelledby="searchPanel-{{ $key }}">
                        <h4 class="searchPage__panelTitle" id="searchPanel-{{ $key }}">
                            <span class="{{ $provider->icon() }}" aria-hidden="true"></span>{{ $provider->label() }}
                        </h4>
                        <x-global::hx
                            endpoint="search/results/get"
                            :vals="$panelVals + ['type' => $key]"
                            trigger="load"
                            loader="line"
                            :loaderCount="4"
                        />
                    </section>
                @endforeach
            </div>
        @endif

    </div>
</div>
@endsection
