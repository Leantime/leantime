{{-- Header quick-search dropdown body. Swapped into #globalSearchResults. --}}
@php
    $resultCount = array_sum(array_map('count', $groups));
@endphp

@if (! $searchable)
    <div class="globalSearch__hint">{{ __('search.hint_min_chars') }}</div>
@elseif ($resultCount === 0)
    <div class="globalSearch__hint">{{ sprintf(__('search.no_results'), $term) }}</div>
@else
    @foreach ($groups as $key => $results)
        @php $provider = $providers[$key]; @endphp
        <div class="globalSearch__group" role="group" aria-label="{{ $provider->label() }}">
            <div class="globalSearch__groupTitle">
                <span class="{{ $provider->icon() }}" aria-hidden="true"></span>{{ $provider->label() }}
            </div>
            @foreach ($results as $result)
                <x-search::result :result="$result" :tokens="$tokens" variant="compact" />
            @endforeach
        </div>
    @endforeach

    <a class="searchResult globalSearch__footer" role="option" href="{{ BASE_URL }}/search/show?q={{ urlencode($term) }}">
        <span class="searchResult__icon fa-solid fa-arrow-right" aria-hidden="true"></span>
        <span class="searchResult__body">{{ __('search.see_all') }}</span>
    </a>
@endif
