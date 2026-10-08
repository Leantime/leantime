@extends($layout)

@section('content')
<x-global::pageheader :icon="'fa-solid fa-magnifying-glass'">
    <h5>{{ __('search.headline') }}</h5>
    <h1>{{ __('search.results_for') }} “{{ $term }}”</h1>
</x-global::pageheader>

<div class="maincontent">
    {!! $tpl->displayNotification() !!}

    <div class="maincontentinner searchPage">

        <form class="searchPage__form" role="search" action="{{ BASE_URL }}/search/show" method="get" autocomplete="off">
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

        @if (! $searchable)
            <p class="searchPage__empty">{{ __('search.hint_min_chars') }}</p>
        @else
            <div class="searchPage__panels">
                @foreach ($providers as $key => $provider)
                    <section class="searchPage__panel" aria-labelledby="searchPanel-{{ $key }}">
                        <h4 class="searchPage__panelTitle" id="searchPanel-{{ $key }}">
                            <span class="{{ $provider->icon() }}" aria-hidden="true"></span>{{ $provider->label() }}
                        </h4>
                        <x-global::hx
                            endpoint="search/results/get"
                            :vals="['q' => $term, 'type' => $key]"
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
