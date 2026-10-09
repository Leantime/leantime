{{--
    One entity-type panel on the full results page.

    The first page renders the list; "load more" requests render only the `rows` fragment,
    which swaps itself over the load-more button (hx-target="this", outerHTML).
--}}
@if ($offset === 0)
    {{-- Loaded count for the section header; "+" when another page exists. --}}
    <span class="searchPage__count" id="searchCount-{{ $provider->key() }}" hx-swap-oob="true">{{ count($results) }}{{ $hasMore ? '+' : '' }}</span>
@endif

@if ($offset === 0 && $results === [])
    <p class="searchPage__empty">{{ __('search.no_results_type') }}</p>
@else
    <div class="searchPage__list" role="list">
        @fragment('rows')
            @foreach ($results as $result)
                <x-search::result :result="$result" :tokens="$tokens" variant="full" />
            @endforeach

            @if ($hasMore)
                <button
                    type="button"
                    class="btn btn-link searchPage__more"
                    hx-get="{{ BASE_URL }}/hx/search/results/get"
                    hx-vals='{!! json_encode(array_merge($filters, ['q' => $term, 'type' => $provider->key(), 'offset' => $nextOffset]), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) !!}'
                    hx-target="this"
                    hx-swap="outerHTML"
                >{{ __('search.load_more') }}</button>
            @endif
        @endfragment
    </div>
@endif
